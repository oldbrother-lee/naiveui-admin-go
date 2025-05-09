package model

import (
	"time"
)

// PlatformAPI 平台接口配置
type PlatformAPI struct {
	ID          int64     `json:"id" gorm:"primaryKey"`
	PlatformID  int64     `json:"platform_id" gorm:"not null;index"` // 平台ID
	Name        string    `json:"name" gorm:"size:100;not null;comment:平台名称"`
	Code        string    `json:"code" gorm:"size:50;not null;comment:接口代码;uniqueIndex"`
	URL         string    `json:"url" gorm:"size:255;not null;comment:接口地址"`
	Method      string    `json:"method" gorm:"size:10;not null;comment:请求方法"`
	MerchantID  string    `json:"merchant_id" gorm:"size:255;comment:商户ID"`
	SecretKey   string    `json:"secret_key" gorm:"size:255;comment:密钥"`
	CallbackURL string    `json:"callback_url" gorm:"size:255;comment:回调地址"`
	Timeout     int       `json:"timeout" gorm:"default:30;comment:超时时间(秒)"`
	RetryTimes  int       `json:"retry_times" gorm:"default:3;comment:重试次数"`
	Remark      string    `json:"remark" gorm:"type:text;comment:接口说明"`
	Status      int       `json:"status" gorm:"not null;default:1;comment:状态：1-启用，0-禁用"`
	IsDeleted   int       `json:"is_deleted" gorm:"not null;default:0;comment:是否删除：0-未删除，1-已删除"`
	CreatedAt   time.Time `json:"created_at" gorm:"not null;default:CURRENT_TIMESTAMP;type:datetime"`
	UpdatedAt   time.Time `json:"updated_at" gorm:"not null;default:CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;type:datetime"`
}

// PlatformAPIParam 接口套餐配置
type PlatformAPIParam struct {
	ID              int64     `json:"id" gorm:"primaryKey"`
	APIID           int64     `json:"api_id" gorm:"not null;index" validate:"required"`
	Name            string    `json:"name" gorm:"size:50;not null;comment:参数名称" validate:"required,max=50"`
	ProductID       int64     `json:"product_id" gorm:"not null;comment:产品ID" validate:"required"`
	Description     string    `json:"description" gorm:"size:255;comment:参数描述"`
	Cost            float64   `json:"cost" gorm:"type:decimal(10,4);default:0.0000;comment:产品成本" validate:"min=0"`
	ParValue        float64   `json:"par_value" gorm:"type:decimal(10,4);default:0.0000;comment:套餐值" `
	Price           float64   `json:"price" gorm:"type:decimal(10,4);default:0.0000;comment:价格" `
	AllowProvinces  string    `json:"allow_provinces" gorm:"type:text;comment:允许的省份"`
	AllowCities     string    `json:"allow_cities" gorm:"type:text;comment:允许的城市"`
	ForbidProvinces string    `json:"forbid_provinces" gorm:"type:text;comment:禁止的省份"`
	ForbidCities    string    `json:"forbid_cities" gorm:"type:text;comment:禁止的城市"`
	Sort            int       `json:"sort" gorm:"not null;default:0;comment:排序"`
	Status          int       `json:"status" gorm:"not null;default:1;comment:状态：1-启用，0-禁用" validate:"oneof=0 1"`
	CreatedAt       time.Time `json:"created_at" gorm:"not null;default:CURRENT_TIMESTAMP;type:datetime"`
	UpdatedAt       time.Time `json:"updated_at" gorm:"not null;default:CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;type:datetime"`
}

// APICallLog 接口调用日志
type APICallLog struct {
	ID            int64     `json:"id" gorm:"primaryKey"`
	APIID         int64     `json:"api_id" gorm:"not null;comment:接口ID"`
	RequestURL    string    `json:"request_url" gorm:"size:255;not null;comment:请求URL"`
	RequestMethod string    `json:"request_method" gorm:"size:10;not null;comment:请求方法"`
	RequestParams string    `json:"request_params" gorm:"type:text;comment:请求参数"`
	ResponseData  string    `json:"response_data" gorm:"type:text;comment:响应数据"`
	StatusCode    int       `json:"status_code" gorm:"not null;comment:状态码"`
	ErrorMessage  string    `json:"error_message" gorm:"size:255;comment:错误信息"`
	Duration      int       `json:"duration" gorm:"not null;comment:耗时(毫秒)"`
	CreatedAt     time.Time `json:"created_at" gorm:"not null;default:CURRENT_TIMESTAMP;type:datetime"`
}
