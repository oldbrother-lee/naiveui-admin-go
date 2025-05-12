package task

import (
	"context"
	"encoding/json"
	"fmt"
	model "recharge-go/internal/model/notification"
	svc "recharge-go/internal/service/notification"
	"recharge-go/pkg/logger"
	"recharge-go/pkg/queue"
	"time"
)

// NotificationTask 通知任务处理器
type NotificationTask struct {
	notificationService svc.NotificationService
	queue               queue.Queue
	queueName           string
	maxRetries          int
	batchSize           int
}

// NewNotificationTask 创建通知任务处理器
func NewNotificationTask(
	notificationService svc.NotificationService,
	queue queue.Queue,
	maxRetries int,
) *NotificationTask {
	return &NotificationTask{
		notificationService: notificationService,
		queue:               queue,
		queueName:           "notification_queue",
		maxRetries:          maxRetries,
		batchSize:           10, // 每次处理的通知数量
	}
}

// Start 启动通知任务处理器
func (t *NotificationTask) Start(ctx context.Context) error {
	logger.Info("starting notification task processor")

	// 启动重试任务
	go t.startRetryTask(ctx)

	// 启动主处理循环
	for {
		select {
		case <-ctx.Done():
			logger.Info("notification task processor stopped")
			return nil
		default:
			if err := t.processNotifications(ctx); err != nil {
				logger.Error("process notifications failed", "error", err)
				time.Sleep(time.Second) // 发生错误时暂停一秒
			}
		}
	}
}

// processNotifications 处理通知
func (t *NotificationTask) processNotifications(ctx context.Context) error {
	// 从队列中获取通知
	value, err := t.queue.Pop(ctx, t.queueName)
	if err != nil {
		return fmt.Errorf("pop from queue failed: %v", err)
	}

	// 解析通知记录
	var record model.NotificationRecord
	if err := json.Unmarshal([]byte(value.(string)), &record); err != nil {
		return fmt.Errorf("unmarshal notification record failed: %v", err)
	}

	// 处理通知
	if err := t.notificationService.CreateNotification(ctx, &record); err != nil {
		logger.Error("process notification failed",
			"error", err,
			"record_id", record.ID,
			"platform", record.PlatformCode,
			"type", record.NotificationType,
		)

		// 如果处理失败且未超过最大重试次数，则重新入队
		if record.RetryCount < t.maxRetries {
			// 使用指数退避策略计算重试间隔
			retryInterval := time.Duration(1<<uint(record.RetryCount)) * time.Minute
			if err := t.notificationService.RetryFailedNotification(ctx, record.ID); err != nil {
				logger.Error("retry notification failed",
					"error", err,
					"record_id", record.ID,
					"retry_interval", retryInterval,
				)
			}
		}
	}

	return nil
}

// startRetryTask 启动重试任务
func (t *NotificationTask) startRetryTask(ctx context.Context) {
	ticker := time.NewTicker(5 * time.Minute)
	defer ticker.Stop()

	for {
		select {
		case <-ctx.Done():
			return
		case <-ticker.C:
			// 获取所有待重试的通知记录
			records, _, err := t.notificationService.ListNotifications(ctx, map[string]interface{}{
				"status": 4, // 失败状态
			}, 1, t.batchSize)
			if err != nil {
				logger.Error("get failed notifications failed", "error", err)
				continue
			}

			// 重试每条记录
			for _, record := range records {
				if record.RetryCount < t.maxRetries {
					if err := t.notificationService.RetryFailedNotification(ctx, record.ID); err != nil {
						logger.Error("retry notification failed",
							"error", err,
							"record_id", record.ID,
						)
					}
				}
			}
		}
	}
}

// Stop 停止通知任务处理器
func (t *NotificationTask) Stop() {
	// 清理资源
	logger.Info("notification task processor stopped")
}
