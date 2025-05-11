package worker

import (
	"context"
	"recharge-go/internal/service"
	"recharge-go/pkg/logger"
	"time"
)

// RechargeWorker 充值工作器
type RechargeWorker struct {
	rechargeService service.RechargeService
	stopChan        chan struct{}
}

// NewRechargeWorker 创建充值工作器
func NewRechargeWorker(rechargeService service.RechargeService) *RechargeWorker {
	return &RechargeWorker{
		rechargeService: rechargeService,
		stopChan:        make(chan struct{}),
	}
}

// Start 启动工作器
func (w *RechargeWorker) Start() {
	logger.Info("充值工作器启动")
	go w.processQueue()
}

// Stop 停止工作器
func (w *RechargeWorker) Stop() {
	logger.Info("充值工作器停止")
	close(w.stopChan)
}

// processQueue 处理队列
func (w *RechargeWorker) processQueue() {
	ctx := context.Background()
	for {
		select {
		case <-w.stopChan:
			return
		default:
			// 从队列中获取订单ID
			orderID, err := w.rechargeService.PopFromRechargeQueue(ctx)
			if err != nil {
				logger.Error("从队列获取订单失败: %v", err)
				time.Sleep(time.Second)
				continue
			}

			// 获取订单信息
			order, err := w.rechargeService.GetOrderByID(ctx, orderID)
			if err != nil {
				logger.Error("获取订单信息失败, order_id: %d, error: %v", orderID, err)
				continue
			}

			// 处理充值任务
			if err := w.rechargeService.ProcessRechargeTask(ctx, order); err != nil {
				logger.Error("处理充值任务失败, order_id: %d, error: %v", orderID, err)
				// 如果处理失败，将订单重新放入队列
				if err := w.rechargeService.PushToRechargeQueue(ctx, orderID); err != nil {
					logger.Error("重新放入队列失败, order_id: %d, error: %v", orderID, err)
				}
				time.Sleep(time.Second)
				continue
			}

			logger.Info("充值任务处理完成, order_id: %d", orderID)
		}
	}
}
