package controller

import (
	"net/http"
	"recharge-go/internal/model"
	"recharge-go/internal/service"
	"recharge-go/internal/utils"
	"strconv"

	"github.com/gin-gonic/gin"
)

// PermissionController handles permission-related HTTP requests
type PermissionController struct {
	permissionService *service.PermissionService
}

// NewPermissionController creates a new PermissionController
func NewPermissionController(permissionService *service.PermissionService) *PermissionController {
	return &PermissionController{
		permissionService: permissionService,
	}
}

// @Summary 创建权限
// @Description 创建新的权限
// @Tags 权限管理
// @Accept json
// @Produce json
// @Param permission body model.PermissionRequest true "权限信息"
// @Success 200 {object} model.Response
// @Failure 400 {object} model.Response
// @Failure 500 {object} model.Response
// @Router /api/v1/permissions [post]
func (c *PermissionController) Create(ctx *gin.Context) {
	var permission model.Permission
	if err := ctx.ShouldBindJSON(&permission); err != nil {
		ctx.JSON(http.StatusBadRequest, model.Response{
			Code:    http.StatusBadRequest,
			Message: "Invalid request body",
			Data:    nil,
		})
		return
	}

	createdPermission, err := c.permissionService.CreatePermission(&permission)
	if err != nil {
		ctx.JSON(http.StatusInternalServerError, model.Response{
			Code:    http.StatusInternalServerError,
			Message: "Failed to create permission",
			Data:    nil,
		})
		return
	}

	ctx.JSON(http.StatusOK, model.Response{
		Code:    http.StatusOK,
		Message: "Success",
		Data:    createdPermission,
	})
}

// @Summary 更新权限
// @Description 更新指定ID的权限信息
// @Tags 权限管理
// @Accept json
// @Produce json
// @Param id path int true "权限ID"
// @Param permission body model.Permission true "权限信息"
// @Success 200 {object} model.Response
// @Failure 400 {object} model.Response
// @Failure 500 {object} model.Response
// @Router /api/v1/permissions/{id} [put]
func (c *PermissionController) Update(ctx *gin.Context) {
	id, err := strconv.ParseInt(ctx.Param("id"), 10, 64)
	if err != nil {
		ctx.JSON(http.StatusBadRequest, model.Response{
			Code:    http.StatusBadRequest,
			Message: "Invalid permission ID",
			Data:    nil,
		})
		return
	}

	var permission model.Permission
	if err := ctx.ShouldBindJSON(&permission); err != nil {
		ctx.JSON(http.StatusBadRequest, model.Response{
			Code:    http.StatusBadRequest,
			Message: "Invalid request body",
			Data:    nil,
		})
		return
	}

	permission.ID = id
	updatedPermission, err := c.permissionService.UpdatePermission(&permission)
	if err != nil {
		ctx.JSON(http.StatusInternalServerError, model.Response{
			Code:    http.StatusInternalServerError,
			Message: "Failed to update permission",
			Data:    nil,
		})
		return
	}

	ctx.JSON(http.StatusOK, model.Response{
		Code:    http.StatusOK,
		Message: "Success",
		Data:    updatedPermission,
	})
}

// @Summary 删除权限
// @Description 删除指定ID的权限
// @Tags 权限管理
// @Produce json
// @Param id path int true "权限ID"
// @Success 200 {object} model.Response
// @Failure 400 {object} model.Response
// @Failure 500 {object} model.Response
// @Router /api/v1/permissions/{id} [delete]
func (c *PermissionController) Delete(ctx *gin.Context) {
	id, err := strconv.ParseInt(ctx.Param("id"), 10, 64)
	if err != nil {
		ctx.JSON(http.StatusBadRequest, model.Response{
			Code:    http.StatusBadRequest,
			Message: "Invalid permission ID",
			Data:    nil,
		})
		return
	}

	err = c.permissionService.DeletePermission(id)
	if err != nil {
		ctx.JSON(http.StatusInternalServerError, model.Response{
			Code:    http.StatusInternalServerError,
			Message: "Failed to delete permission",
			Data:    nil,
		})
		return
	}

	ctx.JSON(http.StatusOK, model.Response{
		Code:    http.StatusOK,
		Message: "Success",
		Data:    nil,
	})
}

// @Summary 获取权限树
// @Description 获取完整的权限树结构
// @Tags 权限管理
// @Produce json
// @Success 200 {object} model.Response{data=[]*model.PermissionTree}
// @Failure 500 {object} model.Response
// @Router /api/v1/permissions/tree [get]
func (c *PermissionController) GetTree(ctx *gin.Context) {
	tree, err := c.permissionService.GetPermissionTree()
	if err != nil {
		utils.Error(ctx, 500, err.Error())
		return
	}
	utils.Success(ctx, tree)
}

// @Summary 获取菜单权限
// @Description 获取所有菜单权限
// @Tags 权限管理
// @Produce json
// @Success 200 {object} model.Response{data=[]model.Permission}
// @Failure 500 {object} model.Response
// @Router /api/v1/permissions/menus [get]
func (c *PermissionController) GetMenuPermissions(ctx *gin.Context) {
	menus, err := c.permissionService.GetMenuPermissions()
	if err != nil {
		utils.Error(ctx, 500, err.Error())
		return
	}
	utils.Success(ctx, menus)
}

// @Summary 获取所有权限
// @Description 获取所有权限（包括菜单和按钮）
// @Tags 权限管理
// @Produce json
// @Success 200 {object} model.Response{data=[]model.Permission}
// @Failure 500 {object} model.Response
// @Router /api/v1/permissions [get]
func (c *PermissionController) GetAllPermissions(ctx *gin.Context) {
	permissions, err := c.permissionService.GetAllPermissions()
	if err != nil {
		utils.Error(ctx, 500, err.Error())
		return
	}
	utils.Success(ctx, permissions)
}

// @Summary 获取按钮权限
// @Description 获取所有按钮权限
// @Tags 权限管理
// @Produce json
// @Success 200 {object} model.Response{data=[]model.Permission}
// @Failure 500 {object} model.Response
// @Router /api/v1/permissions/buttons [get]
func (c *PermissionController) GetButtonPermissions(ctx *gin.Context) {
	buttons, err := c.permissionService.GetButtonPermissions()
	if err != nil {
		utils.Error(ctx, 500, err.Error())
		return
	}
	utils.Success(ctx, buttons)
}
