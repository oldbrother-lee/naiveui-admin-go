package repository

import (
	"context"
	"errors"
	"fmt"
	"recharge-go/internal/model"
	"recharge-go/pkg/logger"
	"strconv"
	"time"

	"gorm.io/gorm"
)

var (
	ErrOrderNotFound = errors.New("order not found")
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
	UpdateAPIInfo(ctx context.Context, id int64, apiID int64, apiName string, apiParamName string) error
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
	// GetByStatus 根据状态获取订单列表
	GetByStatus(ctx context.Context, status model.OrderStatus) ([]*model.Order, error)
	// GetByOrderID 根据订单号获取订单
	GetByOrderID(ctx context.Context, orderID string) (*model.Order, error)
	// UpdatePlatformID 更新订单支付平台ID和API ID
	UpdatePlatformID(ctx context.Context, orderID int64, platformID int64, ParamID int64) error
}

// OrderRepositoryImpl 订单仓库实现
type OrderRepositoryImpl struct {
	db *gorm.DB
}

// NewOrderRepository 创建订单仓库
func NewOrderRepository(db *gorm.DB) *OrderRepositoryImpl {
	return &OrderRepositoryImpl{db: db}
}

// GetByStatus 根据状态获取订单列表
func (r *OrderRepositoryImpl) GetByStatus(ctx context.Context, status model.OrderStatus) ([]*model.Order, error) {
	var orders []*model.Order
	if err := r.db.Where("status = ?", status).Find(&orders).Error; err != nil {
		return nil, err
	}
	return orders, nil
}

// Create 创建订单
func (r *OrderRepositoryImpl) Create(ctx context.Context, order *model.Order) error {
	return r.db.Create(order).Error
}

// GetByID 根据ID获取订单
func (r *OrderRepositoryImpl) GetByID(ctx context.Context, id int64) (*model.Order, error) {
	var order model.Order
	if err := r.db.First(&order, id).Error; err != nil {
		return nil, err
	}
	return &order, nil
}

// GetByOrderNumber 根据订单号获取订单
func (r *OrderRepositoryImpl) GetByOrderNumber(ctx context.Context, orderNumber string) (*model.Order, error) {
	var order model.Order
	if err := r.db.Where("order_number = ?", orderNumber).First(&order).Error; err != nil {
		return nil, err
	}
	return &order, nil
}

// GetByCustomerID 根据客户ID获取订单列表
func (r *OrderRepositoryImpl) GetByCustomerID(ctx context.Context, customerID int64, page, pageSize int) ([]*model.Order, int64, error) {
	var orders []*model.Order
	var total int64

	if err := r.db.Model(&model.Order{}).Where("customer_id = ?", customerID).Count(&total).Error; err != nil {
		return nil, 0, err
	}

	offset := (page - 1) * pageSize
	if err := r.db.Where("customer_id = ?", customerID).Offset(offset).Limit(pageSize).Find(&orders).Error; err != nil {
		return nil, 0, err
	}

	return orders, total, nil
}

// UpdateStatus 更新订单状态
func (r *OrderRepositoryImpl) UpdateStatus(ctx context.Context, id int64, status model.OrderStatus) error {
	fmt.Println(id, "更新订单状态id++++++++", status)
	return r.db.Model(&model.Order{}).Where("id = ?", id).Update("status", status).Error
}

// UpdatePayInfo 更新支付信息
func (r *OrderRepositoryImpl) UpdatePayInfo(ctx context.Context, id int64, payWay int, serialNumber string) error {
	return r.db.Model(&model.Order{}).Where("id = ?", id).Updates(map[string]interface{}{
		"pay_way":       payWay,
		"serial_number": serialNumber,
	}).Error
}

// UpdateAPIInfo 更新API信息
func (r *OrderRepositoryImpl) UpdateAPIInfo(ctx context.Context, id int64, apiID int64, apiName string, apiParamName string) error {
	return r.db.WithContext(ctx).
		Model(&model.Order{}).
		Where("id = ?", id).
		Updates(map[string]interface{}{
			"api_cur_id":       apiID,
			"api_cur_param_id": apiParamName,
		}).Error
}

