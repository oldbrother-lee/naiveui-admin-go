package repository

import (
	"context"
	"recharge-go/internal/model"

	"gorm.io/gorm"
)

// PlatformAPIRepository 平台接口仓储接口
type PlatformAPIRepository interface {
	// Create 创建平台接口
	Create(ctx context.Context, api *model.PlatformAPI) error
	// Update 更新平台接口
	Update(ctx context.Context, api *model.PlatformAPI) error
	// Delete 删除平台接口
	Delete(ctx context.Context, id int64) error
	// GetByID 根据ID获取平台接口
	GetByID(ctx context.Context, id int64) (*model.PlatformAPI, error)
	// List 获取平台接口列表
	List(ctx context.Context, page, pageSize int) ([]*model.PlatformAPI, int64, error)
}

// platformAPIRepository 平台接口仓储实现
type platformAPIRepository struct {
	db *gorm.DB
}

// NewPlatformAPIRepository 创建平台接口仓储实例
func NewPlatformAPIRepository(db *gorm.DB) PlatformAPIRepository {
	return &platformAPIRepository{db: db}
}

func (r *platformAPIRepository) Create(ctx context.Context, api *model.PlatformAPI) error {
	return r.db.WithContext(ctx).Create(api).Error
}

func (r *platformAPIRepository) Update(ctx context.Context, api *model.PlatformAPI) error {
	return r.db.WithContext(ctx).Save(api).Error
}

func (r *platformAPIRepository) Delete(ctx context.Context, id int64) error {
	return r.db.WithContext(ctx).Model(&model.PlatformAPI{}).Where("id = ?", id).Update("is_deleted", 1).Error
}

func (r *platformAPIRepository) GetByID(ctx context.Context, id int64) (*model.PlatformAPI, error) {
	var api model.PlatformAPI
	err := r.db.WithContext(ctx).Where("id = ? AND is_deleted = 0", id).First(&api).Error
	if err != nil {
		return nil, err
	}
	return &api, nil
}

func (r *platformAPIRepository) List(ctx context.Context, page, pageSize int) ([]*model.PlatformAPI, int64, error) {
	var apis []*model.PlatformAPI
	var total int64

	offset := (page - 1) * pageSize
	err := r.db.WithContext(ctx).Model(&model.PlatformAPI{}).
		Where("is_deleted = 0").
		Count(&total).
		Offset(offset).
		Limit(pageSize).
		Find(&apis).Error

	if err != nil {
		return nil, 0, err
	}

	return apis, total, nil
}
