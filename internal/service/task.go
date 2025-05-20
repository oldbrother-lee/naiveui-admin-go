package service

import (
	"context"
	"fmt"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/internal/service/platform"
	"recharge-go/pkg/logger"
	"sync"
	"time"
)

type TaskService struct {
	taskConfigRepo *repository.TaskConfigRepository
	taskOrderRepo  *repository.TaskOrderRepository
	platformSvc    *platform.Service
	config         *TaskConfig
	ctx            context.Context
	cancel         context.CancelFunc
	wg             sync.WaitGroup
	mu             sync.Mutex
	isRunning      bool
}

type TaskConfig struct {
	Interval      time.Duration // 任务执行间隔
	MaxRetries    int           // 最大重试次数
	RetryDelay    time.Duration // 重试延迟
	MaxConcurrent int           // 最大并发数
	APIKey        string        // API密钥
	UserID        string        // 用户ID
	BaseURL       string        // API基础URL
}

func NewTaskService(
	taskConfigRepo *repository.TaskConfigRepository,
	taskOrderRepo *repository.TaskOrderRepository,
	platformSvc *platform.Service,
	config *TaskConfig,
) *TaskService {
	ctx, cancel := context.WithCancel(context.Background())
	return &TaskService{
		taskConfigRepo: taskConfigRepo,
		taskOrderRepo:  taskOrderRepo,
		platformSvc:    platformSvc,
		config:         config,
		ctx:            ctx,
		cancel:         cancel,
	}
}

// StartTask 启动自动取单任务
func (s *TaskService) StartTask() {
	s.mu.Lock()
	if s.isRunning {
		s.mu.Unlock()
		return
	}
	s.isRunning = true
	s.mu.Unlock()

	s.wg.Add(1)
	go func() {
		defer s.wg.Done()
		ticker := time.NewTicker(s.config.Interval)
		defer ticker.Stop()

		for {
			select {
			case <-s.ctx.Done():
				return
			case <-ticker.C:
				s.processTask()
			}
		}
	}()
}

// StopTask 停止自动取单任务
func (s *TaskService) StopTask() {
	s.mu.Lock()
	if !s.isRunning {
		s.mu.Unlock()
		return
	}
	s.isRunning = false
	s.mu.Unlock()

	s.cancel()
	s.wg.Wait()
}

// processTask 处理取单任务
func (s *TaskService) processTask() {
	logger.Info("开始执行定时任务")

	// 获取所有启用的任务配置
	configs, err := s.taskConfigRepo.GetEnabledConfigs()
	if err != nil {
		logger.Error("获取任务配置失败", err)
		return
	}
	logger.Info(fmt.Sprintf("获取到 %d 个启用的任务配置", len(configs)))

	// 处理每个配置
	for _, config := range configs {
		channelID := int(config.ChannelID)
		productID := int(config.ProductID)

		logger.Info(fmt.Sprint("处理任务配置: ChannelID=%d, ProductID=%d", channelID, productID))
		fmt.Printf("处理任务配置11: %d\n", channelID)

		// 1. 获取有效 token（自动复用/过期自动申请）
		token, err := s.platformSvc.GetToken(channelID, productID, "", config.FaceValues, config.MinSettleAmounts)
		if err != nil {
			fmt.Printf("获取 token 失败: ChannelID=%d, ProductID=%d, error=%v\n", channelID, productID, err)
			logger.Error("获取 token 失败: ChannelID=%d, ProductID=%d, error=%v", channelID, productID, err)
			continue
		}
		logger.Info(fmt.Sprintf("获取 token 成功: ChannelID=%d, ProductID=%d, token=%s", channelID, productID, token))

		// 2. 查询任务结果
		order, err := s.platformSvc.QueryTask(token)
		if err != nil {
			logger.Error("查询任务匹配状态失败: token=%s, error=%v", token, err)
			continue
		}

		if order == nil {
			logger.Info(fmt.Sprintf("未匹配到订单: token=%s", token))
			continue
		}

		logger.Info("匹配到订单: OrderNumber=%s, AccountNum=%s, SettlementAmount=%.2f",
			order.OrderNumber, order.AccountNum, order.SettlementAmount)

		// 3. 匹配到订单后让 token 失效
		_ = s.platformSvc.InvalidateToken()

		// 4. 创建任务订单记录
		taskOrder := &model.TaskOrder{
			OrderNumber:            order.OrderNumber,
			ChannelID:              channelID,
			ProductID:              productID,
			AccountNum:             order.AccountNum,
			AccountLocation:        order.AccountLocation,
			SettlementAmount:       order.SettlementAmount,
			OrderStatus:            order.OrderStatus,
			SettlementStatus:       1, // 待结算
			CreateTime:             order.CreateTime.UnixMilli(),
			ExpirationTime:         order.ExpirationTime.UnixMilli(),
			SettlementTime:         order.SettlementTime.UnixMilli(),
			ExpectedSettlementTime: order.ExpectedSettlementTime.UnixMilli(),
		}

		// 5. 保存任务订单
		if err := s.taskOrderRepo.Create(taskOrder); err != nil {
			logger.Error("保存任务订单失败: OrderNumber=%s, error=%v", order.OrderNumber, err)
			continue
		}
		logger.Info("保存任务订单成功: OrderNumber=%s", order.OrderNumber)
	}

	logger.Info("定时任务执行完成")
}
