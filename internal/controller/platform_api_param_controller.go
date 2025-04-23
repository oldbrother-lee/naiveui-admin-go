package controller

import (
	"net/http"
	"recharge-go/internal/model"
	"recharge-go/internal/service"
	"recharge-go/internal/validator"
	"strconv"

	"github.com/gin-gonic/gin"
)

type PlatformAPIParamController struct {
	service service.PlatformAPIParamService
}

// NewPlatformAPIParamController 创建平台接口参数控制器实例
func NewPlatformAPIParamController(service service.PlatformAPIParamService) *PlatformAPIParamController {
	return &PlatformAPIParamController{service: service}
}

// CreateParam 创建平台接口参数
func (c *PlatformAPIParamController) CreateParam(ctx *gin.Context) {
	var param model.PlatformAPIParam
	if err := ctx.ShouldBindJSON(&param); err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	// 参数验证
	if err := validator.ValidatePlatformAPIParam(&param); err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	if err := c.service.CreateParam(ctx, &param); err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	ctx.JSON(http.StatusOK, gin.H{"data": param})
}

// UpdateParam 更新平台接口参数
func (c *PlatformAPIParamController) UpdateParam(ctx *gin.Context) {
	var param model.PlatformAPIParam
	if err := ctx.ShouldBindJSON(&param); err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	// 参数验证
	if err := validator.ValidatePlatformAPIParam(&param); err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	if err := c.service.UpdateParam(ctx, &param); err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	ctx.JSON(http.StatusOK, gin.H{"data": param})
}

// DeleteParam 删除平台接口参数
func (c *PlatformAPIParamController) DeleteParam(ctx *gin.Context) {
	id, err := strconv.ParseInt(ctx.Param("id"), 10, 64)
	if err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": "无效的参数ID"})
		return
	}

	if err := c.service.DeleteParam(ctx, id); err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	ctx.JSON(http.StatusOK, gin.H{"message": "删除成功"})
}

// GetParam 获取平台接口参数详情
func (c *PlatformAPIParamController) GetParam(ctx *gin.Context) {
	id, err := strconv.ParseInt(ctx.Param("id"), 10, 64)
	if err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": "无效的参数ID"})
		return
	}

	param, err := c.service.GetParam(ctx, id)
	if err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	ctx.JSON(http.StatusOK, gin.H{"data": param})
}

// ListParams 获取平台接口参数列表
func (c *PlatformAPIParamController) ListParams(ctx *gin.Context) {
	apiID, _ := strconv.ParseInt(ctx.Query("api_id"), 10, 64)
	page, _ := strconv.Atoi(ctx.DefaultQuery("page", "1"))
	pageSize, _ := strconv.Atoi(ctx.DefaultQuery("page_size", "10"))

	params, total, err := c.service.ListParams(ctx, apiID, page, pageSize)
	if err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	ctx.JSON(http.StatusOK, gin.H{
		"data": params,
		"meta": gin.H{
			"total":     total,
			"page":      page,
			"page_size": pageSize,
		},
	})
}
