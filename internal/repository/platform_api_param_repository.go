package repository

import (
	"context"
	"recharge-go/internal/model"
)

// PlatformAPIParamRepository 平台接口参数仓库接口
type PlatformAPIParamRepository interface {
	// Create 创建平台接口参数
	Create(ctx context.Context, param *model.PlatformAPIParam) error
	// Update 更新平台接口参数
	Update(ctx context.Context, param *model.PlatformAPIParam) error
	// Delete 删除平台接口参数
	Delete(ctx context.Context, id int64) error
	// GetByID 根据ID获取平台接口参数
	GetByID(ctx context.Context, id int64) (*model.PlatformAPIParam, error)
	// List 获取平台接口参数列表
	List(ctx context.Context, apiID int64, page, pageSize int) ([]*model.PlatformAPIParam, int64, error)
}
