package task

import (
	"context"
	"encoding/json"
	"fmt"
	model "recharge-go/internal/model/notification"
	"recharge-go/internal/service"
	svc "recharge-go/internal/service/notification"
	"recharge-go/pkg/logger"
	"recharge-go/pkg/queue"
	"time"
)

// NotificationTask 通知任务处理器
type NotificationTask struct {
	notificationService svc.NotificationService
	platformService     *service.PlatformService
	queue               queue.Queue
	queueName           string
	maxRetries          int
	batchSize           int
}

// NewNotificationTask 创建通知任务处理器
func NewNotificationTask(
	notificationService svc.NotificationService,
	platformService *service.PlatformService,
	queue queue.Queue,
	maxRetries int,
) *NotificationTask {
	return &NotificationTask{
		notificationService: notificationService,
		platformService:     platformService,
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
			} else {
				// 获取队列头部的通知
				value, err := t.queue.Peek(ctx, t.queueName)
				if err != nil {
					logger.Error("peek notification failed", "error", err)
					time.Sleep(time.Second)
					continue
				}

				if value == nil {
					// 如果队列为空，休眠 5 秒
					time.Sleep(5 * time.Second)
					continue
				}

				// 解析通知记录
				var record model.NotificationRecord
				valueStr, ok := value.(string)
				if !ok {
					logger.Error("invalid value type", "type", fmt.Sprintf("%T", value))
					time.Sleep(time.Second)
					continue
				}

				if err := json.Unmarshal([]byte(valueStr), &record); err != nil {
					logger.Error("unmarshal notification failed", "error", err)
					time.Sleep(time.Second)
					continue
				}

				// 根据重试时间计算休眠时间
				if !record.NextRetryTime.IsZero() {
					now := time.Now()
					if now.Before(record.NextRetryTime) {
						// 如果还未到重试时间，休眠到重试时间
						sleepDuration := record.NextRetryTime.Sub(now)
						logger.Info("sleep until next retry time",
							"notification_id", record.ID,
							"sleep_duration", sleepDuration,
							"next_retry_time", record.NextRetryTime,
						)
						time.Sleep(sleepDuration)
					} else {
						// 如果已到重试时间，短暂休眠后继续处理
						time.Sleep(100 * time.Millisecond)
					}
				} else {
					// 如果没有设置重试时间，短暂休眠后继续处理
					time.Sleep(100 * time.Millisecond)
				}
			}
		}
	}
}

// processNotifications 处理通知
func (t *NotificationTask) processNotifications(ctx context.Context) error {
	logger.Info("开始处理通知队列")

	// 先查看队列头部的通知
	value, err := t.queue.Peek(ctx, t.queueName)
	if err != nil {
		logger.Error("从队列查看通知失败",
			"error", err,
			"queue_name", t.queueName,
		)
		return fmt.Errorf("peek from queue failed: %v", err)
	}

	if value == nil {
		logger.Info("队列为空，无通知需要处理",
			"queue_name", t.queueName,
		)
		return nil
	}

	// 解析通知记录
	var record model.NotificationRecord
	valueStr, ok := value.(string)
	if !ok {
		logger.Error("队列值类型错误",
			"value_type", fmt.Sprintf("%T", value),
			"value", value,
		)
		return fmt.Errorf("invalid value type: %T", value)
	}

	// 解析 JSON 字符串
	if err := json.Unmarshal([]byte(valueStr), &record); err != nil {
		logger.Error("解析通知记录失败",
			"error", err,
			"value", valueStr,
		)
		return fmt.Errorf("unmarshal notification record failed: %v", err)
	}

	// 检查重试时间
	if !record.NextRetryTime.IsZero() && time.Now().Before(record.NextRetryTime) {
		logger.Info("通知还未到重试时间，等待处理",
			"notification_id", record.ID,
			"next_retry_time", record.NextRetryTime,
			"current_time", time.Now(),
		)
		return nil
	}

	// 如果到达重试时间，则从队列中取出通知
	value, err = t.queue.Pop(ctx, t.queueName)
	if err != nil {
		logger.Error("从队列获取通知失败",
			"error", err,
			"queue_name", t.queueName,
		)
		return fmt.Errorf("pop from queue failed: %v", err)
	}

	logger.Info("从队列获取到通知",
		"queue_name", t.queueName,
		"value", value,
		"value_type", fmt.Sprintf("%T", value),
	)

	// 从数据库获取最新的通知记录
	dbRecord, err := t.notificationService.GetNotification(ctx, record.ID)
	if err != nil {
		logger.Error("获取通知记录失败",
			"error", err,
			"notification_id", record.ID,
		)
		return err
	}

	// 使用数据库中的记录
	record = *dbRecord

	// 获取订单信息
	order, err := t.platformService.GetOrder(ctx, record.OrderID)
	if err != nil {
		logger.Error("获取订单信息失败",
			"error", err,
			"order_id", record.OrderID,
		)
		return err
	}

	// 发送通知
	if err := t.platformService.SendNotification(ctx, order); err != nil {
		logger.Error("发送通知失败",
			"error", err,
			"notification_id", record.ID,
			"order_id", record.OrderID,
			"platform_code", record.PlatformCode,
			"notification_type", record.NotificationType,
			"retry_count", record.RetryCount,
		)

		fmt.Println("发送通知最大重试次数1", record.RetryCount, t.maxRetries, record)

		// 如果处理失败且未超过最大重试次数，则重试
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

			// 更新通知记录状态
			if err := t.notificationService.UpdateNotificationStatus(ctx, record.ID, 4); err != nil {
				logger.Error("更新通知记录状态失败",
					"error", err,
					"notification_id", record.ID,
				)
				return err
			}

			// 重新入队
			if err := t.queue.Push(ctx, t.queueName, record); err != nil {
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

			// 更新通知状态为失败
			if err := t.notificationService.UpdateNotificationStatus(ctx, record.ID, 3); err != nil {
				logger.Error("更新通知状态失败",
					"error", err,
					"notification_id", record.ID,
				)
				return err
			}

			// 从队列中移除通知
			if err := t.queue.Remove(ctx, t.queueName, record); err != nil {
				logger.Error("从队列移除通知失败",
					"error", err,
					"notification_id", record.ID,
					"queue_name", t.queueName,
				)
				return err
			}

			logger.Info("通知已从队列中移除",
				"notification_id", record.ID,
				"queue_name", t.queueName,
			)
		}
	} else {
		// 更新通知状态为成功
		if err := t.notificationService.UpdateNotificationStatus(ctx, record.ID, 3); err != nil {
			logger.Error("更新通知状态失败",
				"error", err,
				"notification_id", record.ID,
			)
			return err
		}

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
