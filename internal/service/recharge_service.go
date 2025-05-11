package service

import (
	"context"
	"fmt"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/internal/service/recharge"
	"recharge-go/pkg/logger"
	redisPkg "recharge-go/pkg/redis"
	"strconv"
	"time"

	redisV8 "github.com/go-redis/redis/v8"
)

// RechargeService 充值服务接口
type RechargeService interface {
	// Recharge 执行充值
	Recharge(ctx context.Context, orderID int64) error
	// HandleCallback 处理平台回调
	HandleCallback(ctx context.Context, platformName string, data []byte) error
	// GetPendingTasks 获取待处理的充值任务
	GetPendingTasks(ctx context.Context, limit int) ([]*model.Order, error)
	// ProcessRechargeTask 处理充值任务
	ProcessRechargeTask(ctx context.Context, order *model.Order) error
	// CreateRechargeTask 创建充值任务
	CreateRechargeTask(ctx context.Context, orderID int64) error
	// GetPlatformAPIByOrderID 根据订单ID获取平台API信息
	GetPlatformAPIByOrderID(ctx context.Context, orderID string) (*model.PlatformAPI, *model.ProductAPIRelation, error)
	// PushToRechargeQueue 将订单推送到充值队列
	PushToRechargeQueue(ctx context.Context, orderID int64) error
	// PopFromRechargeQueue 从充值队列获取订单
	PopFromRechargeQueue(ctx context.Context) (int64, error)
	// GetOrderByID 根据ID获取订单
	GetOrderByID(ctx context.Context, orderID int64) (*model.Order, error)
	// RemoveFromProcessingQueue 从处理中队列移除任务
	RemoveFromProcessingQueue(ctx context.Context, orderID int64) error
}

// rechargeService 充值服务实现
type rechargeService struct {
	orderRepo              repository.OrderRepository
	platformRepo           repository.PlatformRepository
	productAPIRelationRepo repository.ProductAPIRelationRepository
	manager                *recharge.Manager
	redisClient            *redisV8.Client
}

// NewRechargeService 创建充值服务实例
func NewRechargeService(
	orderRepo repository.OrderRepository,
	platformRepo repository.PlatformRepository,
	productAPIRelationRepo repository.ProductAPIRelationRepository,
	manager *recharge.Manager,
) RechargeService {
	return &rechargeService{
		orderRepo:              orderRepo,
		platformRepo:           platformRepo,
		productAPIRelationRepo: productAPIRelationRepo,
		manager:                manager,
		redisClient:            redisPkg.GetClient(),
	}
}

// Recharge 执行充值
func (s *rechargeService) Recharge(ctx context.Context, orderID int64) error {
	// 1. 获取订单信息
	order, err := s.orderRepo.GetByID(ctx, orderID)
	if err != nil {
		logger.Error("【获取订单信息失败】order_id: %d, error: %v", orderID, err)

		return fmt.Errorf("get order failed: %v", err)
	}

	// 检查订单状态，如果已经是充值中或已完成，则不再处理
	if order.Status == model.OrderStatusRecharging || order.Status == model.OrderStatusSuccess {
		logger.Info("【订单状态异常，跳过处理】order_id: %d, status: %d", orderID, order.Status)
		// 从处理中队列移除
		_ = s.RemoveFromProcessingQueue(ctx, orderID)
		return nil
	}

	// 2. 获取平台API信息
	api, relation, err := s.GetPlatformAPIByOrderID(ctx, order.OrderNumber)
	if err != nil {
		logger.Error("【获取平台API信息失败】order_id: %d, error: %v", orderID, err)

		return fmt.Errorf("get platform api failed: %v", err)
	}
	fmt.Println(api, "api******")
	// 3. 提交订单到平台
	logger.Info("【开始提交订单到平台】order_id: %d", orderID)
	if err := s.manager.SubmitOrder(ctx, order, api); err != nil {
		logger.Error("【提交订单到平台失败】order_id: %d, error: %v", orderID, err)

		return fmt.Errorf("submit order failed: %v", err)
	}

	// 4. 更新订单状态为充值中
	if err := s.orderRepo.UpdateStatus(ctx, orderID, model.OrderStatusRecharging); err != nil {
		logger.Error("【更新订单状态失败】order_id: %d, error: %v", orderID, err)

		return fmt.Errorf("update order status failed: %v", err)
	}
	//更新订单支付平台 id 和 api id
	fmt.Println(relation, "relation******")
	if err := s.orderRepo.UpdatePlatformID(ctx, orderID, api.ID, relation.ParamID); err != nil {
		logger.Error("【更新订单支付平台ID失败】order_id: %d, error: %v", orderID, err)

		return fmt.Errorf("update order platform id failed: %v", err)
	}

	// 5. 从处理中队列移除
	logger.Info("【从处理中队列移除】order_id: %d", orderID)
	if err := s.RemoveFromProcessingQueue(ctx, orderID); err != nil {
		logger.Error("【从处理中队列移除失败】order_id: %d, error: %v", orderID, err)
	}

	logger.Info("【充值流程完成】order_id: %d", orderID)
	return nil
}

