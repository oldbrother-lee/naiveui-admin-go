package model

import (
	"time"
)

// TaskConfig 任务配置
type TaskConfig struct {
	ID               int64     `json:"id" gorm:"primaryKey"`
	ChannelID        int64     `json:"channel_id" gorm:"not null;comment:渠道ID"`
	ChannelName      string    `json:"channel_name" gorm:"not null;comment:渠道名称"`
	ProductID        int64     `json:"product_id" gorm:"not null;comment:产品ID"`
	ProductName      string    `json:"product_name" gorm:"not null;comment:产品名称"`
	FaceValues       string    `json:"face_values" gorm:"type:text;not null;comment:面值列表"`
	MinSettleAmounts string    `json:"min_settle_amounts" gorm:"type:text;not null;comment:最低结算价列表"`
	Status           int       `json:"status" gorm:"not null;default:1;comment:状态 1:启用 2:禁用"`
	CreatedAt        time.Time `json:"created_at" gorm:"autoCreateTime"`
	UpdatedAt        time.Time `json:"updated_at" gorm:"autoUpdateTime"`
}

// TableName 表名
func (TaskConfig) TableName() string {
	return "task_configs"
}

type UpdateTaskConfigRequest struct {
	ID               *int64  `json:"id"`
	FaceValues       *string `json:"face_values"`
	MinSettleAmounts *string `json:"min_settle_amounts"`
	Status           *int    `json:"status"`
}
