package recharge

import (
	"context"
	"recharge-go/internal/model"
)

// Platform 充值平台接口
type Platform interface {
	// GetPlatformName 获取平台名称
	GetPlatformName() string

	// SubmitOrder 提交充值订单
	SubmitOrder(ctx context.Context, order *model.Order, api *model.PlatformAPI) error

	// HandleCallback 处理平台回调
	HandleCallback(ctx context.Context, data []byte) error
}

// PlatformConfig 平台配置
type PlatformConfig struct {
	AppKey    string
	AppSecret string
	ApiURL    string
	NotifyURL string
}

// BasePlatform 基础平台实现
type BasePlatform struct {
	platformName string
}

// NewBasePlatform 创建基础平台
func NewBasePlatform(platformName string) *BasePlatform {
	return &BasePlatform{
		platformName: platformName,
	}
}

// GetPlatformName 获取平台名称
func (p *BasePlatform) GetPlatformName() string {
	return p.platformName
}
