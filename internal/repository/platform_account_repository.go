package repository

import (
	"recharge-go/internal/model"

	"gorm.io/gorm"
)

// PlatformAccountRepository 平台账号仓储
type PlatformAccountRepository struct {
	db *gorm.DB
}

func NewPlatformAccountRepository(db *gorm.DB) *PlatformAccountRepository {
	return &PlatformAccountRepository{db: db}
}

// 绑定本地用户
func (r *PlatformAccountRepository) BindUser(platformAccountID int64, userID int64) error {
	return r.db.Model(&model.PlatformAccount{}).
		Where("id = ?", platformAccountID).
		Update("bind_user_id", userID).Error
}

// 查询平台账号（带本地用户名）
func (r *PlatformAccountRepository) GetListWithUserName(req *model.PlatformAccountListRequest) (total int64, list []model.PlatformAccount, err error) {
	db := r.db.Model(&model.PlatformAccount{}).Where("deleted_at IS NULL")

	if req.PlatformID != nil {
		db = db.Where("platform_id = ?", *req.PlatformID)
	}
	if req.Status != nil {
		db = db.Where("status = ?", *req.Status)
	}

	// 统计总数
	err = db.Count(&total).Error
	if err != nil {
		return
	}

	// 分页查询并 join 用户表
	err = db.
		Select("platform_accounts.*, u.username as bind_user_name").
		Joins("LEFT JOIN users u ON platform_accounts.bind_user_id = u.id").
		Order("platform_accounts.id DESC").
		Limit(req.PageSize).
		Offset((req.Page - 1) * req.PageSize).
		Find(&list).Error
	return
}

// 查询单个账号
func (r *PlatformAccountRepository) GetByID(id int64) (*model.PlatformAccount, error) {
	var account model.PlatformAccount
	err := r.db.Where("id = ?", id).First(&account).Error
	if err != nil {
		return nil, err
	}
	return &account, nil
}
