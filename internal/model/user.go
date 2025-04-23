package model

import (
	"time"
)

// User 用户
type User struct {
	ID        int64     `json:"id" gorm:"primaryKey;type:bigint;not null"`
	Username  string    `json:"username" gorm:"size:50;not null;unique"`
	Password  string    `json:"-" gorm:"size:100;not null"`
	Nickname  string    `json:"nickname" gorm:"size:50"`
	Phone     string    `json:"phone" gorm:"size:20"`
	Email     string    `json:"email" gorm:"size:100"`
	Avatar    string    `json:"avatar" gorm:"size:255"`
	Status    int       `json:"status" gorm:"type:bigint;default:1"`
	LastLogin time.Time `json:"last_login" gorm:"type:datetime"`
	CreatedAt time.Time `json:"created_at" gorm:"type:datetime;autoCreateTime"`
	UpdatedAt time.Time `json:"updated_at" gorm:"type:datetime;autoUpdateTime"`
}

// TableName 指定表名
func (User) TableName() string {
	return "users"
}

type UserLoginRequest struct {
	Username string `json:"username" binding:"required"`
	Password string `json:"password" binding:"required"`
}

type UserLoginResponse struct {
	Token        string   `json:"token"`
	RefreshToken string   `json:"refreshToken"`
	UserInfo     UserInfo `json:"userInfo"`
}

type UserInfo struct {
	UserId   string   `json:"userId"`
	Username string   `json:"userName"`
	Roles    []string `json:"roles"`
	Buttons  []string `json:"buttons"`
}

type UserRegisterRequest struct {
	Username string  `json:"username" binding:"required"`
	Password string  `json:"password" binding:"required"`
	Nickname *string `json:"nickname"`
	Email    *string `json:"email"`
	Phone    *string `json:"phone"`
}

type UserUpdateRequest struct {
	Nickname *string `json:"nickname"`
	Email    *string `json:"email" binding:"omitempty,email"`
	Phone    *string `json:"phone"`
	Avatar   *string `json:"avatar"`
}

type UserChangePasswordRequest struct {
	OldPassword string `json:"oldPassword" binding:"required"`
	NewPassword string `json:"newPassword" binding:"required"`
}

type UserListRequest struct {
	Current  int    `json:"current" form:"current"`
	Size     int    `json:"size" form:"size"`
	Username string `json:"username" form:"username"`
	Phone    string `json:"phone" form:"phone"`
	Email    string `json:"email" form:"email"`
	Status   int    `json:"status" form:"status"`
}

type UserListResponse struct {
	List  []User `json:"list"`
	Total int64  `json:"total"`
}
