package repository

import (
	"recharge-go/internal/model"
	"recharge-go/pkg/database"

	"gorm.io/gorm"
)

type PlatformTokenRepository struct {
	db *gorm.DB
}

func NewPlatformTokenRepository() *PlatformTokenRepository {
	return &PlatformTokenRepository{db: database.DB}
}

// Get 获取当前平台 token（只保留一条记录）
func (r *PlatformTokenRepository) Get() (*model.PlatformToken, error) {
	var token model.PlatformToken
	err := r.db.First(&token).Error
	if err != nil {
		return nil, err
	}
	return &token, nil
}

// Save 保存/更新平台 token（先清空再插入）
func (r *PlatformTokenRepository) Save(token string) error {
	r.db.Exec("DELETE FROM platform_token")
	return r.db.Create(&model.PlatformToken{
		Token: token,
	}).Error
}

// Delete 删除平台 token
func (r *PlatformTokenRepository) Delete() error {
	return r.db.Exec("DELETE FROM platform_token").Error
}
