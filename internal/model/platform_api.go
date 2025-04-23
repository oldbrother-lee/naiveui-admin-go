package model

import (
	"time"
)

// PlatformAPI 平台接口配置
type PlatformAPI struct {
	ID          int64     `json:"id" gorm:"primaryKey"`
	PlatformID  int64     `json:"platform_id" gorm:"not null;index"`
	Name        string    `json:"name" gorm:"size:50;not null;comment:平台名称"`
	Code        string    `json:"code" gorm:"size:50;not null;comment:接口代码"`
	URL         string    `json:"url" gorm:"size:255;not null;comment:接口地址"`
	Method      string    `json:"method" gorm:"size:10;default:POST;comment:请求方法"`
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

// PlatformAPIParam 接口参数配置
type PlatformAPIParam struct {
	ID              int64     `json:"id" gorm:"primaryKey"`
	APIID           int64     `json:"api_id" gorm:"not null;index" validate:"required"`
	Name            string    `json:"name" gorm:"size:50;not null;comment:参数名称" validate:"required,max=50"`
	Code            string    `json:"code" gorm:"size:50;not null;comment:参数代码" validate:"required,max=50"`
	Value           string    `json:"value" gorm:"size:255;comment:参数值"`
	Description     string    `json:"description" gorm:"size:255;comment:参数描述"`
	Cost            float64   `json:"cost" gorm:"type:decimal(10,4);default:0.0000;comment:接口成本" validate:"min=0"`
	MinCost         float64   `json:"min_cost" gorm:"type:decimal(10,4);default:0.0000;comment:最低成本" validate:"min=0"`
	MaxCost         float64   `json:"max_cost" gorm:"type:decimal(10,4);default:0.0000;comment:最高成本" validate:"min=0"`
	AllowProvinces  string    `json:"allow_provinces" gorm:"type:text;comment:允许的省份"`
	AllowCities     string    `json:"allow_cities" gorm:"type:text;comment:允许的城市"`
	ForbidProvinces string    `json:"forbid_provinces" gorm:"type:text;comment:禁止的省份"`
	ForbidCities    string    `json:"forbid_cities" gorm:"type:text;comment:禁止的城市"`
	Sort            int       `json:"sort" gorm:"not null;default:0;comment:排序"`
	Status          int       `json:"status" gorm:"not null;default:1;comment:状态：1-启用，0-禁用" validate:"oneof=0 1"`
	CreatedAt       time.Time `json:"created_at" gorm:"not null;default:CURRENT_TIMESTAMP;type:datetime"`
	UpdatedAt       time.Time `json:"updated_at" gorm:"not null;default:CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;type:datetime"`
}

// ProductAPIRelation 商品接口关联
type ProductAPIRelation struct {
	ID        int64     `json:"id" gorm:"primaryKey"`
	ProductID int64     `json:"product_id" gorm:"not null;comment:商品ID"`
	APIID     int64     `json:"api_id" gorm:"not null;comment:接口ID"`
	ParamID   int64     `json:"param_id" gorm:"not null;comment:参数ID"`
	Sort      int       `json:"sort" gorm:"not null;default:0;comment:排序"`
	Status    int       `json:"status" gorm:"not null;default:1;comment:状态：1-启用，0-禁用"`
	CreatedAt time.Time `json:"created_at" gorm:"not null;default:CURRENT_TIMESTAMP;type:datetime"`
	UpdatedAt time.Time `json:"updated_at" gorm:"not null;default:CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;type:datetime"`
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
