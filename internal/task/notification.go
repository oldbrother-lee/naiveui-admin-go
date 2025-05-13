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
	logger.Info("开始处理通知队列")

	// 从队列中获取通知
	value, err := t.queue.Pop(ctx, t.queueName)
	if err != nil {
		logger.Error("从队列获取通知失败",
			"error", err,
			"queue_name", t.queueName,
		)
		return fmt.Errorf("pop from queue failed: %v", err)
	}

	if value == nil {
		logger.Info("队列为空，无通知需要处理",
			"queue_name", t.queueName,
		)
		return nil
	}

	logger.Info("从队列获取到通知",
		"queue_name", t.queueName,
		"value", value,
	)

	// 解析通知记录
	var record model.NotificationRecord
	if err := json.Unmarshal([]byte(value.(string)), &record); err != nil {
		logger.Error("解析通知记录失败",
			"error", err,
			"value", value,
		)
		return fmt.Errorf("unmarshal notification record failed: %v", err)
	}

	logger.Info("开始处理通知",
		"notification_id", record.ID,
		"order_id", record.OrderID,
		"platform_code", record.PlatformCode,
		"notification_type", record.NotificationType,
	)

	// 处理通知
	if err := t.notificationService.CreateNotification(ctx, &record); err != nil {
		logger.Error("处理通知失败",
			"error", err,
			"notification_id", record.ID,
			"order_id", record.OrderID,
			"platform_code", record.PlatformCode,
			"notification_type", record.NotificationType,
			"retry_count", record.RetryCount,
		)

		// 如果处理失败且未超过最大重试次数，则重新入队
		if record.RetryCount < t.maxRetries {
			// 使用指数退避策略计算重试间隔
			retryInterval := time.Duration(1<<uint(record.RetryCount)) * time.Minute
			record.NextRetryTime = time.Now().Add(retryInterval)

			logger.Info("准备重试通知",
				"notification_id", record.ID,
				"order_id", record.OrderID,
				"retry_count", record.RetryCount,
				"next_retry_time", record.NextRetryTime,
				"retry_interval", retryInterval,
			)

			// 序列化通知记录
			notificationJSON, err := json.Marshal(record)
			if err != nil {
				logger.Error("序列化通知记录失败",
					"error", err,
					"notification_id", record.ID,
				)
				return err
			}

			// 重新入队
			if err := t.queue.Push(ctx, t.queueName, string(notificationJSON)); err != nil {
				logger.Error("重新入队失败",
					"error", err,
					"notification_id", record.ID,
					"queue_name", t.queueName,
				)
				return err
			}

			logger.Info("通知已重新入队",
				"notification_id", record.ID,
				"queue_name", t.queueName,
				"next_retry_time", record.NextRetryTime,
			)
		} else {
			logger.Info("通知已达到最大重试次数，不再重试",
				"notification_id", record.ID,
				"order_id", record.OrderID,
				"retry_count", record.RetryCount,
				"max_retries", t.maxRetries,
			)
		}
	} else {
		logger.Info("通知处理成功",
			"notification_id", record.ID,
			"order_id", record.OrderID,
			"platform_code", record.PlatformCode,
			"notification_type", record.NotificationType,
		)
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
