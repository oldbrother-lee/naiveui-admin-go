package service

import (
	"context"
	"fmt"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/pkg/logger"
)

type RechargeService interface {
	CreateRechargeTask(ctx context.Context, order *model.Order) error
	ProcessRechargeTask(ctx context.Context, task *model.RechargeTask) error
	GetPendingTasks(ctx context.Context, limit int) ([]*model.RechargeTask, error)
}

type rechargeService struct {
	taskRepo        repository.RechargeTaskRepository
	orderRepo       repository.OrderRepository
	platformService *PlatformService
}

func NewRechargeService(
	taskRepo repository.RechargeTaskRepository,
	orderRepo repository.OrderRepository,
	platformService *PlatformService,
) RechargeService {
	return &rechargeService{
		taskRepo:        taskRepo,
		orderRepo:       orderRepo,
		platformService: platformService,
	}
}

func (s *rechargeService) CreateRechargeTask(ctx context.Context, order *model.Order) error {
	logger.Info("开始创建充值任务, 订单ID: %d, 订单号: %s, 手机号: %s, 金额: %.2f",
		order.ID, order.OrderNumber, order.Mobile, order.TotalPrice)

	task := &model.RechargeTask{
		OrderID: order.ID,
		// PlatformID: order.PlatformID,
		Status:     0, // 待处理
		MaxRetries: 3,
	}

	err := s.taskRepo.Create(ctx, task)
	if err != nil {
		logger.Error("创建充值任务失败, 订单ID: %d, 订单号: %s, 错误: %v",
			order.ID, order.OrderNumber, err)
		return err
	}

	logger.Info("创建充值任务成功, 任务ID: %d, 订单ID: %d, 订单号: %s",
		task.ID, order.ID, order.OrderNumber)
	return nil
}

func (s *rechargeService) ProcessRechargeTask(ctx context.Context, task *model.RechargeTask) error {
	logger.Info("开始处理充值任务, 任务ID: %d, 订单ID: %d",
		task.ID, task.OrderID)

	// 1. 更新任务状态为处理中
	task.Status = model.RechargeTaskStatusProcessing
	if err := s.taskRepo.UpdateStatus(ctx, task); err != nil {
		logger.Error("更新任务状态失败, 任务ID: %d, 订单ID: %d, 错误: %v",
			task.ID, task.OrderID, err)
		return fmt.Errorf("更新任务状态失败: %v", err)
	}
	logger.Info("更新任务状态为处理中, 任务ID: %d, 订单ID: %d", task.ID, task.OrderID)

	// 2. 获取订单信息
	order, err := s.taskRepo.GetOrderByTaskID(ctx, task.ID)
	if err != nil {
		logger.Error("获取订单信息失败, 任务ID: %d, 订单ID: %d, 错误: %v",
			task.ID, task.OrderID, err)
		task.Status = model.RechargeTaskStatusFailed
		task.ErrorMsg = fmt.Sprintf("获取订单信息失败: %v", err)
		s.taskRepo.UpdateStatus(ctx, task)
		return err
	}
	logger.Info("获取订单信息成功, 订单号: %s, 手机号: %s, 金额: %.2f",
		order.OrderNumber, order.Mobile, order.TotalPrice)

	// 3. 执行充值操作
	logger.Info("开始执行充值操作, 订单号: %s, 手机号: %s, 金额: %.2f, 平台ID: %d",
		order.OrderNumber, order.Mobile, order.TotalPrice, order.PlatformId)
	// TODO: 实现具体的充值逻辑
	// 这里需要根据不同的充值渠道实现不同的充值逻辑

	// 4. 更新任务状态
	task.Status = model.RechargeTaskStatusSuccess
	if err := s.taskRepo.UpdateStatus(ctx, task); err != nil {
		logger.Error("更新任务状态失败, 任务ID: %d, 订单号: %s, 错误: %v",
			task.ID, order.OrderNumber, err)
		return fmt.Errorf("更新任务状态失败: %v", err)
	}
	logger.Info("更新任务状态为成功, 任务ID: %d, 订单号: %s", task.ID, order.OrderNumber)

	// 5. 更新订单状态
	order.Status = model.OrderStatusSuccess
	if err := s.taskRepo.UpdateOrderStatus(ctx, order); err != nil {
		logger.Error("更新订单状态失败, 订单号: %s, 错误: %v", order.OrderNumber, err)
		return fmt.Errorf("更新订单状态失败: %v", err)
	}
	logger.Info("更新订单状态为成功, 订单号: %s, 手机号: %s", order.OrderNumber, order.Mobile)

	// 6. 发送通知
	// logger.Info("开始发送通知, 订单号: %s, 手机号: %s, 状态: %s",
	// 	order.OrderNumber, order.Mobile, s.convertOrderStatus(order.Status))
	if err := s.platformService.SendNotification(ctx, order); err != nil {
		logger.Error("发送通知失败, 订单号: %s, 错误: %v", order.OrderNumber, err)
		// 通知失败不影响主流程，只记录日志
	} else {
		logger.Info("发送通知成功, 订单号: %s", order.OrderNumber)
	}

	logger.Info("充值任务处理完成, 任务ID: %d, 订单号: %s, 手机号: %s",
		task.ID, order.OrderNumber, order.Mobile)
	return nil
}

func (s *rechargeService) GetPendingTasks(ctx context.Context, limit int) ([]*model.RechargeTask, error) {
	logger.Info("开始获取待处理任务, 限制数量: %d", limit)

	tasks, err := s.taskRepo.GetPendingTasks(ctx, limit)
	if err != nil {
		logger.Error("获取待处理任务失败, 错误: %v", err)
		return nil, err
	}

	logger.Info("获取待处理任务成功, 数量: %d", len(tasks))
	return tasks, nil
}
