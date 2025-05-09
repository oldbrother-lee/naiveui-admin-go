package service

import (
	"context"
	"recharge-go/pkg/logger"
	"time"

	"go.uber.org/zap"
)

// RechargeWorker 充值工作器
type RechargeWorker struct {
	rechargeService RechargeService
	stopChan        chan struct{}
}

// NewRechargeWorker 创建充值工作器
func NewRechargeWorker(rechargeService RechargeService) *RechargeWorker {
	return &RechargeWorker{
		rechargeService: rechargeService,
		stopChan:        make(chan struct{}),
	}
}

// Start 启动工作器
func (w *RechargeWorker) Start() {
	logger.Log.Info("充值工作器启动")
	ticker := time.NewTicker(5 * time.Second)
	defer ticker.Stop()

	for {
		select {
		case <-ticker.C:
			w.processTasks()
		case <-w.stopChan:
			logger.Log.Info("充值工作器停止")
			return
		}
	}
}

// Stop 停止工作器
func (w *RechargeWorker) Stop() {
	close(w.stopChan)
}

// processTasks 处理待处理的任务
func (w *RechargeWorker) processTasks() {
	ctx := context.Background()
	tasks, err := w.rechargeService.GetPendingTasks(ctx, 10)
	if err != nil {
		logger.Log.Error("获取待处理任务失败", zap.Error(err))
		return
	}

	for _, task := range tasks {
		if err := w.rechargeService.ProcessRechargeTask(ctx, task); err != nil {
			logger.Log.Error("处理充值任务失败",
				zap.Int64("task_id", task.ID),
				zap.Error(err))
		}
	}
}
