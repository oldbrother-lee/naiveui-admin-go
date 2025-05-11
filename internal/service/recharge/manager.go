package recharge

import (
	"context"
	"fmt"
	"recharge-go/internal/model"
)

// Manager 充值服务管理器
type Manager struct {
	platforms map[string]Platform
	accounts  map[int64]*model.PlatformAccount
}

// NewManager 创建充值服务管理器
func NewManager() *Manager {
	return &Manager{
		platforms: make(map[string]Platform),
		accounts:  make(map[int64]*model.PlatformAccount),
	}
}

// RegisterPlatform 注册平台
func (m *Manager) RegisterPlatform(name string, platform Platform) {
	m.platforms[name] = platform
}

// RegisterAccount 注册平台账号
func (m *Manager) RegisterAccount(account *model.PlatformAccount) {
	m.accounts[account.ID] = account
}

// GetPlatform 获取平台实例
func (m *Manager) GetPlatform(platformID int64) (Platform, error) {
	// 根据平台ID获取平台名称
	platformName := getPlatformNameByID(platformID)
	platform, ok := m.platforms[platformName]
	if !ok {
		return nil, fmt.Errorf("platform with ID %d not found", platformID)
	}
	return platform, nil
}

// getPlatformNameByID 根据平台ID获取平台名称
func getPlatformNameByID(platformID int64) string {
	// 这里可以根据实际需求实现平台ID到平台名称的映射
	// 例如：1 -> "kekebang", 2 -> "other_platform" 等
	switch platformID {
	case 2:
		return "kekebang"
	default:
		return "unknown"
	}
}

// GetAccount 获取平台账号
func (m *Manager) GetAccount(id int64) (*model.PlatformAccount, error) {
	account, ok := m.accounts[id]
	if !ok {
		return nil, fmt.Errorf("platform account %d not found", id)
	}
	return account, nil
}

// SubmitOrder 提交充值订单
func (m *Manager) SubmitOrder(ctx context.Context, order *model.Order, api *model.PlatformAPI) error {
	platform, err := m.GetPlatform(api.PlatformID)
	if err != nil {
		return fmt.Errorf("get platform failed: %v", err)
	}
	return platform.SubmitOrder(ctx, order, api)
}

// HandleCallback 处理平台回调
func (m *Manager) HandleCallback(ctx context.Context, platformID int64, data []byte) error {
	platform, err := m.GetPlatform(platformID)
	if err != nil {
		return err
	}
	return platform.HandleCallback(ctx, data)
}