// HandleCallback 处理平台回调
func (s *rechargeService) HandleCallback(ctx context.Context, platformName string, data []byte) error {
	// 处理回调
	if err := s.manager.HandleCallback(ctx, platformName, data); err != nil {
		return fmt.Errorf("handle callback failed: %v", err)
	}

	return nil
}

// GetPendingTasks 获取待处理的充值任务
func (s *rechargeService) GetPendingTasks(ctx context.Context, limit int) ([]*model.Order, error) {
	// 获取状态为待充值的订单，并且最近5分钟内没有被处理过的
	orders, err := s.orderRepo.GetByStatus(ctx, model.OrderStatusPendingRecharge)
	if err != nil {
		return nil, err
	}

	// 过滤掉最近5分钟内处理过的订单
	var filteredOrders []*model.Order
	now := time.Now()
	for _, order := range orders {
		// 如果订单的更新时间在5分钟内，跳过
		if order.UpdatedAt.Add(5 * time.Minute).After(now) {
			continue
		}
		filteredOrders = append(filteredOrders, order)
	}

	// 限制返回数量
	if len(filteredOrders) > limit {
		filteredOrders = filteredOrders[:limit]
	}

	return filteredOrders, nil
}

// ProcessRechargeTask 处理充值任务
func (s *rechargeService) ProcessRechargeTask(ctx context.Context, order *model.Order) error {
	logger.Info("开始处理充值任务, order_id: %d, order_number: %s, mobile: %s",
		order.ID, order.OrderNumber, order.Mobile)

	// 执行充值
	if err := s.Recharge(ctx, order.ID); err != nil {
		logger.Error("处理充值任务失败, order_id: %d, order_number: %s, error: %v",
			order.ID, order.OrderNumber, err)
		return err
	}

	logger.Info("充值任务处理完成, order_id: %d, order_number: %s",
		order.ID, order.OrderNumber)
	return nil
}

// CreateRechargeTask 创建充值任务
func (s *rechargeService) CreateRechargeTask(ctx context.Context, orderID int64) error {
	logger.Info("创建充值任务, order_id: %d", orderID)

	// 获取订单信息
	_, err := s.orderRepo.GetByID(ctx, orderID)
	if err != nil {
		logger.Error("获取订单信息失败, order_id: %d, error: %v", orderID, err)
		return fmt.Errorf("get order failed: %v", err)
	}

	// 更新订单状态为待充值
	if err := s.orderRepo.UpdateStatus(ctx, orderID, model.OrderStatusPendingRecharge); err != nil {
		logger.Error("更新订单状态失败, order_id: %d, error: %v", orderID, err)
		return fmt.Errorf("update order status failed: %v", err)
	}

	logger.Info("充值任务创建成功, order_id: %d", orderID)
	return nil
}

// GetPlatformAPIByOrderID 根据订单ID获取平台API信息
func (s *rechargeService) GetPlatformAPIByOrderID(ctx context.Context, orderID string) (*model.PlatformAPI, *model.ProductAPIRelation, error) {
	// 获取订单信息
	order, err := s.orderRepo.GetByOrderID(ctx, orderID)
	if err != nil {
		return nil, nil, fmt.Errorf("获取订单信息失败%d: %v", orderID, err)
	}

	// 获取产品API关系
	relation, err := s.productAPIRelationRepo.GetByProductID(ctx, order.ProductID)
	if err != nil {
		return nil, nil, fmt.Errorf("获取产品API关系失败: %v", err)
	}

	// 获取平台API信息
	api, err := s.platformRepo.GetAPIByID(ctx, relation.APIID)
	if err != nil {
		return nil, nil, fmt.Errorf("获取平台API信息失败: %v", err)
	}

	return api, relation, nil
}

// PushToRechargeQueue 将订单推送到充值队列
func (s *rechargeService) PushToRechargeQueue(ctx context.Context, orderID int64) error {
	return s.redisClient.LPush(ctx, "recharge_queue", orderID).Err()
}

// PopFromRechargeQueue 从充值队列获取订单
func (s *rechargeService) PopFromRechargeQueue(ctx context.Context) (int64, error) {
	// 使用 BRPOPLPUSH 命令，将任务从队列中移除并放入处理中队列
	result, err := s.redisClient.BRPopLPush(ctx, "recharge_queue", "recharge_processing", 0).Result()
	if err != nil {
		return 0, err
	}

	orderID, err := strconv.ParseInt(result, 10, 64)
	if err != nil {
		return 0, fmt.Errorf("parse order id failed: %v", err)
	}

	return orderID, nil
}

// RemoveFromProcessingQueue 从处理中队列移除任务
func (s *rechargeService) RemoveFromProcessingQueue(ctx context.Context, orderID int64) error {
	return s.redisClient.LRem(ctx, "recharge_processing", 0, orderID).Err()
}

// GetOrderByID 根据ID获取订单
func (s *rechargeService) GetOrderByID(ctx context.Context, orderID int64) (*model.Order, error) {
	return s.orderRepo.GetByID(ctx, orderID)
}
