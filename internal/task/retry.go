package task

import (
	"context"
	"recharge-go/internal/service"
	"recharge-go/pkg/logger"
	"time"

	"go.uber.org/zap"
)

type RetryTask struct {
	retryService *service.RetryService
	stopChan     chan struct{}
}

func NewRetryTask(retryService *service.RetryService) *RetryTask {
	return &RetryTask{
		retryService: retryService,
		stopChan:     make(chan struct{}),
	}
}

func (t *RetryTask) Start() {
	go func() {
		ticker := time.NewTicker(5 * time.Minute)
		defer ticker.Stop()

		for {
			select {
			case <-t.stopChan:
				return
			case <-ticker.C:
				if err := t.retryService.ProcessRetries(context.Background()); err != nil {
					logger.Log.Error("处理重试任务失败", zap.Error(err))
				}
			}
		}
	}()
}

func (t *RetryTask) Stop() {
	close(t.stopChan)
	logger.Log.Info("重试任务已停止")
}
