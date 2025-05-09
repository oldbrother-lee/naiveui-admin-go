package repository

import (
	"context"
	"recharge-go/internal/model"
)

// OrderRepository 订单仓库接口
type OrderRepository interface {
	// Create 创建订单
	Create(ctx context.Context, order *model.Order) error
	// GetByID 根据ID获取订单
	GetByID(ctx context.Context, id int64) (*model.Order, error)
	// GetByOrderNumber 根据订单号获取订单
	GetByOrderNumber(ctx context.Context, orderNumber string) (*model.Order, error)
	// GetByCustomerID 根据客户ID获取订单列表
	GetByCustomerID(ctx context.Context, customerID int64, page, pageSize int) ([]*model.Order, int64, error)
	// UpdateStatus 更新订单状态
	UpdateStatus(ctx context.Context, id int64, status model.OrderStatus) error
	// UpdatePayInfo 更新支付信息
	UpdatePayInfo(ctx context.Context, id int64, payWay int, serialNumber string) error
	// UpdateAPIInfo 更新API信息
	UpdateAPIInfo(ctx context.Context, id int64, apiID int64, apiOrderNumber string, apiTradeNum string) error
	// UpdateFinishTime 更新完成时间
	UpdateFinishTime(ctx context.Context, id int64) error
	// UpdateRemark 更新备注
	UpdateRemark(ctx context.Context, id int64, remark string) error
	// Delete 删除订单
	Delete(ctx context.Context, id int64) error
	// GetOrderByOutTradeNum 根据外部交易号获取订单
	GetOrderByOutTradeNum(ctx context.Context, outTradeNum string) (*model.Order, error)
	// GetOrders 获取订单列表
	GetOrders(ctx context.Context, params map[string]interface{}, page, pageSize int) ([]*model.Order, int64, error)
}
