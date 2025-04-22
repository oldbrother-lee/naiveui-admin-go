package service

import (
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
)

type PlatformService struct {
	repo *repository.PlatformRepository
}

func NewPlatformService(repo *repository.PlatformRepository) *PlatformService {
	return &PlatformService{repo: repo}
}

// ListPlatforms 获取平台列表
func (s *PlatformService) ListPlatforms(req *model.PlatformListRequest) (*model.PlatformListResponse, error) {
	platforms, total, err := s.repo.ListPlatforms(req)
	if err != nil {
		return nil, err
	}
	return &model.PlatformListResponse{
		Total: total,
		Items: platforms,
	}, nil
}

// CreatePlatform 创建平台
func (s *PlatformService) CreatePlatform(req *model.PlatformCreateRequest) (*model.Platform, error) {
	platform := &model.Platform{
		Name:        req.Name,
		Code:        req.Code,
		ApiURL:      req.ApiURL,
		Description: req.Description,
		Status:      1, // 默认启用
	}
	err := s.repo.CreatePlatform(platform)
	if err != nil {
		return nil, err
	}
	return platform, nil
}

// UpdatePlatform 更新平台
func (s *PlatformService) UpdatePlatform(id int64, req *model.PlatformUpdateRequest) error {
	platform, err := s.repo.GetPlatformByID(id)
	if err != nil {
		return err
	}

	platform.Name = req.Name
	platform.ApiURL = req.ApiURL
	platform.Description = req.Description
	if req.Status != nil {
		platform.Status = *req.Status
	}

	return s.repo.UpdatePlatform(platform)
}

// DeletePlatform 删除平台
func (s *PlatformService) DeletePlatform(id int64) error {
	return s.repo.Delete(id)
}

// GetPlatform 获取平台详情
func (s *PlatformService) GetPlatform(id int64) (*model.Platform, error) {
	return s.repo.GetPlatformByID(id)
}

// ListPlatformAccounts 获取平台账号列表
func (s *PlatformService) ListPlatformAccounts(req *model.PlatformAccountListRequest) (*model.PlatformAccountListResponse, error) {
	return s.repo.ListPlatformAccounts(req)
}

// CreatePlatformAccount 创建平台账号
func (s *PlatformService) CreatePlatformAccount(req *model.PlatformAccountCreateRequest) (*model.PlatformAccount, error) {
	account := &model.PlatformAccount{
		PlatformID:   req.PlatformID,
		AppKey:       req.AppKey,
		AppSecret:    req.AppSecret,
		AccountName:  req.AccountName,
		DailyLimit:   req.DailyLimit,
		MonthlyLimit: req.MonthlyLimit,
		Priority:     req.Priority,
		Status:       1, // 默认启用
	}
	err := s.repo.CreatePlatformAccount(account)
	if err != nil {
		return nil, err
	}
	return account, nil
}

// UpdatePlatformAccount 更新平台账号
func (s *PlatformService) UpdatePlatformAccount(id int64, req *model.PlatformAccountUpdateRequest) error {
	account, err := s.repo.GetPlatformAccountByID(id)
	if err != nil {
		return err
	}

	if req.AppSecret != "" {
		account.AppSecret = req.AppSecret
	}
	if req.AccountName != "" {
		account.AccountName = req.AccountName
	}
	if req.DailyLimit > 0 {
		account.DailyLimit = req.DailyLimit
	}
	if req.MonthlyLimit > 0 {
		account.MonthlyLimit = req.MonthlyLimit
	}
	if req.Balance > 0 {
		account.Balance = req.Balance
	}
	if req.Priority > 0 {
		account.Priority = req.Priority
	}
	if req.Status != nil {
		account.Status = *req.Status
	}

	return s.repo.UpdatePlatformAccount(account)
}

// DeletePlatformAccount 删除平台账号
func (s *PlatformService) DeletePlatformAccount(id int64) error {
	return s.repo.DeleteAccount(id)
}

// GetPlatformAccount 获取平台账号详情
func (s *PlatformService) GetPlatformAccount(id int64) (*model.PlatformAccount, error) {
	return s.repo.GetPlatformAccountByID(id)
}
