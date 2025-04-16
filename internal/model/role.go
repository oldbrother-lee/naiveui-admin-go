package model

import "time"

// Role represents a role in the system
type Role struct {
	ID          int64      `json:"id" gorm:"primaryKey"`
	Name        string     `json:"name" gorm:"size:50;not null;uniqueIndex" binding:"required"`
	Code        string     `json:"code" gorm:"size:50;not null;uniqueIndex" binding:"required"`
	Description string     `json:"description" gorm:"size:200"`
	Status      int        `json:"status" gorm:"default:1"` // 1: enabled, 0: disabled
	CreatedAt   time.Time  `json:"created_at"`
	UpdatedAt   time.Time  `json:"updated_at"`
	DeletedAt   *time.Time `json:"deleted_at,omitempty" gorm:"index"`
}

// RoleRequest represents the request body for creating/updating a role
type RoleRequest struct {
	Name        string `json:"name" binding:"required"`
	Code        string `json:"code" binding:"required"`
	Description string `json:"description"`
	Status      int    `json:"status"`
}

// RolePermission represents the relationship between roles and permissions
type RolePermission struct {
	ID           int64      `json:"id" gorm:"primaryKey"`
	RoleID       int64      `json:"role_id" gorm:"index"`
	PermissionID int64      `json:"permission_id" gorm:"index"`
	CreatedAt    time.Time  `json:"created_at"`
	UpdatedAt    time.Time  `json:"updated_at"`
	DeletedAt    *time.Time `json:"deleted_at,omitempty" gorm:"index"`
}

// RoleWithPermissions represents a role with its permissions
type RoleWithPermissions struct {
	Role        Role         `json:"role"`
	Permissions []Permission `json:"permissions"`
}
