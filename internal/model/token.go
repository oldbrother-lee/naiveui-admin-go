package model

import "time"

// PlatformToken 平台 token 模型
// 用于存储第三方平台 token 及其生成时间
// 只保留一条记录即可

type PlatformToken struct {
	ID        int64     `gorm:"primaryKey;autoIncrement" json:"id"`
	Token     string    `gorm:"type:varchar(255);not null" json:"token"`                 // token 字符串
	CreatedAt time.Time `gorm:"type:datetime;not null;autoCreateTime" json:"created_at"` // token 获取时间
}

func (PlatformToken) TableName() string {
	return "platform_token"
}
