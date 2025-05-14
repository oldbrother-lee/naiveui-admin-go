package service

import (
	"context"
	"fmt"
	"recharge-go/internal/model"
	notificationModel "recharge-go/internal/model/notification"
	"recharge-go/internal/repository"
	notificationRepo "recharge-go/internal/repository/notification"
	"recharge-go/internal/utils"
	"recharge-go/pkg/logger"
	"recharge-go/pkg/queue"
	"time"
)

// OrderService 订单服务接口
type OrderService interface {
	// CreateOrder 创建订单
	CreateOrder(ctx context.Context, order *model.Order) error
	// GetOrderByID 根据ID获取订单
	GetOrderByID(ctx context.Context, id int64) (*model.Order, error)
	// GetOrderByOrderNumber 根据订单号获取订单
	GetOrderByOrderNumber(ctx context.Context, orderNumber string) (*model.Order, error)
	// GetOrdersByCustomerID 根据客户ID获取订单列表
	GetOrdersByCustomerID(ctx context.Context, customerID int64, page, pageSize int) ([]*model.Order, int64, error)
	// UpdateOrderStatus 更新订单状态
	UpdateOrderStatus(ctx context.Context, id int64, status model.OrderStatus) error
	// ProcessOrderPayment 处理订单支付
	ProcessOrderPayment(ctx context.Context, orderID int64, payWay int, serialNumber string) error
	// ProcessOrderRecharge 处理订单充值
	ProcessOrderRecharge(ctx context.Context, orderID int64, apiID int64, apiOrderNumber string, apiTradeNum string) error
	// ProcessOrderSuccess 处理订单成功
	ProcessOrderSuccess(ctx context.Context, orderID int64) error
	// ProcessOrderFail 处理订单失败
	ProcessOrderFail(ctx context.Context, orderID int64, remark string) error
	// ProcessOrderRefund 处理订单退款
	ProcessOrderRefund(ctx context.Context, orderID int64, remark string) error
	// ProcessOrderCancel 处理订单取消
	ProcessOrderCancel(ctx context.Context, orderID int64, remark string) error
	// ProcessOrderSplit 处理订单拆单
	ProcessOrderSplit(ctx context.Context, orderID int64, remark string) error
	// ProcessOrderPartial 处理订单部分充值
	ProcessOrderPartial(ctx context.Context, orderID int64, remark string) error
	// GetOrderByOutTradeNum 根据外部交易号获取订单
	GetOrderByOutTradeNum(ctx context.Context, outTradeNum string) (*model.Order, error)
	// GetOrders 获取订单列表
	GetOrders(ctx context.Context, params map[string]interface{}, page, pageSize int) ([]*model.Order, int64, error)
	// SetRechargeService 设置充值服务
	SetRechargeService(rechargeService RechargeService)
}

// orderService 订单服务实现
type orderService struct {
	orderRepo        repository.OrderRepository
	rechargeService  RechargeService
	notificationRepo notificationRepo.Repository
	queue            queue.Queue
}

// NewOrderService 创建订单服务实例
func NewOrderService(
	orderRepo repository.OrderRepository,
	rechargeService RechargeService,
	notificationRepo notificationRepo.Repository,
	queue queue.Queue,
) OrderService {
	return &orderService{
		orderRepo:        orderRepo,
		rechargeService:  rechargeService,
		notificationRepo: notificationRepo,
		queue:            queue,
	}
}

// CreateOrder 创建订单
func (s *orderService) CreateOrder(ctx context.Context, order *model.Order) error {
	// 生成订单号
	order.OrderNumber = generateOrderNumber()
	order.CreateTime = time.Now()
	order.UpdatedAt = time.Now()
	order.Status = model.OrderStatusPendingPayment
	order.IsDel = 0

	if err := s.orderRepo.Create(ctx, order); err != nil {
		return err
	}

	// 创建成功后，将订单推送到充值队列
	if err := s.rechargeService.PushToRechargeQueue(ctx, order.ID); err != nil {
		logger.Error("推送订单到充值队列失败: %v", err)
		// 这里可以选择是否返回错误，因为订单已经创建成功
	}

	return nil
}

