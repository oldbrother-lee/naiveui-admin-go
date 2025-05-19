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
				s.fetchOrders()
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

// fetchOrders 获取订单
func (s *TaskService) fetchOrders() error {
	logger.Info("开始执行定时任务")

	// 获取任务配置
	configs, err := s.taskConfigRepo.GetEnabledConfigs()
	if err != nil {
		logger.Error("获取任务配置失败", err)
		return err
	}
	logger.Info("获取到 %d 个启用的任务配置", len(configs))

	for _, config := range configs {
		logger.Info("处理任务配置: ChannelID=%d, ProductID=%d", config.ChannelID, config.ProductID)
		fmt.Printf("处理任务配置11: %d\n", config.ChannelID)
		// 申请做单
		token, err := s.platformSvc.SubmitTask(
			config.ChannelID,
			config.ProductID,
			"", // 暂时不限制省份
			config.FaceValues,
			config.MinSettleAmounts,
		)
		if err != nil {
			fmt.Printf("申请做单失败: ChannelID=%d, ProductID=%d, error=%v\n", config.ChannelID, config.ProductID, err)
			logger.Error("申请做单失败: ChannelID=%d, ProductID=%d, error=%v", config.ChannelID, config.ProductID, err)
			continue
		}
		logger.Info("申请做单成功: ChannelID=%d, ProductID=%d, token=%s", config.ChannelID, config.ProductID, token)

		// 查询是否匹配到订单
		order, err := s.platformSvc.QueryTask(token)
		if err != nil {
			logger.Error("查询任务匹配状态失败: token=%s, error=%v", token, err)
			continue
		}

		if order == nil {
			logger.Info("未匹配到订单: token=%s", token)
			continue
		}

		logger.Info("匹配到订单: OrderNumber=%s, AccountNum=%s, SettlementAmount=%.2f",
			order.OrderNumber, order.AccountNum, order.SettlementAmount)

		// 创建任务订单记录
		taskOrder := &model.TaskOrder{
			OrderNumber:            order.OrderNumber,
			ChannelID:              config.ChannelID,
			ProductID:              config.ProductID,
			AccountNum:             order.AccountNum,
			SettlementAmount:       order.SettlementAmount,
			OrderStatus:            order.OrderStatus,
			CreateTime:             order.CreateTime.UnixMilli(),
			ExpirationTime:         order.ExpirationTime.UnixMilli(),
			SettlementTime:         order.SettlementTime.UnixMilli(),
			ExpectedSettlementTime: order.ExpectedSettlementTime.UnixMilli(),
		}

		// 保存任务订单
		if err := s.taskOrderRepo.Create(taskOrder); err != nil {
			logger.Error("保存任务订单失败: OrderNumber=%s, error=%v", order.OrderNumber, err)
			continue
		}
		logger.Info("保存任务订单成功: OrderNumber=%s", order.OrderNumber)
	}

	logger.Info("定时任务执行完成")
	return nil
}
