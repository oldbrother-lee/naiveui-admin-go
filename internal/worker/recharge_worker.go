package worker

import (
	"context"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/pkg/logger"
	"time"

	"go.uber.org/zap"
)

type RechargeWorker struct {
	rechargeSvc service.RechargeService
	taskRepo    repository.RechargeTaskRepository
}

func NewRechargeWorker(rechargeSvc service.RechargeService, taskRepo repository.RechargeTaskRepository) *RechargeWorker {
	return &RechargeWorker{
		rechargeSvc: rechargeSvc,
		taskRepo:    taskRepo,
	}
}

func (w *RechargeWorker) Start(ctx context.Context) {
	ticker := time.NewTicker(time.Second)
	defer ticker.Stop()

	for {
		select {
		case <-ctx.Done():
			return
		case <-ticker.C:
			w.processTasks(ctx)
		}
	}
}

func (w *RechargeWorker) processTasks(ctx context.Context) {
	// 获取待处理的任务
	tasks, err := w.taskRepo.GetPendingTasks(ctx, 10)
	if err != nil {
		logger.Log.Error("获取待处理任务失败", zap.Error(err))
		return
	}

	for _, task := range tasks {
		// 检查是否到达重试时间
		if task.NextRetryAt.After(time.Now()) {
			continue
		}

		// 处理任务
		if err := w.rechargeSvc.ProcessRechargeTask(ctx, task); err != nil {
			logger.Log.Error("处理充值任务失败",
				zap.Int64("task_id", task.ID),
				zap.Int64("order_id", task.OrderID),
				zap.Error(err))
		}
	}
}
