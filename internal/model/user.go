package model

import (
	"time"
)

type User struct {
	ID        int64      `json:"id" gorm:"primaryKey"`
	Username  string     `json:"username" gorm:"uniqueIndex;size:50"`
	Password  string     `json:"-" gorm:"size:100"`
	Nickname  *string    `json:"nickname" gorm:"size:50"`
	Email     *string    `json:"email" gorm:"size:100"`
	Phone     *string    `json:"phone" gorm:"size:20"`
	Avatar    *string    `json:"avatar" gorm:"size:255"`
	Status    int        `json:"status" gorm:"default:1"` // 1:正常 0:禁用
	CreatedAt time.Time  `json:"created_at"`
	UpdatedAt time.Time  `json:"updated_at"`
	DeletedAt *time.Time `json:"deleted_at" gorm:"index"`
}

type UserLoginRequest struct {
	Username string `json:"username" binding:"required"`
	Password string `json:"password" binding:"required"`
}

type UserLoginResponse struct {
	Token string `json:"token"`
	User  User   `json:"user"`
}

type UserRegisterRequest struct {
	Username string  `json:"username" binding:"required"`
	Password string  `json:"password" binding:"required"`
	Nickname *string `json:"nickname"`
	Email    *string `json:"email" binding:"omitempty,email"`
	Phone    *string `json:"phone"`
}

type UserUpdateRequest struct {
	Nickname *string `json:"nickname"`
	Email    *string `json:"email" binding:"omitempty,email"`
	Phone    *string `json:"phone"`
	Avatar   *string `json:"avatar"`
}

type UserChangePasswordRequest struct {
	OldPassword string `json:"old_password" binding:"required"`
	NewPassword string `json:"new_password" binding:"required"`
}