// UpdateFinishTime 更新完成时间
func (r *OrderRepositoryImpl) UpdateFinishTime(ctx context.Context, id int64) error {
	return r.db.Model(&model.Order{}).Where("id = ?", id).Update("finish_time", time.Now()).Error
}

// UpdateRemark 更新备注
func (r *OrderRepositoryImpl) UpdateRemark(ctx context.Context, id int64, remark string) error {
	return r.db.Model(&model.Order{}).Where("id = ?", id).Update("remark", remark).Error
}

// Delete 删除订单
func (r *OrderRepositoryImpl) Delete(ctx context.Context, id int64) error {
	return r.db.Delete(&model.Order{}, id).Error
}

// GetOrderByOutTradeNum 根据外部交易号获取订单
func (r *OrderRepositoryImpl) GetOrderByOutTradeNum(ctx context.Context, outTradeNum string) (*model.Order, error) {
	var order model.Order
	if err := r.db.Where("out_trade_num = ?", outTradeNum).First(&order).Error; err != nil {
		return nil, ErrOrderNotFound
	}
	fmt.Println(order, "gggg")
	return &order, nil
}

// GetOrders 获取订单列表
func (r *OrderRepositoryImpl) GetOrders(ctx context.Context, params map[string]interface{}, page, pageSize int) ([]*model.Order, int64, error) {
	var orders []*model.Order
	var total int64

	query := r.db.Model(&model.Order{})

	// 添加查询条件
	for key, value := range params {
		// 将 interface{} 转换为 string
		strValue, ok := value.(string)
		if !ok || strValue == "" {
			continue
		}

		switch key {
		case "client":
			// 将字符串转换为整数
			clientID, err := strconv.ParseInt(strValue, 10, 64)
			if err != nil {
				logger.Error("解析client参数失败: %v", err)
				continue
			}
			if clientID > 0 {
				query = query.Where("client = ?", clientID)
			}
		case "status":
			// 将字符串转换为整数
			status, err := strconv.ParseInt(strValue, 10, 64)
			if err != nil {
				logger.Error("解析status参数失败: %v", err)
				continue
			}
			if status >= 0 {
				query = query.Where("status = ?", status)
			}
		case "order_number":
			query = query.Where("order_number LIKE ?", "%"+strValue+"%")
		case "mobile":
			query = query.Where("mobile LIKE ?", "%"+strValue+"%")
		case "start_time":
			query = query.Where("create_time >= ?", strValue)
		case "end_time":
			query = query.Where("create_time <= ?", strValue)
		default:
			// 对于其他字段，使用精确匹配
			query = query.Where(key+" = ?", strValue)
		}
	}

	if err := query.Count(&total).Error; err != nil {
		return nil, 0, err
	}

	offset := (page - 1) * pageSize
	if err := query.Order("create_time DESC").Offset(offset).Limit(pageSize).Find(&orders).Error; err != nil {
		return nil, 0, err
	}

	return orders, total, nil
}

// GetByOrderID 根据订单号获取订单
func (r *OrderRepositoryImpl) GetByOrderID(ctx context.Context, orderID string) (*model.Order, error) {
	var order model.Order
	if err := r.db.Where("order_number = ?", orderID).First(&order).Error; err != nil {
		return nil, err
	}
	return &order, nil
}

// UpdatePlatformID 更新订单支付平台ID和API ID
func (r *OrderRepositoryImpl) UpdatePlatformID(ctx context.Context, orderID int64, platformID int64, ParamID int64) error {
	return r.db.WithContext(ctx).
		Model(&model.Order{}).
		Where("id = ?", orderID).
		Updates(map[string]interface{}{
			"platform_id":      platformID,
			"api_cur_id":       platformID, // 使用相同的platformID作为api_id
			"api_cur_param_id": ParamID,
		}).Error
}
