package controller

import (
	"net/http"
	"strconv"

	"recharge-go/internal/model"
	"recharge-go/internal/service"
	"recharge-go/internal/utils"

	"github.com/gin-gonic/gin"
)

// ProductAPIRelationController 商品接口关联控制器
type ProductAPIRelationController struct {
	svc service.ProductAPIRelationService
}

// NewProductAPIRelationController 创建商品接口关联控制器实例
func NewProductAPIRelationController(svc service.ProductAPIRelationService) *ProductAPIRelationController {
	return &ProductAPIRelationController{svc: svc}
}

// Create 创建商品接口关联
func (c *ProductAPIRelationController) Create(ctx *gin.Context) {
	var req model.ProductAPIRelationCreateRequest
	if err := ctx.ShouldBindJSON(&req); err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	if err := c.svc.Create(ctx, &req); err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	ctx.JSON(http.StatusOK, gin.H{"message": "success"})
}

// Update 更新商品接口关联
func (c *ProductAPIRelationController) Update(ctx *gin.Context) {
	var req model.ProductAPIRelationUpdateRequest
	if err := ctx.ShouldBindJSON(&req); err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	if err := c.svc.Update(ctx, &req); err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	ctx.JSON(http.StatusOK, gin.H{"message": "success"})
}

// Delete 删除商品接口关联
func (c *ProductAPIRelationController) Delete(ctx *gin.Context) {
	id, err := strconv.ParseInt(ctx.Param("id"), 10, 64)
	if err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": "invalid id"})
		return
	}

	if err := c.svc.Delete(ctx, id); err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	ctx.JSON(http.StatusOK, gin.H{"message": "success"})
}

// GetByID 根据ID获取商品接口关联
func (c *ProductAPIRelationController) GetByID(ctx *gin.Context) {
	id, err := strconv.ParseInt(ctx.Param("id"), 10, 64)
	if err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": "invalid id"})
		return
	}

	relation, err := c.svc.GetByID(ctx, id)
	if err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}

	if relation == nil {
		ctx.JSON(http.StatusNotFound, gin.H{"error": "not found"})
		return
	}

	ctx.JSON(http.StatusOK, relation)
}

// GetList 获取商品接口关联列表
func (c *ProductAPIRelationController) GetList(ctx *gin.Context) {
	var req model.ProductAPIRelationListRequest
	if err := ctx.ShouldBindQuery(&req); err != nil {
		utils.Error(ctx, http.StatusBadRequest, err.Error())
		return
	}

	// 处理可选参数
	if productID := ctx.Query("product_id"); productID != "" {
		id, err := strconv.ParseInt(productID, 10, 64)
		if err != nil {
			ctx.JSON(http.StatusBadRequest, gin.H{"error": "invalid product_id"})
			return
		}
		req.ProductID = &id
	}

	if apiID := ctx.Query("api_id"); apiID != "" {
		id, err := strconv.ParseInt(apiID, 10, 64)
		if err != nil {
			ctx.JSON(http.StatusBadRequest, gin.H{"error": "invalid api_id"})
			return
		}
		req.APIID = &id
	}

	if status := ctx.Query("status"); status != "" {
		s, err := strconv.Atoi(status)
		if err != nil {
			ctx.JSON(http.StatusBadRequest, gin.H{"error": "invalid status"})
			return
		}
		req.Status = &s
	}

	resp, err := c.svc.List(ctx, &req)
	if err != nil {
		utils.Error(ctx, http.StatusInternalServerError, err.Error())
		return
	}

	utils.Success(ctx, resp)
}
