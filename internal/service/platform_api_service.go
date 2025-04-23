package service

import (
	"context"
	"errors"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
)

// PlatformAPIService 平台接口服务接口
type PlatformAPIService interface {
	// CreateAPI 创建平台接口
	CreateAPI(ctx context.Context, api *model.PlatformAPI) error
	// UpdateAPI 更新平台接口
	UpdateAPI(ctx context.Context, api *model.PlatformAPI) error
	// DeleteAPI 删除平台接口
	DeleteAPI(ctx context.Context, id int64) error
	// GetAPI 获取平台接口详情
	GetAPI(ctx context.Context, id int64) (*model.PlatformAPI, error)
	// ListAPIs 获取平台接口列表
	ListAPIs(ctx context.Context, page, pageSize int) ([]*model.PlatformAPI, int64, error)
}

// platformAPIService 平台接口服务实现
type platformAPIService struct {
	repo repository.PlatformAPIRepository
}

// NewPlatformAPIService 创建平台接口服务实例
func NewPlatformAPIService(repo repository.PlatformAPIRepository) PlatformAPIService {
	return &platformAPIService{repo: repo}
}

func (s *platformAPIService) CreateAPI(ctx context.Context, api *model.PlatformAPI) error {
	// 参数验证
	if api.Name == "" {
		return errors.New("平台名称不能为空")
	}
	if api.URL == "" {
		return errors.New("接口地址不能为空")
	}
	if api.Method == "" {
		return errors.New("请求方法不能为空")
	}
	if api.PlatformID == 0 {
		return errors.New("平台ID不能为空")
	}

	// 设置默认值
	api.Status = 1 // 默认启用
	api.IsDeleted = 0
	api.Timeout = 30
	api.RetryTimes = 3
	return s.repo.Create(ctx, api)
}

func (s *platformAPIService) UpdateAPI(ctx context.Context, api *model.PlatformAPI) error {
	// 检查API是否存在
	existing, err := s.repo.GetByID(ctx, api.ID)
	if err != nil {
		return err
	}
	if existing == nil {
		return errors.New("接口不存在")
	}

	// 参数验证
	if api.Name == "" {
		return errors.New("平台名称不能为空")
	}
	if api.URL == "" {
		return errors.New("接口地址不能为空")
	}
	if api.Method == "" {
		return errors.New("请求方法不能为空")
	}
	if api.PlatformID == 0 {
		return errors.New("平台ID不能为空")
	}

	return s.repo.Update(ctx, api)
}

func (s *platformAPIService) DeleteAPI(ctx context.Context, id int64) error {
	// 检查API是否存在
	existing, err := s.repo.GetByID(ctx, id)
	if err != nil {
		return err
	}
	if existing == nil {
		return errors.New("接口不存在")
	}

	return s.repo.Delete(ctx, id)
}

func (s *platformAPIService) GetAPI(ctx context.Context, id int64) (*model.PlatformAPI, error) {
	return s.repo.GetByID(ctx, id)
}

func (s *platformAPIService) ListAPIs(ctx context.Context, page, pageSize int) ([]*model.PlatformAPI, int64, error) {
	// 参数验证
	if page < 1 {
		page = 1
	}
	if pageSize < 1 {
		pageSize = 10
	}
	if pageSize > 100 {
		pageSize = 100
	}

	return s.repo.List(ctx, page, pageSize)
}
