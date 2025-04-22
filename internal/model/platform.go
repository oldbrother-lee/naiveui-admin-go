package model

import "time"

// Platform 平台信息
type Platform struct {
	ID          int64             `json:"id" gorm:"primaryKey"`
	Name        string            `json:"name" gorm:"size:50;not null"`
	Code        string            `json:"code" gorm:"size:20;not null;uniqueIndex"`
	ApiURL      string            `json:"api_url" gorm:"size:255;not null"`
	Description string            `json:"description" gorm:"size:255"`
	Status      int               `json:"status" gorm:"default:1"` // 1:启用 0:禁用
	CreatedAt   time.Time         `json:"created_at"`
	UpdatedAt   time.Time         `json:"updated_at"`
	DeletedAt   *time.Time        `json:"deleted_at" gorm:"index"`
	Accounts    []PlatformAccount `gorm:"foreignKey:PlatformID" json:"accounts,omitempty"`
	APIs        []PlatformAPI     `gorm:"foreignKey:PlatformID" json:"apis,omitempty"`
}

// PlatformAccount 平台账号信息
type PlatformAccount struct {
	ID           int64      `json:"id" gorm:"primaryKey"`
	PlatformID   int64      `json:"platform_id" gorm:"not null;index"`
	AppKey       string     `json:"app_key" gorm:"size:64;not null"`
	AppSecret    string     `json:"app_secret" gorm:"size:64;not null"`
	AccountName  string     `json:"account_name" gorm:"size:50;not null"`
	DailyLimit   float64    `json:"daily_limit" gorm:"type:decimal(10,2);default:0.00"`
	MonthlyLimit float64    `json:"monthly_limit" gorm:"type:decimal(10,2);default:0.00"`
	Balance      float64    `json:"balance" gorm:"type:decimal(10,2);default:0.00"`
	Priority     int        `json:"priority" gorm:"default:0"`
	Status       int        `json:"status" gorm:"default:1"` // 1:启用 0:禁用
	CreatedAt    time.Time  `json:"created_at"`
	UpdatedAt    time.Time  `json:"updated_at"`
	DeletedAt    *time.Time `json:"deleted_at" gorm:"index"`
	Platform     *Platform  `json:"platform,omitempty" gorm:"foreignKey:PlatformID"`
}

// PlatformAPI 平台接口配置
type PlatformAPI struct {
	ID         uint      `gorm:"primaryKey" json:"id"`
	PlatformID uint      `gorm:"not null;index" json:"platform_id"`
	ApiName    string    `gorm:"size:50;not null" json:"api_name"`
	ApiCode    string    `gorm:"size:50;not null" json:"api_code"`
	ApiPath    string    `gorm:"size:255;not null" json:"api_path"`
	Method     string    `gorm:"size:10;default:POST" json:"method"`
	Timeout    int       `gorm:"default:30" json:"timeout"`
	RetryTimes int       `gorm:"default:3" json:"retry_times"`
	Status     bool      `gorm:"default:true" json:"status"`
	CreatedAt  time.Time `json:"created_at"`
	UpdatedAt  time.Time `json:"updated_at"`
	Platform   Platform  `gorm:"foreignKey:PlatformID" json:"-"`
}

// PlatformListRequest 平台列表请求
type PlatformListRequest struct {
	Page     int    `form:"page" binding:"required,min=1"`
	PageSize int    `form:"page_size" binding:"required,min=1,max=100"`
	Name     string `form:"name"`
	Code     string `form:"code"`
	Status   *int   `form:"status"`
}

// PlatformListResponse 平台列表响应
type PlatformListResponse struct {
	Total int64      `json:"total"`
	Items []Platform `json:"items"`
}

// PlatformAccountListRequest 平台账号列表请求
type PlatformAccountListRequest struct {
	Page       int    `form:"page" binding:"required,min=1"`
	PageSize   int    `form:"page_size" binding:"required,min=1,max=100"`
	PlatformID *int64 `form:"platform_id"`
	Status     *int   `form:"status"`
}

// PlatformAccountListResponse 平台账号列表响应
type PlatformAccountListResponse struct {
	Total int64             `json:"total"`
	Items []PlatformAccount `json:"items"`
}

// PlatformCreateRequest 创建平台请求
type PlatformCreateRequest struct {
	Name        string `json:"name" binding:"required,max=50"`
	Code        string `json:"code" binding:"required,max=20"`
	ApiURL      string `json:"api_url" binding:"required,max=255"`
	Description string `json:"description" binding:"max=255"`
}

// PlatformUpdateRequest 更新平台请求
type PlatformUpdateRequest struct {
	Name        string `json:"name" binding:"required,max=50"`
	ApiURL      string `json:"api_url" binding:"required,max=255"`
	Description string `json:"description" binding:"max=255"`
	Status      *int   `json:"status" binding:"omitempty,oneof=0 1"`
}

// PlatformAccountCreateRequest 创建平台账号请求
type PlatformAccountCreateRequest struct {
	PlatformID   int64   `json:"platform_id" binding:"required"`
	AppKey       string  `json:"app_key" binding:"required,max=64"`
	AppSecret    string  `json:"app_secret" binding:"required,max=64"`
	AccountName  string  `json:"account_name" binding:"required,max=50"`
	DailyLimit   float64 `json:"daily_limit" binding:"min=0"`
	MonthlyLimit float64 `json:"monthly_limit" binding:"min=0"`
	Priority     int     `json:"priority" binding:"min=0"`
}

// PlatformAccountUpdateRequest 更新平台账号请求
type PlatformAccountUpdateRequest struct {
	AppSecret    string  `json:"app_secret" binding:"max=64"`
	AccountName  string  `json:"account_name" binding:"max=50"`
	DailyLimit   float64 `json:"daily_limit" binding:"min=0"`
	MonthlyLimit float64 `json:"monthly_limit" binding:"min=0"`
	Balance      float64 `json:"balance" binding:"min=0"`
	Priority     int     `json:"priority" binding:"min=0"`
	Status       *int    `json:"status" binding:"omitempty,oneof=0 1"`
}
