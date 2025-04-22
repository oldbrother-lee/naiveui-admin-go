package repository

import (
	"recharge-go/internal/model"

	"gorm.io/gorm"
)

type PlatformRepository struct {
	db *gorm.DB
}

func NewPlatformRepository(db *gorm.DB) *PlatformRepository {
	return &PlatformRepository{db: db}
}

// List 获取平台列表
func (r *PlatformRepository) List(req *model.PlatformListRequest) (*model.PlatformListResponse, error) {
	var total int64
	var items []model.Platform

	query := r.db.Model(&model.Platform{})

	if req.Name != "" {
		query = query.Where("name LIKE ?", "%"+req.Name+"%")
	}
	if req.Code != "" {
		query = query.Where("code = ?", req.Code)
	}
	if req.Status != nil {
		query = query.Where("status = ?", *req.Status)
	}

	if err := query.Count(&total).Error; err != nil {
		return nil, err
	}

	offset := (req.Page - 1) * req.PageSize
	if err := query.Offset(offset).Limit(req.PageSize).Find(&items).Error; err != nil {
		return nil, err
	}

	return &model.PlatformListResponse{
		Total: total,
		Items: items,
	}, nil
}

// GetByID 根据ID获取平台
func (r *PlatformRepository) GetByID(id int64) (*model.Platform, error) {
	var platform model.Platform
	if err := r.db.First(&platform, id).Error; err != nil {
		return nil, err
	}
	return &platform, nil
}

// Create 创建平台
func (r *PlatformRepository) Create(platform *model.Platform) error {
	return r.db.Create(platform).Error
}

// Update 更新平台
func (r *PlatformRepository) Update(platform *model.Platform) error {
	return r.db.Save(platform).Error
}

// Delete 删除平台
func (r *PlatformRepository) Delete(id int64) error {
	return r.db.Delete(&model.Platform{}, id).Error
}

// ListAccounts 获取平台账号列表
func (r *PlatformRepository) ListAccounts(req *model.PlatformAccountListRequest) (*model.PlatformAccountListResponse, error) {
	var total int64
	var items []model.PlatformAccount

	query := r.db.Model(&model.PlatformAccount{}).Preload("Platform")

	if req.PlatformID != nil {
		query = query.Where("platform_id = ?", *req.PlatformID)
	}
	if req.Status != nil {
		query = query.Where("status = ?", *req.Status)
	}

	if err := query.Count(&total).Error; err != nil {
		return nil, err
	}

	offset := (req.Page - 1) * req.PageSize
	if err := query.Offset(offset).Limit(req.PageSize).Find(&items).Error; err != nil {
		return nil, err
	}

	return &model.PlatformAccountListResponse{
		Total: total,
		Items: items,
	}, nil
}

// GetAccountByID 根据ID获取平台账号
func (r *PlatformRepository) GetAccountByID(id int64) (*model.PlatformAccount, error) {
	var account model.PlatformAccount
	if err := r.db.Preload("Platform").First(&account, id).Error; err != nil {
		return nil, err
	}
	return &account, nil
}

// CreateAccount 创建平台账号
func (r *PlatformRepository) CreateAccount(account *model.PlatformAccount) error {
	return r.db.Create(account).Error
}

// UpdateAccount 更新平台账号
func (r *PlatformRepository) UpdateAccount(account *model.PlatformAccount) error {
	return r.db.Save(account).Error
}

// DeleteAccount 删除平台账号
func (r *PlatformRepository) DeleteAccount(id int64) error {
	return r.db.Delete(&model.PlatformAccount{}, id).Error
}

// CreatePlatform 创建平台
func (r *PlatformRepository) CreatePlatform(platform *model.Platform) error {
	return r.db.Create(platform).Error
}

// UpdatePlatform 更新平台
func (r *PlatformRepository) UpdatePlatform(platform *model.Platform) error {
	return r.db.Save(platform).Error
}

// GetPlatformByID 根据ID获取平台
func (r *PlatformRepository) GetPlatformByID(id int64) (*model.Platform, error) {
	var platform model.Platform
	err := r.db.First(&platform, id).Error
	if err != nil {
		return nil, err
	}
	return &platform, nil
}

// ListPlatforms 获取平台列表
func (r *PlatformRepository) ListPlatforms(req *model.PlatformListRequest) ([]model.Platform, int64, error) {
	var platforms []model.Platform
	var total int64

	query := r.db.Model(&model.Platform{})

	if req.Name != "" {
		query = query.Where("name LIKE ?", "%"+req.Name+"%")
	}
	if req.Code != "" {
		query = query.Where("code LIKE ?", "%"+req.Code+"%")
	}
	if req.Status != nil {
		query = query.Where("status = ?", *req.Status)
	}

	err := query.Count(&total).Error
	if err != nil {
		return nil, 0, err
	}

	offset := (req.Page - 1) * req.PageSize
	err = query.Offset(offset).Limit(req.PageSize).Find(&platforms).Error
	if err != nil {
		return nil, 0, err
	}

	return platforms, total, nil
}

// CreatePlatformAccount 创建平台账号
func (r *PlatformRepository) CreatePlatformAccount(account *model.PlatformAccount) error {
	return r.db.Create(account).Error
}

// UpdatePlatformAccount 更新平台账号
func (r *PlatformRepository) UpdatePlatformAccount(account *model.PlatformAccount) error {
	return r.db.Save(account).Error
}

// GetPlatformAccountByID 根据ID获取平台账号
func (r *PlatformRepository) GetPlatformAccountByID(id int64) (*model.PlatformAccount, error) {
	var account model.PlatformAccount
	err := r.db.Preload("Platform").First(&account, id).Error
	if err != nil {
		return nil, err
	}
	return &account, nil
}

// ListPlatformAccounts 获取平台账号列表
func (r *PlatformRepository) ListPlatformAccounts(req *model.PlatformAccountListRequest) (*model.PlatformAccountListResponse, error) {
	var accounts []model.PlatformAccount
	var total int64

	query := r.db.Model(&model.PlatformAccount{}).Preload("Platform")
	if req.PlatformID != nil {
		query = query.Where("platform_id = ?", *req.PlatformID)
	}
	if req.Status != nil {
		query = query.Where("status = ?", *req.Status)
	}

	err := query.Count(&total).Error
	if err != nil {
		return nil, err
	}

	offset := (req.Page - 1) * req.PageSize
	err = query.Offset(offset).Limit(req.PageSize).Find(&accounts).Error
	if err != nil {
		return nil, err
	}

	return &model.PlatformAccountListResponse{
		Total: total,
		Items: accounts,
	}, nil
}
