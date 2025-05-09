package repository

import (
	"context"
	"errors"
	"recharge-go/internal/model"
	"time"

	"gorm.io/gorm"
)

type orderRepository struct {
	db *gorm.DB
}

// NewOrderRepository 创建订单仓库
func NewOrderRepository(db *gorm.DB) OrderRepository {
	return &orderRepository{db: db}
}

// Create 创建订单
func (r *orderRepository) Create(ctx context.Context, order *model.Order) error {
	return r.db.WithContext(ctx).Create(order).Error
}

// GetByID 根据ID获取订单
func (r *orderRepository) GetByID(ctx context.Context, id int64) (*model.Order, error) {
	var order model.Order
	err := r.db.WithContext(ctx).First(&order, id).Error
	if err != nil {
		return nil, err
	}
	return &order, nil
}

// GetByOrderNumber 根据订单号获取订单
func (r *orderRepository) GetByOrderNumber(ctx context.Context, orderNumber string) (*model.Order, error) {
	var order model.Order
	err := r.db.WithContext(ctx).Where("order_number = ?", orderNumber).First(&order).Error
	if err != nil {
		return nil, err
	}
	return &order, nil
}

// GetByCustomerID 根据客户ID获取订单列表
func (r *orderRepository) GetByCustomerID(ctx context.Context, customerID int64, page, pageSize int) ([]*model.Order, int64, error) {
	var orders []*model.Order
	var total int64

	offset := (page - 1) * pageSize
	err := r.db.WithContext(ctx).Model(&model.Order{}).
		Where("customer_id = ?", customerID).
		Count(&total).
		Offset(offset).
		Limit(pageSize).
		Order("id desc").
		Find(&orders).Error
	if err != nil {
		return nil, 0, err
	}

	return orders, total, nil
}

// UpdateStatus 更新订单状态
func (r *orderRepository) UpdateStatus(ctx context.Context, id int64, status model.OrderStatus) error {
	return r.db.WithContext(ctx).Model(&model.Order{}).
		Where("id = ?", id).
		Updates(map[string]interface{}{
			"status":     status,
			"updated_at": time.Now(),
		}).Error
}

// UpdatePayInfo 更新支付信息
func (r *orderRepository) UpdatePayInfo(ctx context.Context, id int64, payWay int, serialNumber string) error {
	return r.db.WithContext(ctx).Model(&model.Order{}).
		Where("id = ?", id).
		Updates(map[string]interface{}{
			"pay_way":       payWay,
			"serial_number": serialNumber,
			"is_pay":        1,
			"pay_time":      time.Now(),
			"updated_at":    time.Now(),
		}).Error
}

// UpdateAPIInfo 更新API信息
func (r *orderRepository) UpdateAPIInfo(ctx context.Context, id int64, apiID int64, apiOrderNumber string, apiTradeNum string) error {
	return r.db.WithContext(ctx).Model(&model.Order{}).
		Where("id = ?", id).
		Updates(map[string]interface{}{
			"api_cur_id":       apiID,
			"api_order_number": apiOrderNumber,
			"api_trade_num":    apiTradeNum,
			"updated_at":       time.Now(),
		}).Error
}

// UpdateFinishTime 更新完成时间
func (r *orderRepository) UpdateFinishTime(ctx context.Context, id int64) error {
	now := time.Now()
	return r.db.WithContext(ctx).Model(&model.Order{}).
		Where("id = ?", id).
		Updates(map[string]interface{}{
			"finish_time": now,
			"updated_at":  now,
		}).Error
}

// UpdateRemark 更新备注
func (r *orderRepository) UpdateRemark(ctx context.Context, id int64, remark string) error {
	return r.db.WithContext(ctx).Model(&model.Order{}).
		Where("id = ?", id).
		Updates(map[string]interface{}{
			"remark":     remark,
			"updated_at": time.Now(),
		}).Error
}

// Delete 删除订单
func (r *orderRepository) Delete(ctx context.Context, id int64) error {
	return r.db.WithContext(ctx).Model(&model.Order{}).
		Where("id = ?", id).
		Updates(map[string]interface{}{
			"is_del":     1,
			"updated_at": time.Now(),
		}).Error
}

// GetOrderByOutTradeNum 根据外部交易号获取订单
func (r *orderRepository) GetOrderByOutTradeNum(ctx context.Context, outTradeNum string) (*model.Order, error) {
	var order model.Order
	err := r.db.Where("out_trade_num = ?", outTradeNum).First(&order).Error
	if err != nil {
		if errors.Is(err, gorm.ErrRecordNotFound) {
			return nil, nil
		}
		return nil, err
	}
	return &order, nil
}

// GetOrders 获取订单列表
func (r *orderRepository) GetOrders(ctx context.Context, params map[string]interface{}, page, pageSize int) ([]*model.Order, int64, error) {
	var orders []*model.Order
	var total int64

	query := r.db.WithContext(ctx).Model(&model.Order{}).Where("is_del = ?", 0)

	// 构建查询条件
	if orderNumber, ok := params["order_number"].(string); ok && orderNumber != "" {
		query = query.Where("order_number LIKE ?", "%"+orderNumber+"%")
	}
	if outTradeNum, ok := params["out_trade_num"].(string); ok && outTradeNum != "" {
		query = query.Where("out_trade_num LIKE ?", "%"+outTradeNum+"%")
	}
	if mobile, ok := params["mobile"].(string); ok && mobile != "" {
		query = query.Where("mobile LIKE ?", "%"+mobile+"%")
	}
	if status, ok := params["status"].(string); ok && status != "" {
		query = query.Where("status = ?", status)
	}
	if client, ok := params["client"].(int); ok && client > 0 {
		query = query.Where("client = ?", client)
	}
	if startTime, ok := params["start_time"].(string); ok && startTime != "" {
		query = query.Where("created_at >= ?", startTime)
	}
	if endTime, ok := params["end_time"].(string); ok && endTime != "" {
		query = query.Where("created_at <= ?", endTime)
	}

	// 获取总数
	if err := query.Count(&total).Error; err != nil {
		return nil, 0, err
	}

	// 分页查询
	offset := (page - 1) * pageSize
	if err := query.Order("id desc").
		Offset(offset).
		Limit(pageSize).
		Find(&orders).Error; err != nil {
		return nil, 0, err
	}

	return orders, total, nil
}
