package model

import (
	"time"
)

// User 用户模型
type User struct {
	ID        int64     `json:"id" gorm:"primaryKey;type:bigint;not null"`
	Username  string    `json:"username" gorm:"size:50;not null;unique"`
	Password  string    `json:"-" gorm:"size:100;not null"`
	Nickname  string    `json:"nickname" gorm:"size:50"`
	Phone     string    `json:"phone" gorm:"size:20"`
	Email     string    `json:"email" gorm:"size:100"`
	Avatar    string    `json:"avatar" gorm:"size:255"`
	Type      int       `json:"type" gorm:"type:tinyint;default:1;comment:用户类型(1:普通用户 2:代理商 3:管理员)"`
	Gender    int       `json:"gender" gorm:"type:tinyint;default:0;comment:性别(0:未知 1:男 2:女)"`
	Credit    float64   `json:"credit" gorm:"type:decimal(10,2);default:0.00;comment:授信额度"`
	Status    int       `json:"status" gorm:"type:bigint;default:1"`
	LastLogin time.Time `json:"last_login" gorm:"type:datetime"`
	CreatedAt time.Time `json:"created_at" gorm:"type:datetime;autoCreateTime"`
	UpdatedAt time.Time `json:"updated_at" gorm:"type:datetime;autoUpdateTime"`
}

// TableName 指定表名
func (User) TableName() string {
	return "users"
}

// UserGrade 用户等级模型
type UserGrade struct {
	ID          int64     `json:"id" gorm:"primaryKey;type:bigint;not null"`
	Name        string    `json:"name" gorm:"size:50;not null"`
	Description string    `json:"description" gorm:"size:255"`
	Icon        string    `json:"icon" gorm:"size:255"`
	MinPoints   int64     `json:"min_points" gorm:"type:bigint;default:0"`
	Discount    float64   `json:"discount" gorm:"type:decimal(10,2);default:1.00"`
	Status      int       `json:"status" gorm:"type:tinyint;default:1"`
	CreatedAt   time.Time `json:"created_at" gorm:"type:datetime;autoCreateTime"`
	UpdatedAt   time.Time `json:"updated_at" gorm:"type:datetime;autoUpdateTime"`
}

// TableName 指定表名
func (UserGrade) TableName() string {
	return "user_grades"
}

// UserTag 用户标签模型
type UserTag struct {
	ID        int64     `json:"id" gorm:"primaryKey;type:bigint;not null"`
	Name      string    `json:"name" gorm:"size:50;not null"`
	Category  string    `json:"category" gorm:"size:50"`
	Color     string    `json:"color" gorm:"size:20"`
	Status    int       `json:"status" gorm:"type:tinyint;default:1"`
	CreatedAt time.Time `json:"created_at" gorm:"type:datetime;autoCreateTime"`
	UpdatedAt time.Time `json:"updated_at" gorm:"type:datetime;autoUpdateTime"`
}

// TableName 指定表名
func (UserTag) TableName() string {
	return "user_tags"
}

// UserTagRelation 用户标签关系模型
type UserTagRelation struct {
	ID        int64     `json:"id" gorm:"primaryKey;type:bigint;not null"`
	UserID    int64     `json:"user_id" gorm:"type:bigint;not null"`
	TagID     int64     `json:"tag_id" gorm:"type:bigint;not null"`
	CreatedAt time.Time `json:"created_at" gorm:"type:datetime;autoCreateTime"`
}

// TableName 指定表名
func (UserTagRelation) TableName() string {
	return "user_tag_relations"
}

// UserGradeRelation 用户等级关系模型
type UserGradeRelation struct {
	ID        int64     `json:"id" gorm:"primaryKey;type:bigint;not null"`
	UserID    int64     `json:"user_id" gorm:"type:bigint;not null"`
	GradeID   int64     `json:"grade_id" gorm:"type:bigint;not null"`
	CreatedAt time.Time `json:"created_at" gorm:"type:datetime;autoCreateTime"`
}

// TableName 指定表名
func (UserGradeRelation) TableName() string {
	return "user_grade_relations"
}

// Request and Response structures
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
	Username *string  `json:"username"`
	Nickname *string  `json:"nickname"`
	Email    *string  `json:"email" binding:"omitempty,email"`
	Phone    *string  `json:"phone"`
	Avatar   *string  `json:"avatar"`
	Status   *int     `json:"status"`
	Type     *int     `json:"type"`
	Gender   *int     `json:"gender"`
	Credit   *float64 `json:"credit"`
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
	List  []UserResponse `json:"list"`
	Total int64          `json:"total"`
}

// UserResponse 用户响应结构体
type UserResponse struct {
	ID        int64     `json:"id"`
	Username  string    `json:"username"`
	Nickname  string    `json:"nickname"`
	Phone     string    `json:"phone"`
	Email     string    `json:"email"`
	Avatar    string    `json:"avatar"`
	Status    int       `json:"status"`
	LastLogin time.Time `json:"last_login"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

// UserType 用户类型
const (
	UserTypeNormal = 1 // 普通用户
	UserTypeAgent  = 2 // 代理商
	UserTypeAdmin  = 3 // 管理员
)

// UserGender 用户性别
const (
	UserGenderUnknown = 0 // 未知
	UserGenderMale    = 1 // 男
	UserGenderFemale  = 2 // 女
)
