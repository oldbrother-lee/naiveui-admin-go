package model

import "time"

// TaskConfig 取单配置表
// 用于配置自动取单的渠道、运营商、面值、最低结算价等参数
// status: 1-启用 2-禁用
// face_values 和 min_settle_amounts 逗号分隔
type TaskConfig struct {
	ID               int64     `gorm:"primaryKey"`                 // 主键ID
	ChannelID        int       `gorm:"not null;index"`             // 渠道ID
	ProductID        int       `gorm:"not null;index"`             // 运营商ID
	FaceValues       string    `gorm:"type:varchar(255);not null"` // 面值列表，逗号分隔
	MinSettleAmounts string    `gorm:"type:varchar(255);not null"` // 最低结算价列表，逗号分隔
	Status           int       `gorm:"not null;default:1;index"`   // 状态 1-启用 2-禁用
	CreatedAt        time.Time // 创建时间
	UpdatedAt        time.Time // 更新时间
}
