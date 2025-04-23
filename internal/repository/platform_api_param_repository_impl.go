package repository

import (
	"context"
	"recharge-go/internal/model"

	"gorm.io/gorm"
)

type platformAPIParamRepository struct {
	db *gorm.DB
}

// NewPlatformAPIParamRepository 创建平台接口参数仓库实例
func NewPlatformAPIParamRepository(db *gorm.DB) PlatformAPIParamRepository {
	return &platformAPIParamRepository{db: db}
}

func (r *platformAPIParamRepository) Create(ctx context.Context, param *model.PlatformAPIParam) error {
	return r.db.WithContext(ctx).Create(param).Error
}

func (r *platformAPIParamRepository) Update(ctx context.Context, param *model.PlatformAPIParam) error {
	return r.db.WithContext(ctx).Save(param).Error
}

func (r *platformAPIParamRepository) Delete(ctx context.Context, id int64) error {
	return r.db.WithContext(ctx).Delete(&model.PlatformAPIParam{}, id).Error
}

func (r *platformAPIParamRepository) GetByID(ctx context.Context, id int64) (*model.PlatformAPIParam, error) {
	var param model.PlatformAPIParam
	err := r.db.WithContext(ctx).First(&param, id).Error
	if err != nil {
		return nil, err
	}
	return &param, nil
}

func (r *platformAPIParamRepository) List(ctx context.Context, apiID int64, page, pageSize int) ([]*model.PlatformAPIParam, int64, error) {
	var params []*model.PlatformAPIParam
	var total int64

	query := r.db.WithContext(ctx).Model(&model.PlatformAPIParam{})
	if apiID > 0 {
		query = query.Where("api_id = ?", apiID)
	}

	err := query.Count(&total).Error
	if err != nil {
		return nil, 0, err
	}

	err = query.Offset((page - 1) * pageSize).Limit(pageSize).Find(&params).Error
	if err != nil {
		return nil, 0, err
	}

	return params, total, nil
}
