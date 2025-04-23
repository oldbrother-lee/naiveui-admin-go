package model

import (
	"time"
)

// ProductCategory 商品分类
type ProductCategory struct {
	ID        int64     `json:"id" gorm:"primaryKey;type:bigint;not null"`
	Name      string    `json:"name" gorm:"size:100;not null"`
	Sort      int       `json:"sort" gorm:"type:bigint;default:0"`
	Status    int       `json:"status" gorm:"type:bigint;default:1"`
	CreatedAt time.Time `json:"created_at" gorm:"type:datetime;autoCreateTime"`
	UpdatedAt time.Time `json:"updated_at" gorm:"type:datetime;autoUpdateTime"`
}

// Product 商品
type Product struct {
	ID              int64            `json:"id" gorm:"primaryKey;type:bigint"`                              // 主键ID
	Name            string           `json:"name" gorm:"size:100;not null"`                                 // 商品名称
	Description     string           `json:"description" gorm:"size:500"`                                   // 商品描述
	Price           float64          `json:"price" gorm:"type:decimal(10,2);not null"`                      // 商品价格
	Type            int64            `json:"type" gorm:"column:type;type:bigint"`                           // 商品类型ID
	ISP             string           `json:"isp" gorm:"size:50"`                                            // 运营商
	Status          int              `json:"status" gorm:"type:bigint;default:1"`                           // 状态：1-启用，0-禁用
	Sort            int              `json:"sort" gorm:"type:bigint;default:0"`                             // 排序权重
	APIEEnabled     bool             `json:"api_enabled" gorm:"default:false"`                              // API是否启用
	Remark          string           `json:"remark" gorm:"size:500"`                                        // 备注信息
	CategoryID      int64            `json:"category_id" gorm:"type:bigint;not null"`                       // 分类ID
	OperatorTag     string           `json:"operator_tag" gorm:"size:50"`                                   // 运营商标签
	MaxPrice        float64          `json:"max_price" gorm:"type:decimal(10,2)"`                           // 最高价格
	VoucherPrice    string           `json:"voucher_price" gorm:"type:varchar(20)"`                         // 代金券价格
	VoucherName     string           `json:"voucher_name" gorm:"size:100"`                                  // 代金券名称
	ShowStyle       int              `json:"show_style" gorm:"type:bigint;default:1"`                       // 显示样式
	APIFailStyle    int              `json:"api_fail_style" gorm:"type:bigint;default:1"`                   // API失败处理方式
	AllowProvinces  string           `json:"allow_provinces" gorm:"size:500"`                               // 允许的省份
	AllowCities     string           `json:"allow_cities" gorm:"size:500"`                                  // 允许的城市
	ForbidProvinces string           `json:"forbid_provinces" gorm:"size:500"`                              // 禁止的省份
	ForbidCities    string           `json:"forbid_cities" gorm:"size:500"`                                 // 禁止的城市
	APIDelay        string           `json:"api_delay" gorm:"type:varchar(20)"`                             // API延迟时间
	GradeIDs        string           `json:"grade_ids" gorm:"size:500"`                                     // 等级ID列表
	APIID           int64            `json:"api_id" gorm:"type:bigint"`                                     // API接口ID
	APIParamID      int64            `json:"api_param_id" gorm:"type:bigint"`                               // API参数ID
	IsDecode        bool             `json:"is_decode" gorm:"default:false"`                                // 是否需要解码
	CreatedAt       time.Time        `json:"created_at" gorm:"type:datetime;autoCreateTime"`                // 创建时间
	UpdatedAt       time.Time        `json:"updated_at" gorm:"type:datetime;autoUpdateTime"`                // 更新时间
	ProductType     *ProductType     `json:"product_type,omitempty" gorm:"foreignKey:Type;references:ID"`   // 关联的商品类型
	Category        *ProductCategory `json:"category,omitempty" gorm:"foreignKey:CategoryID;references:ID"` // 关联的商品分类
}

// TableName 指定表名
func (Product) TableName() string {
	return "products"
}

// ProductSpec 商品规格
type ProductSpec struct {
	ID        int64     `json:"id" gorm:"primaryKey;type:bigint"`
	ProductID int64     `json:"product_id" gorm:"type:bigint;not null"`
	Name      string    `json:"name" gorm:"size:100;not null"`
	Value     string    `json:"value" gorm:"size:100"`
	Price     float64   `json:"price" gorm:"type:decimal(10,2);default:0"`
	Stock     int       `json:"stock" gorm:"type:bigint;default:0"`
	Sort      int       `json:"sort" gorm:"type:bigint;default:0"`
	CreatedAt time.Time `json:"created_at" gorm:"type:datetime;autoCreateTime"`
	UpdatedAt time.Time `json:"updated_at" gorm:"type:datetime;autoUpdateTime"`
}

// MemberGrade 会员等级
type MemberGrade struct {
	ID        int64     `json:"id" gorm:"primaryKey;type:bigint"`
	Name      string    `json:"name" gorm:"size:100;not null"`
	GradeType int       `json:"grade_type" gorm:"type:bigint;default:1"`
	Sort      int       `json:"sort" gorm:"type:bigint;default:0"`
	CreatedAt time.Time `json:"created_at" gorm:"type:datetime;autoCreateTime"`
	UpdatedAt time.Time `json:"updated_at" gorm:"type:datetime;autoUpdateTime"`
}

// ProductGradePrice 商品会员价格
type ProductGradePrice struct {
	ID        int64     `json:"id" gorm:"primaryKey;type:bigint"`
	ProductID int64     `json:"product_id" gorm:"type:bigint;not null"`
	GradeID   int64     `json:"grade_id" gorm:"type:bigint;not null"`
	Price     float64   `json:"price" gorm:"type:decimal(10,2);not null"`
	CreatedAt time.Time `json:"created_at" gorm:"type:datetime;autoCreateTime"`
	UpdatedAt time.Time `json:"updated_at" gorm:"type:datetime;autoUpdateTime"`
}
