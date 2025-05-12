package recharge

import (
	"context"
	"fmt"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/pkg/logger"
	"sync"

	"gorm.io/gorm"
)

// Manager 平台管理器
type Manager struct {
	platformRepo    repository.PlatformRepository
	platformAPIRepo repository.PlatformAPIRepository
	platforms       map[string]Platform
	mu              sync.RWMutex
}

// NewManager 创建平台管理器
func NewManager(db *gorm.DB) *Manager {
	return &Manager{
		platformRepo:    repository.NewPlatformRepository(db),
		platformAPIRepo: repository.NewPlatformAPIRepository(db),
		platforms:       make(map[string]Platform),
	}
}

// GetPlatform 获取平台实例
func (m *Manager) GetPlatform(platformCode string) (Platform, error) {
	// 直接从数据库加载平台API配置
	api, err := m.platformAPIRepo.GetByCode(context.Background(), platformCode)
	if err != nil {
		return nil, fmt.Errorf("failed to get platform API: %v", err)
	}

	// 创建平台实例
	platform := m.createPlatform(api)
	if platform == nil {
		return nil, fmt.Errorf("failed to create platform instance for %s", platformCode)
	}

	return platform, nil
}

// createPlatform 创建平台实例
func (m *Manager) createPlatform(api *model.PlatformAPI) Platform {
	switch api.Code {
	case "kekebang":
		return NewKekebangPlatform(api)
	default:
		return nil
	}
}

// LoadPlatforms 从数据库加载所有平台配置
func (m *Manager) LoadPlatforms() error {
	// 获取所有启用的平台
	platforms, _, err := m.platformRepo.ListPlatforms(&model.PlatformListRequest{
		Page:     1,
		PageSize: 100,
		Status:   &[]int{1}[0],
	})
	if err != nil {
		return fmt.Errorf("failed to list platforms: %v", err)
	}

	// 为每个平台创建实例
	for _, platform := range platforms {
		// 获取平台API配置
		api, err := m.platformAPIRepo.GetByCode(context.Background(), platform.Code)
		if err != nil {
			logger.Error("Failed to get platform API for %s: %v", platform.Code, err)
			continue
		}

		// 创建平台实例
		platformInstance := m.createPlatform(api)
		if platformInstance == nil {
			logger.Error("Failed to create platform instance for %s", platform.Code)
			continue
		}

		// 缓存平台实例
		m.mu.Lock()
		m.platforms[platform.Code] = platformInstance
		m.mu.Unlock()
	}

	return nil
}

// SubmitOrder 提交订单到平台
func (m *Manager) SubmitOrder(ctx context.Context, order *model.Order, api *model.PlatformAPI) error {
	// 获取平台实例
	platform, err := m.GetPlatform("kekebang")
	if err != nil {
		return fmt.Errorf("failed to get platform: %v", err)
	}
	fmt.Println("提交订单到平台,获取平台实例", platform)

	// 提交订单
	return platform.SubmitOrder(ctx, order, api)
}

// QueryOrderStatus 查询订单状态
func (m *Manager) QueryOrderStatus(ctx context.Context, order *model.Order) error {
	// 获取平台实例
	platform, err := m.GetPlatform(order.PlatformName)
	if err != nil {
		return fmt.Errorf("failed to get platform: %v", err)
	}

	// 查询订单状态
	status, err := platform.QueryOrderStatus(order)
	if err != nil {
		return err
	}

	// 更新订单状态
	order.Status = model.OrderStatus(status)
	return nil
}

// HandleCallback 处理平台回调
func (m *Manager) HandleCallback(ctx context.Context, platformCode string, data []byte) error {
	// 获取平台实例
	platform, err := m.GetPlatform(platformCode)
	if err != nil {
		return fmt.Errorf("failed to get platform: %v", err)
	}

	// 解析回调数据
	callbackData, err := platform.ParseCallbackData(data)
	if err != nil {
		return fmt.Errorf("failed to parse callback data: %v", err)
	}

	// TODO: 处理回调数据
	_ = callbackData
	return nil
}

// ParseCallbackData 解析回调数据
func (m *Manager) ParseCallbackData(data []byte) (*model.CallbackData, error) {
	// 获取平台实例
	platform, err := m.GetPlatform("kekebang") // 这里需要根据实际情况获取正确的平台
	if err != nil {
		return nil, fmt.Errorf("get platform failed: %v", err)
	}

	// 调用平台的 ParseCallbackData 方法
	return platform.ParseCallbackData(data)
}
