package controller

import (
	"net/http"
	"recharge-go/internal/model"
	"recharge-go/internal/service"
	"recharge-go/internal/utils"
	"strconv"

	"github.com/gin-gonic/gin"
)

type PlatformController struct {
	service *service.PlatformService
}

func NewPlatformController(service *service.PlatformService) *PlatformController {
	return &PlatformController{service: service}
}

// ListPlatforms 获取平台列表
func (c *PlatformController) ListPlatforms(ctx *gin.Context) {
	var req model.PlatformListRequest
	if err := ctx.ShouldBindQuery(&req); err != nil {
		utils.Error(ctx, http.StatusBadRequest, err.Error())
		return
	}

	platforms, total := c.service.ListPlatforms(&req)

	resp := gin.H{
		"list":  platforms,
		"total": total,
	}

	utils.Success(ctx, resp)
}

// CreatePlatform 创建平台
func (c *PlatformController) CreatePlatform(ctx *gin.Context) {
	var req model.PlatformCreateRequest
	if err := ctx.ShouldBindJSON(&req); err != nil {
		utils.Error(ctx, http.StatusBadRequest, err.Error())
		return
	}

	if err := c.service.CreatePlatform(&req); err != nil {
		utils.Error(ctx, http.StatusInternalServerError, err.Error())
		return
	}

	utils.Success(ctx, nil)
}

// UpdatePlatform 更新平台
func (c *PlatformController) UpdatePlatform(ctx *gin.Context) {
	id, err := strconv.ParseInt(ctx.Param("id"), 10, 64)
	if err != nil {
		utils.Error(ctx, http.StatusBadRequest, "invalid platform id")
		return
	}

	var req model.PlatformUpdateRequest
	if err := ctx.ShouldBindJSON(&req); err != nil {
		utils.Error(ctx, http.StatusBadRequest, err.Error())
		return
	}

	err = c.service.UpdatePlatform(id, &req)
	if err != nil {
		utils.Error(ctx, http.StatusInternalServerError, err.Error())
		return
	}

	utils.Success(ctx, nil)
}

// DeletePlatform 删除平台
func (c *PlatformController) DeletePlatform(ctx *gin.Context) {
	id, err := strconv.ParseInt(ctx.Param("id"), 10, 64)
	if err != nil {
		utils.Error(ctx, http.StatusBadRequest, "invalid platform id")
		return
	}

	err = c.service.DeletePlatform(id)
	if err != nil {
		utils.Error(ctx, http.StatusInternalServerError, err.Error())
		return
	}

	utils.Success(ctx, nil)
}

// GetPlatform 获取平台详情
func (c *PlatformController) GetPlatform(ctx *gin.Context) {
	id, err := strconv.ParseInt(ctx.Param("id"), 10, 64)
	if err != nil {
		utils.Error(ctx, http.StatusBadRequest, "invalid platform id")
		return
	}

	platform, err := c.service.GetPlatform(id)
	if err != nil {
		utils.Error(ctx, http.StatusInternalServerError, err.Error())
		return
	}

	utils.Success(ctx, platform)
}

// ListPlatformAccounts 获取平台账号列表
func (c *PlatformController) ListPlatformAccounts(ctx *gin.Context) {
	var req model.PlatformAccountListRequest
	if err := ctx.ShouldBindQuery(&req); err != nil {
		utils.Error(ctx, http.StatusBadRequest, err.Error())
		return
	}

	resp, err := c.service.ListPlatformAccounts(&req)
	if err != nil {
		utils.Error(ctx, http.StatusInternalServerError, err.Error())
		return
	}

	utils.Success(ctx, resp)
}

// CreatePlatformAccount 创建平台账号
func (c *PlatformController) CreatePlatformAccount(ctx *gin.Context) {
	var req model.PlatformAccountCreateRequest
	if err := ctx.ShouldBindJSON(&req); err != nil {
		utils.Error(ctx, http.StatusBadRequest, err.Error())
		return
	}

	if err := c.service.CreatePlatformAccount(&req); err != nil {
		utils.Error(ctx, http.StatusInternalServerError, err.Error())
		return
	}

	utils.Success(ctx, nil)
}

// UpdatePlatformAccount 更新平台账号
func (c *PlatformController) UpdatePlatformAccount(ctx *gin.Context) {
	id, err := strconv.ParseInt(ctx.Param("id"), 10, 64)
	if err != nil {
		utils.Error(ctx, http.StatusBadRequest, "invalid account id")
		return
	}

	var req model.PlatformAccountUpdateRequest
	if err := ctx.ShouldBindJSON(&req); err != nil {
		utils.Error(ctx, http.StatusBadRequest, err.Error())
		return
	}

	account := &model.PlatformAccount{
		ID:           id,
		AccountName:  req.AccountName,
		Type:         req.Type,
		AppKey:       req.AppKey,
		AppSecret:    req.AppSecret,
		Description:  req.Description,
		DailyLimit:   req.DailyLimit,
		MonthlyLimit: req.MonthlyLimit,
		Balance:      req.Balance,
		Priority:     req.Priority,
	}
	if req.Status != nil {
		account.Status = *req.Status
	}

	if err := c.service.UpdatePlatformAccount(account); err != nil {
		utils.Error(ctx, http.StatusInternalServerError, err.Error())
		return
	}

	utils.Success(ctx, nil)
}

// DeletePlatformAccount 删除平台账号
func (c *PlatformController) DeletePlatformAccount(ctx *gin.Context) {
	id, err := strconv.ParseInt(ctx.Param("id"), 10, 64)
	if err != nil {
		utils.Error(ctx, http.StatusBadRequest, "invalid account id")
		return
	}

	if err := c.service.DeletePlatformAccount(id); err != nil {
		utils.Error(ctx, http.StatusInternalServerError, err.Error())
		return
	}

	utils.Success(ctx, nil)
}

// GetPlatformAccount 获取平台账号详情
func (c *PlatformController) GetPlatformAccount(ctx *gin.Context) {
	id, err := strconv.ParseInt(ctx.Param("id"), 10, 64)
	if err != nil {
		utils.Error(ctx, http.StatusBadRequest, "invalid account id")
		return
	}

	account, err := c.service.GetPlatformAccount(id)
	if err != nil {
		utils.Error(ctx, http.StatusInternalServerError, err.Error())
		return
	}

	utils.Success(ctx, account)
}