// GetOrderByID 根据ID获取订单
func (s *orderService) GetOrderByID(ctx context.Context, id int64) (*model.Order, error) {
	return s.orderRepo.GetByID(ctx, id)
}

// GetOrderByOrderNumber 根据订单号获取订单
func (s *orderService) GetOrderByOrderNumber(ctx context.Context, orderNumber string) (*model.Order, error) {
	return s.orderRepo.GetByOrderNumber(ctx, orderNumber)
}

// GetOrdersByCustomerID 根据客户ID获取订单列表
func (s *orderService) GetOrdersByCustomerID(ctx context.Context, customerID int64, page, pageSize int) ([]*model.Order, int64, error) {
	return s.orderRepo.GetByCustomerID(ctx, customerID, page, pageSize)
}

// UpdateOrderStatus 更新订单状态
func (s *orderService) UpdateOrderStatus(ctx context.Context, id int64, status model.OrderStatus) error {
	fmt.Println(id, "开始更新订单状态orderID++++++++")
	logger.Info("开始更新订单状态",
		"order_id", id,
		"new_status", status,
	)

	// 获取订单信息
	order, err := s.orderRepo.GetByID(ctx, id)
	if err != nil {
		logger.Error("获取订单信息失败",
			"error", err,
			"order_id", id,
		)
		return fmt.Errorf("get order failed: %v", err)
	}

	logger.Info("获取到订单信息",
		"order_id", id,
		"current_status", order.Status,
		"new_status", status,
	)

	// 如果状态没有变化，直接返回
	if order.Status == status {
		logger.Info("订单状态未发生变化，无需更新",
			"order_id", id,
			"status", status,
		)
		return nil
	}

	// 更新订单状态
	if err := s.orderRepo.UpdateStatus(ctx, id, status); err != nil {
		logger.Error("更新订单状态失败",
			"error", err,
			"order_id", id,
			"old_status", order.Status,
			"new_status", status,
		)
		return fmt.Errorf("update order status failed: %v", err)
	}

	logger.Info("订单状态更新成功",
		"order_id", id,
		"old_status", order.Status,
		"new_status", status,
	)

	// 创建通知记录
	notification := &notificationModel.NotificationRecord{
		OrderID:          id,
		PlatformCode:     order.PlatformCode,
		NotificationType: "order_status_changed",
		Content:          fmt.Sprintf("订单状态已更新为: %d", status),
		Status:           1, // 待处理
	}

	// 保存通知记录到数据库
	if err := s.notificationRepo.Create(ctx, notification); err != nil {
		logger.Error("创建通知记录失败",
			"error", err,
			"order_id", id,
			"platform_code", order.PlatformCode,
			"notification_type", notification.NotificationType,
		)
		// 通知失败不影响订单状态更新
	} else {
		logger.Info("通知记录创建成功",
			"order_id", id,
			"notification_id", notification.ID,
			"platform_code", order.PlatformCode,
		)
	}

	// 将通知记录添加到队列
	if err := s.queue.Push(ctx, "notification_queue", notification); err != nil {
		logger.Error("推送通知到队列失败",
			"error", err,
			"order_id", id,
			"notification_id", notification.ID,
			"queue_name", "notification_queue",
		)
		// 队列推送失败不影响订单状态更新
	} else {
		logger.Info("通知已推送到队列",
			"order_id", id,
			"notification_id", notification.ID,
			"queue_name", "notification_queue",
		)
	}

	return nil
}

// ProcessOrderPayment 处理订单支付
func (s *orderService) ProcessOrderPayment(ctx context.Context, orderID int64, payWay int, serialNumber string) error {
	// 更新支付信息
	err := s.orderRepo.UpdatePayInfo(ctx, orderID, payWay, serialNumber)
	if err != nil {
		return err
	}

	// 更新订单状态为待充值
	return s.UpdateOrderStatus(ctx, orderID, model.OrderStatusPendingRecharge)
}

