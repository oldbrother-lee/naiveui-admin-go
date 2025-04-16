package repository

import (
	"recharge-go/internal/model"

	"gorm.io/gorm"
)

type UserRepository struct {
	db *gorm.DB
}

func NewUserRepository(db *gorm.DB) *UserRepository {
	return &UserRepository{db: db}
}

func (r *UserRepository) Create(user *model.User) error {
	return r.db.Create(user).Error
}

func (r *UserRepository) GetByID(id int64) (*model.User, error) {
	var user model.User
	err := r.db.First(&user, id).Error
	if err != nil {
		return nil, err
	}
	return &user, nil
}

func (r *UserRepository) GetByUsername(username string) (*model.User, error) {
	var user model.User
	err := r.db.Where("username = ?", username).First(&user).Error
	if err != nil {
		return nil, err
	}
	return &user, nil
}

func (r *UserRepository) Update(user *model.User) error {
	return r.db.Save(user).Error
}

func (r *UserRepository) Delete(id int64) error {
	return r.db.Delete(&model.User{}, id).Error
}

func (r *UserRepository) List(page, pageSize int) ([]model.User, int64, error) {
	var users []model.User
	var total int64

	err := r.db.Model(&model.User{}).Count(&total).Error
	if err != nil {
		return nil, 0, err
	}

	err = r.db.Offset((page - 1) * pageSize).Limit(pageSize).Find(&users).Error
	if err != nil {
		return nil, 0, err
	}

	return users, total, nil
}

// AddUserRole adds a user to a role
func (r *UserRepository) AddUserRole(userID, roleID int64) error {
	return r.db.Create(&model.UserRole{
		UserID: userID,
		RoleID: roleID,
	}).Error
}

// RemoveUserRole removes a user from a role
func (r *UserRepository) RemoveUserRole(userID, roleID int64) error {
	return r.db.Where("user_id = ? AND role_id = ?", userID, roleID).
		Delete(&model.UserRole{}).Error
}

// GetUserRoles gets all roles for a user
func (r *UserRepository) GetUserRoles(userID int64) ([]model.Role, error) {
	var roles []model.Role
	err := r.db.Joins("JOIN user_roles ON user_roles.role_id = roles.id").
		Where("user_roles.user_id = ?", userID).
		Find(&roles).Error
	if err != nil {
		return nil, err
	}
	return roles, nil
}

// GetUserWithRoles gets a user with their roles
func (r *UserRepository) GetUserWithRoles(userID int64) (*model.UserWithRoles, error) {
	user, err := r.GetByID(userID)
	if err != nil {
		return nil, err
	}

	roles, err := r.GetUserRoles(userID)
	if err != nil {
		return nil, err
	}

	return &model.UserWithRoles{
		User:  *user,
		Roles: roles,
	}, nil
}

// RemoveAllUserRoles removes all roles from a user
func (r *UserRepository) RemoveAllUserRoles(userID int64) error {
	return r.db.Where("user_id = ?", userID).Delete(&model.UserRole{}).Error
}
