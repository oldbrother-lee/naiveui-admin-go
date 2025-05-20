package service

import (
	"context"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
)

type DaichongOrderService struct {
	repo *repository.DaichongOrderRepository
}

func NewDaichongOrderService(repo *repository.DaichongOrderRepository) *DaichongOrderService {
	return &DaichongOrderService{repo: repo}
}

// Create 新增订单
func (s *DaichongOrderService) Create(ctx context.Context, order *model.DaichongOrder) error {
	return s.repo.Create(order)
}

// GetByID 根据ID查询订单
func (s *DaichongOrderService) GetByID(ctx context.Context, id int64) (*model.DaichongOrder, error) {
	return s.repo.GetByID(id)
}

// Update 更新订单
func (s *DaichongOrderService) Update(ctx context.Context, order *model.DaichongOrder) error {
	return s.repo.Update(order)
}

// Delete 删除订单
func (s *DaichongOrderService) Delete(ctx context.Context, id int64) error {
	return s.repo.Delete(id)
}

// List 分页查询订单
func (s *DaichongOrderService) List(ctx context.Context, page, pageSize int) ([]*model.DaichongOrder, int64, error) {
	return s.repo.List(page, pageSize)
}