// ProcessOrderRecharge 处理订单充值
func (s *orderService) ProcessOrderRecharge(ctx context.Context, orderID int64, apiID int64, apiOrderNumber string, apiTradeNum string) error {
	// 更新API信息
	err := s.orderRepo.UpdateAPIInfo(ctx, orderID, apiID, apiOrderNumber, apiTradeNum)
	if err != nil {
		return err
	}

	// 更新订单状态为充值中
	return s.UpdateOrderStatus(ctx, orderID, model.OrderStatusRecharging)
}

// ProcessOrderSuccess 处理订单成功
func (s *orderService) ProcessOrderSuccess(ctx context.Context, orderID int64) error {
	// 更新完成时间
	err := s.orderRepo.UpdateFinishTime(ctx, orderID)
	if err != nil {
		return err
	}

	// 更新订单状态为成功
	return s.UpdateOrderStatus(ctx, orderID, model.OrderStatusSuccess)
}

// ProcessOrderFail 处理订单失败
func (s *orderService) ProcessOrderFail(ctx context.Context, orderID int64, remark string) error {
	// 更新备注
	err := s.orderRepo.UpdateRemark(ctx, orderID, remark)
	if err != nil {
		return err
	}

	// 更新订单状态为失败
	return s.UpdateOrderStatus(ctx, orderID, model.OrderStatusFailed)
}

// ProcessOrderRefund 处理订单退款
func (s *orderService) ProcessOrderRefund(ctx context.Context, orderID int64, remark string) error {
	// 更新备注
	err := s.orderRepo.UpdateRemark(ctx, orderID, remark)
	if err != nil {
		return err
	}

	// 更新订单状态为已退款
	return s.UpdateOrderStatus(ctx, orderID, model.OrderStatusRefunded)
}

// ProcessOrderCancel 处理订单取消
func (s *orderService) ProcessOrderCancel(ctx context.Context, orderID int64, remark string) error {
	// 更新备注
	err := s.orderRepo.UpdateRemark(ctx, orderID, remark)
	if err != nil {
		return err
	}

	// 更新订单状态为已取消
	return s.UpdateOrderStatus(ctx, orderID, model.OrderStatusCancelled)
}

// ProcessOrderSplit 处理订单拆单
func (s *orderService) ProcessOrderSplit(ctx context.Context, orderID int64, remark string) error {
	// 更新备注
	err := s.orderRepo.UpdateRemark(ctx, orderID, remark)
	if err != nil {
		return err
	}

	// 更新订单状态为已拆单
	return s.UpdateOrderStatus(ctx, orderID, model.OrderStatusSplit)
}

// ProcessOrderPartial 处理订单部分充值
func (s *orderService) ProcessOrderPartial(ctx context.Context, orderID int64, remark string) error {
	// 更新备注
	err := s.orderRepo.UpdateRemark(ctx, orderID, remark)
	if err != nil {
		return err
	}

	// 更新订单状态为部分充值
	return s.UpdateOrderStatus(ctx, orderID, model.OrderStatusPartial)
}

// GetOrderByOutTradeNum 根据外部交易号获取订单
func (s *orderService) GetOrderByOutTradeNum(ctx context.Context, outTradeNum string) (*model.Order, error) {
	return s.orderRepo.GetOrderByOutTradeNum(ctx, outTradeNum)
}

// GetOrders 获取订单列表
func (s *orderService) GetOrders(ctx context.Context, params map[string]interface{}, page, pageSize int) ([]*model.Order, int64, error) {
	return s.orderRepo.GetOrders(ctx, params, page, pageSize)
}

// generateOrderNumber 生成订单号
func generateOrderNumber() string {
	return "P" + time.Now().Format("20060102150405") + utils.RandString(6)
}

// SetRechargeService 设置充值服务
func (s *orderService) SetRechargeService(rechargeService RechargeService) {
	s.rechargeService = rechargeService
}
