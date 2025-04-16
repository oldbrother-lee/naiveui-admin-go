package model

type Permission struct {
	ID          int64        `json:"id" gorm:"primaryKey"`
	Code        string       `json:"code" gorm:"size:50;uniqueIndex"`
	Name        string       `json:"name" gorm:"size:50"`
	Type        string       `json:"type" gorm:"size:20"` // MENU or BUTTON
	ParentID    *int64       `json:"parentId" gorm:"index"`
	Path        *string      `json:"path" gorm:"size:255"`
	Component   *string      `json:"component" gorm:"size:255"`
	Icon        string       `json:"icon" gorm:"size:50"`
	Layout      *string      `json:"layout" gorm:"size:50"`
	Method      *string      `json:"method" gorm:"size:20"`
	Description *string      `json:"description" gorm:"size:255"`
	Show        bool         `json:"show" gorm:"default:true"`
	Enable      bool         `json:"enable" gorm:"default:true"`
	Order       int64        `json:"order" gorm:"default:0"`
	KeepAlive   *bool        `json:"keepAlive"`
	Redirect    *string      `json:"redirect" gorm:"size:255"`
	Children    []Permission `json:"children" gorm:"-"`
}

// PermissionTree 用于构建权限树
type PermissionTree struct {
	ID          int64             `json:"id"`
	Code        string            `json:"code"`
	Name        string            `json:"name"`
	Type        string            `json:"type"`
	ParentID    interface{}       `json:"parentId"`
	Path        *string           `json:"path"`
	Component   *string           `json:"component"`
	Icon        string            `json:"icon"`
	Layout      *string           `json:"layout"`
	Method      interface{}       `json:"method"`
	Description interface{}       `json:"description"`
	Show        bool              `json:"show"`
	Enable      bool              `json:"enable"`
	Order       int64             `json:"order"`
	KeepAlive   interface{}       `json:"keepAlive"`
	Redirect    interface{}       `json:"redirect"`
	Children    []*PermissionTree `json:"children"`
}

// PermissionRequest 创建/更新权限的请求
type PermissionRequest struct {
	Code        string  `json:"code" binding:"required"`
	Name        string  `json:"name" binding:"required"`
	Type        string  `json:"type" binding:"required,oneof=MENU BUTTON"`
	ParentID    *int64  `json:"parentId"`
	Path        *string `json:"path"`
	Component   *string `json:"component"`
	Icon        string  `json:"icon"`
	Layout      *string `json:"layout"`
	Method      *string `json:"method"`
	Description *string `json:"description"`
	Show        bool    `json:"show"`
	Enable      bool    `json:"enable"`
	Order       int64   `json:"order"`
	KeepAlive   *bool   `json:"keepAlive"`
	Redirect    *string `json:"redirect"`
}

// PermissionResponse 权限响应
type PermissionResponse struct {
	Code    int         `json:"code"`
	Message string      `json:"message"`
	Data    interface{} `json:"data"`
}
