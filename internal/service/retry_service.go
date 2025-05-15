package service

import (
	"context"
	"encoding/json"
	"fmt"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/pkg/logger"
	"time"

	"go.uber.org/zap"
)

type RetryService struct {
	retryRepo    repository.RetryRepository
	orderRepo    repository.OrderRepository
	platformRepo repository.PlatformRepository
	productRepo  repository.ProductRepository
}

func NewRetryService(
	retryRepo repository.RetryRepository,
	orderRepo repository.OrderRepository,
	platformRepo repository.PlatformRepository,
) *RetryService {
	return &RetryService{
		retryRepo:    retryRepo,
		orderRepo:    orderRepo,
		platformRepo: platformRepo,
	}
}

// HandleRetry 处理重试
func (s *RetryService) HandleRetry(ctx context.Context, order *model.Order, retryType int) error {
	// 1. 获取可用的API关系列表
	relations, err := s.getAvailableAPIRelations(ctx, order.ID, order.ProductID)
	if err != nil {
		return fmt.Errorf("获取可用API失败: %v", err)
	}

	if len(relations) == 0 {
		return fmt.Errorf("没有可用的API进行重试")
	}

	// 2. 创建重试记录
	for _, relation := range relations {
		// 获取已使用的API列表
		records, err := s.retryRepo.GetByOrderID(ctx, order.ID)
		if err != nil {
			return fmt.Errorf("获取已使用API失败: %v", err)
		}

		// 收集已使用的API ID
		usedAPIs := make([]int64, 0)
		for _, record := range records {
			var usedAPIsList []int64
			if err := json.Unmarshal([]byte(record.UsedAPIs), &usedAPIsList); err != nil {
				return fmt.Errorf("解析已使用API失败: %v", err)
			}
			usedAPIs = append(usedAPIs, usedAPIsList...)
		}

		// 添加当前API到已使用列表
		usedAPIs = append(usedAPIs, relation.APIID)
		usedAPIsJSON, err := json.Marshal(usedAPIs)
		if err != nil {
			return fmt.Errorf("序列化已使用API失败: %v", err)
		}

		retryRecord := &model.OrderRetryRecord{
			OrderID:       order.ID,
			APIID:         relation.APIID,
			ParamID:       relation.ParamID,
			RetryType:     retryType,
			Status:        0,                               // 待重试
			NextRetryTime: time.Now().Add(5 * time.Minute), // 默认5分钟后重试
			UsedAPIs:      string(usedAPIsJSON),
		}

		if err := s.retryRepo.Create(ctx, retryRecord); err != nil {
			logger.Log.Error("创建重试记录失败",
				zap.Int64("order_id", order.ID),
				zap.Int64("api_id", relation.APIID),
				zap.Error(err),
			)
			continue
		}
	}

	return nil
}

// ProcessRetries 处理待重试的记录
func (s *RetryService) ProcessRetries(ctx context.Context) error {
	// 1. 获取待重试的记录
	records, err := s.retryRepo.GetPendingRetries(ctx)
	if err != nil {
		return fmt.Errorf("获取待重试记录失败: %v", err)
	}

	// 2. 处理每条重试记录
	for _, record := range records {
		if err := s.executeRetry(ctx, record); err != nil {
			logger.Log.Error("处理重试记录失败",
				zap.Int64("record_id", record.ID),
				zap.Int64("order_id", record.OrderID),
				zap.Error(err),
			)
			continue
		}
	}

	return nil
}

// executeRetry 执行重试
func (s *RetryService) executeRetry(ctx context.Context, record *model.OrderRetryRecord) error {
	// 1. 获取订单信息
	order, err := s.orderRepo.GetByID(ctx, record.OrderID)
	if err != nil {
		return fmt.Errorf("获取订单信息失败: %v", err)
	}

	// 2. 获取API信息
	api, err := s.platformRepo.GetAPIByID(ctx, record.APIID)
	if err != nil {
		return fmt.Errorf("获取API信息失败: %v", err)
	}

	// 3. 获取参数信息
	param, err := s.platformRepo.GetAPIParamByID(ctx, record.ParamID)
	if err != nil {
		return fmt.Errorf("获取参数信息失败: %v", err)
	}

	// 4. 获取平台信息
	platform, err := s.platformRepo.GetPlatformByID(api.PlatformID)
	if err != nil {
		return fmt.Errorf("获取平台信息失败: %v", err)
	}

	// 5. 执行重试
	// 根据平台类型选择不同的充值方法
	switch platform.Code {
	case "kekebang":
		// 调用客客帮充值方法
		err = s.executeKekebangRecharge(ctx, order, api, param)
	case "xianzhuanxia":
		// 调用闲赚侠充值方法
		err = s.executeXianzhuanxiaRecharge(ctx, order, api, param)
	default:
		return fmt.Errorf("不支持的平台类型: %s", platform.Code)
	}

	if err != nil {
		// 更新重试记录状态为失败
		record.Status = 3 // 重试失败
		record.LastError = err.Error()
		if err := s.retryRepo.Update(ctx, record); err != nil {
			logger.Log.Error("更新重试记录失败",
				zap.Int64("record_id", record.ID),
				zap.Error(err),
			)
		}
		return err
	}

	// 6. 更新重试记录状态为成功
	record.Status = 2 // 重试成功
	if err := s.retryRepo.Update(ctx, record); err != nil {
		return fmt.Errorf("更新重试记录失败: %v", err)
	}

	return nil
}

// executeKekebangRecharge 执行客客帮充值
func (s *RetryService) executeKekebangRecharge(ctx context.Context, order *model.Order, api *model.PlatformAPI, param *model.PlatformAPIParam) error {
	// TODO: 实现客客帮充值逻辑
	return nil
}

// executeXianzhuanxiaRecharge 执行闲赚侠充值
func (s *RetryService) executeXianzhuanxiaRecharge(ctx context.Context, order *model.Order, api *model.PlatformAPI, param *model.PlatformAPIParam) error {
	// TODO: 实现闲赚侠充值逻辑
	return nil
}

// getAvailableAPIRelations 获取可用的API关系列表
func (s *RetryService) getAvailableAPIRelations(ctx context.Context, orderID int64, productID int64) ([]*model.ProductAPIRelation, error) {
	// 1. 获取订单的所有重试记录
	records, err := s.retryRepo.GetByOrderID(ctx, orderID)
	if err != nil {
		return nil, err
	}

	// 2. 收集所有已使用过的API ID
	usedAPIs := make(map[int64]bool)
	for _, record := range records {
		var usedAPIsList []int64
		if err := json.Unmarshal([]byte(record.UsedAPIs), &usedAPIsList); err != nil {
			return nil, err
		}
		for _, apiID := range usedAPIsList {
			usedAPIs[apiID] = true
		}
	}

	// 3. 获取商品关联的所有API关系
	relations, err := s.productRepo.GetAPIRelationsByProductID(ctx, productID)
	if err != nil {
		return nil, err
	}

	// 4. 过滤掉已使用过的API
	availableRelations := make([]*model.ProductAPIRelation, 0)
	for _, relation := range relations {
		if !usedAPIs[relation.APIID] {
			availableRelations = append(availableRelations, relation)
		}
	}

	return availableRelations, nil
}
