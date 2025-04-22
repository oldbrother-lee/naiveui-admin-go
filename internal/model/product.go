package model

import (
	"time"
)

// ProductCategory 商品分类
type ProductCategory struct {
	ID        int64     `json:"id" gorm:"primaryKey"`
	Name      string    `json:"name" gorm:"size:100;not null"`
	Sort      int       `json:"sort" gorm:"default:0"`
	Status    int       `json:"status" gorm:"default:1"`
	CreatedAt time.Time `json:"created_at" gorm:"autoCreateTime"`
	UpdatedAt time.Time `json:"updated_at" gorm:"autoUpdateTime"`
}

// Product 商品
type Product struct {
	ID              int64            `json:"id" gorm:"primaryKey"`
	Name            string           `json:"name" gorm:"size:100;not null"`
	Description     string           `json:"description" gorm:"size:500"`
	Price           float64          `json:"price" gorm:"type:decimal(10,2);not null"`
	Type            int64            `json:"type" gorm:"column:type"`
	ISP             string           `json:"isp" gorm:"size:50"`
	Status          int              `json:"status" gorm:"default:1"`
	Sort            int              `json:"sort" gorm:"default:0"`
	APIEEnabled     bool             `json:"api_enabled" gorm:"default:false"`
	Remark          string           `json:"remark" gorm:"size:500"`
	CategoryID      int64            `json:"category_id"`
	OperatorTag     string           `json:"operator_tag" gorm:"size:50"`
	MaxPrice        float64          `json:"max_price" gorm:"type:decimal(10,2)"`
	VoucherPrice    string           `json:"voucher_price" gorm:"type:varchar(20)"`
	VoucherName     string           `json:"voucher_name" gorm:"size:100"`
	ShowStyle       int              `json:"show_style" gorm:"default:1"`
	APIFailStyle    int              `json:"api_fail_style" gorm:"default:1"`
	AllowProvinces  string           `json:"allow_provinces" gorm:"size:500"`
	AllowCities     string           `json:"allow_cities" gorm:"size:500"`
	ForbidProvinces string           `json:"forbid_provinces" gorm:"size:500"`
	ForbidCities    string           `json:"forbid_cities" gorm:"size:500"`
	APIDelay        string           `json:"api_delay" gorm:"type:varchar(20)"`
	GradeIDs        string           `json:"grade_ids" gorm:"size:500"`
	APIID           int64            `json:"api_id"`
	APIParamID      int64            `json:"api_param_id"`
	IsDecode        bool             `json:"is_decode" gorm:"default:false"`
	CreatedAt       time.Time        `json:"created_at" gorm:"autoCreateTime"`
	UpdatedAt       time.Time        `json:"updated_at" gorm:"autoUpdateTime"`
	ProductType     *ProductType     `json:"product_type,omitempty" gorm:"foreignKey:Type;references:ID"`
	Category        *ProductCategory `json:"category,omitempty" gorm:"foreignKey:CategoryID;references:ID"`
}

// TableName 指定表名
func (Product) TableName() string {
	return "products"
}

// ProductSpec 商品规格
type ProductSpec struct {
	ID        int64     `json:"id" gorm:"primaryKey"`
	ProductID int64     `json:"product_id" gorm:"not null"`
	Name      string    `json:"name" gorm:"size:100;not null"`
	Value     string    `json:"value" gorm:"size:100"`
	Price     float64   `json:"price" gorm:"type:decimal(10,2);default:0"`
	Stock     int       `json:"stock" gorm:"default:0"`
	Sort      int       `json:"sort" gorm:"default:0"`
	CreatedAt time.Time `json:"created_at" gorm:"autoCreateTime"`
	UpdatedAt time.Time `json:"updated_at" gorm:"autoUpdateTime"`
}

// MemberGrade 会员等级
type MemberGrade struct {
	ID        int64     `json:"id" gorm:"primaryKey"`
	Name      string    `json:"name" gorm:"size:100;not null"`
	GradeType int       `json:"grade_type" gorm:"default:1"`
	Sort      int       `json:"sort" gorm:"default:0"`
	CreatedAt time.Time `json:"created_at" gorm:"autoCreateTime"`
	UpdatedAt time.Time `json:"updated_at" gorm:"autoUpdateTime"`
}

// ProductGradePrice 商品会员价格
type ProductGradePrice struct {
	ID        int64     `json:"id" gorm:"primaryKey"`
	ProductID int64     `json:"product_id" gorm:"not null"`
	GradeID   int64     `json:"grade_id" gorm:"not null"`
	Price     float64   `json:"price" gorm:"type:decimal(10,2);not null"`
	CreatedAt time.Time `json:"created_at" gorm:"autoCreateTime"`
	UpdatedAt time.Time `json:"updated_at" gorm:"autoUpdateTime"`
}
