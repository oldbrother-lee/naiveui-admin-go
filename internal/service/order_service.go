package service

import (
	"context"
	"fmt"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/internal/utils"
	"recharge-go/pkg/logger"
	"time"
)

// orderService 订单服务
type orderService struct {
	orderRepo       repository.OrderRepository
	rechargeService RechargeService
}

// NewOrderService 创建订单服务
func NewOrderService(orderRepo repository.OrderRepository, rechargeService RechargeService) *orderService {
	return &orderService{
		orderRepo:       orderRepo,
		rechargeService: rechargeService,
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
	if status == model.OrderStatusPendingRecharge {
		// 如果状态是待充值，创建充值任务
		if err := s.rechargeService.CreateRechargeTask(ctx, id); err != nil {
			return fmt.Errorf("create recharge task failed: %v", err)
		}
	}
	return s.orderRepo.UpdateStatus(ctx, id, status)
}

// ProcessOrderPayment 处理订单支付
func (s *orderService) ProcessOrderPayment(ctx context.Context, orderID int64, payWay int, serialNumber string) error {
	// 更新支付信息
	err := s.orderRepo.UpdatePayInfo(ctx, orderID, payWay, serialNumber)
	if err != nil {
		return err
	}

	// 更新订单状态为待充值
	return s.orderRepo.UpdateStatus(ctx, orderID, model.OrderStatusPendingRecharge)
}

// ProcessOrderRecharge 处理订单充值
func (s *orderService) ProcessOrderRecharge(ctx context.Context, orderID int64, apiID int64, apiOrderNumber string, apiTradeNum string) error {
	// 更新API信息
	err := s.orderRepo.UpdateAPIInfo(ctx, orderID, apiID, apiOrderNumber, apiTradeNum)
	if err != nil {
		return err
	}

	// 更新订单状态为充值中
	return s.orderRepo.UpdateStatus(ctx, orderID, model.OrderStatusRecharging)
}

// ProcessOrderSuccess 处理订单成功
func (s *orderService) ProcessOrderSuccess(ctx context.Context, orderID int64) error {
	// 更新完成时间
	err := s.orderRepo.UpdateFinishTime(ctx, orderID)
	if err != nil {
		return err
	}

	// 更新订单状态为成功
	return s.orderRepo.UpdateStatus(ctx, orderID, model.OrderStatusSuccess)
}

// ProcessOrderFail 处理订单失败
func (s *orderService) ProcessOrderFail(ctx context.Context, orderID int64, remark string) error {
	// 更新备注
	err := s.orderRepo.UpdateRemark(ctx, orderID, remark)
	if err != nil {
		return err
	}

	// 更新订单状态为失败
	return s.orderRepo.UpdateStatus(ctx, orderID, model.OrderStatusFailed)
}

// ProcessOrderRefund 处理订单退款
func (s *orderService) ProcessOrderRefund(ctx context.Context, orderID int64, remark string) error {
	// 更新备注
	err := s.orderRepo.UpdateRemark(ctx, orderID, remark)
	if err != nil {
		return err
	}

	// 更新订单状态为已退款
	return s.orderRepo.UpdateStatus(ctx, orderID, model.OrderStatusRefunded)
}

// ProcessOrderCancel 处理订单取消
func (s *orderService) ProcessOrderCancel(ctx context.Context, orderID int64, remark string) error {
	// 更新备注
	err := s.orderRepo.UpdateRemark(ctx, orderID, remark)
	if err != nil {
		return err
	}

	// 更新订单状态为已取消
	return s.orderRepo.UpdateStatus(ctx, orderID, model.OrderStatusCancelled)
}

// ProcessOrderSplit 处理订单拆单
func (s *orderService) ProcessOrderSplit(ctx context.Context, orderID int64, remark string) error {
	// 更新备注
	err := s.orderRepo.UpdateRemark(ctx, orderID, remark)
	if err != nil {
		return err
	}

	// 更新订单状态为已拆单
	return s.orderRepo.UpdateStatus(ctx, orderID, model.OrderStatusSplit)
}

// ProcessOrderPartial 处理订单部分充值
func (s *orderService) ProcessOrderPartial(ctx context.Context, orderID int64, remark string) error {
	// 更新备注
	err := s.orderRepo.UpdateRemark(ctx, orderID, remark)
	if err != nil {
		return err
	}

	// 更新订单状态为部分充值
	return s.orderRepo.UpdateStatus(ctx, orderID, model.OrderStatusPartial)
}

// generateOrderNumber 生成订单号
func generateOrderNumber() string {
	return "P" + time.Now().Format("20060102150405") + utils.RandString(6)
}

// GetOrderByOutTradeNum 根据外部交易号获取订单
func (s *orderService) GetOrderByOutTradeNum(ctx context.Context, outTradeNum string) (*model.Order, error) {
	return s.orderRepo.GetOrderByOutTradeNum(ctx, outTradeNum)
}

// GetOrders 获取订单列表
func (s *orderService) GetOrders(ctx context.Context, params map[string]interface{}, page, pageSize int) ([]*model.Order, int64, error) {
	return s.orderRepo.GetOrders(ctx, params, page, pageSize)
}
