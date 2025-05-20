package repository

import (
	"recharge-go/internal/model"
	"recharge-go/pkg/database"

	"gorm.io/gorm"
)

type DaichongOrderRepository struct {
	db *gorm.DB
}

func NewDaichongOrderRepository() *DaichongOrderRepository {
	return &DaichongOrderRepository{db: database.DB}
}

// Create 新增订单
func (r *DaichongOrderRepository) Create(order *model.DaichongOrder) error {
	return r.db.Create(order).Error
}

// GetByID 根据ID查询订单
func (r *DaichongOrderRepository) GetByID(id int64) (*model.DaichongOrder, error) {
	var order model.DaichongOrder
	if err := r.db.First(&order, id).Error; err != nil {
		return nil, err
	}
	return &order, nil
}

// Update 更新订单
func (r *DaichongOrderRepository) Update(order *model.DaichongOrder) error {
	return r.db.Save(order).Error
}

// Delete 删除订单
func (r *DaichongOrderRepository) Delete(id int64) error {
	return r.db.Delete(&model.DaichongOrder{}, id).Error
}

// List 分页查询订单
func (r *DaichongOrderRepository) List(page, pageSize int) ([]*model.DaichongOrder, int64, error) {
	var orders []*model.DaichongOrder
	var total int64
	offset := (page - 1) * pageSize
	if err := r.db.Model(&model.DaichongOrder{}).Count(&total).Error; err != nil {
		return nil, 0, err
	}
	if err := r.db.Order("id desc").Limit(pageSize).Offset(offset).Find(&orders).Error; err != nil {
		return nil, 0, err
	}
	return orders, total, nil
}
