package task

import (
	"context"
	"recharge-go/internal/service"
	"recharge-go/pkg/logger"
	"time"
)

// RechargeTask 充值任务处理器
type RechargeTask struct {
	rechargeService service.RechargeService
}

// NewRechargeTask 创建充值任务处理器
func NewRechargeTask(rechargeService service.RechargeService) *RechargeTask {
	return &RechargeTask{
		rechargeService: rechargeService,
	}
}

// Start 启动充值任务处理器
func (t *RechargeTask) Start(ctx context.Context) error {
	logger.Info("starting recharge task processor")

	// 启动主处理循环
	for {
		select {
		case <-ctx.Done():
			logger.Info("recharge task processor stopped")
			return nil
		default:
			// 从充值队列获取订单
			orderID, err := t.rechargeService.PopFromRechargeQueue(ctx)
			if err != nil {
				logger.Error("pop from recharge queue failed", "error", err)
				time.Sleep(time.Second) // 发生错误时暂停一秒
				continue
			}

			if orderID == 0 {
				// 如果队列为空，休眠 5 秒
				time.Sleep(5 * time.Second)
				continue
			}

			// 获取订单信息
			order, err := t.rechargeService.GetOrderByID(ctx, orderID)
			if err != nil {
				logger.Error("get order failed",
					"error", err,
					"order_id", orderID,
				)
				// 从处理中队列移除
				_ = t.rechargeService.RemoveFromProcessingQueue(ctx, orderID)
				continue
			}

			// 处理充值任务
			if err := t.rechargeService.ProcessRechargeTask(ctx, order); err != nil {
				logger.Error("process recharge task failed",
					"error", err,
					"order_id", orderID,
				)
				// 从处理中队列移除
				_ = t.rechargeService.RemoveFromProcessingQueue(ctx, orderID)
				continue
			}

			logger.Info("recharge task processed successfully",
				"order_id", orderID,
			)
		}
	}
}

// Stop 停止充值任务处理器
func (t *RechargeTask) Stop() {
	// 清理资源
	logger.Info("recharge task processor stopped")
}
