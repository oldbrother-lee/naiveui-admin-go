package controller

import (
	"net/http"
	"recharge-go/internal/model"
	"recharge-go/internal/service"
	"recharge-go/internal/utils"
	"strconv"

	"github.com/gin-gonic/gin"
)

type DaichongOrderController struct {
	service *service.DaichongOrderService
}

func NewDaichongOrderController(service *service.DaichongOrderService) *DaichongOrderController {
	return &DaichongOrderController{service: service}
}

// Create 新增订单
func (c *DaichongOrderController) Create(ctx *gin.Context) {
	var order model.DaichongOrder
	if err := ctx.ShouldBindJSON(&order); err != nil {
		utils.Error(ctx, http.StatusBadRequest, "参数错误")
		return
	}
	if err := c.service.Create(ctx, &order); err != nil {
		utils.Error(ctx, http.StatusInternalServerError, "创建订单失败")
		return
	}
	utils.Success(ctx, order)
}

// GetByID 查询订单
func (c *DaichongOrderController) GetByID(ctx *gin.Context) {
	idStr := ctx.Param("id")
	id, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil {
		utils.Error(ctx, http.StatusBadRequest, "ID参数错误")
		return
	}
	order, err := c.service.GetByID(ctx, id)
	if err != nil {
		utils.Error(ctx, http.StatusNotFound, "订单不存在")
		return
	}
	utils.Success(ctx, order)
}

// Update 更新订单
func (c *DaichongOrderController) Update(ctx *gin.Context) {
	var order model.DaichongOrder
	if err := ctx.ShouldBindJSON(&order); err != nil {
		utils.Error(ctx, http.StatusBadRequest, "参数错误")
		return
	}
	if err := c.service.Update(ctx, &order); err != nil {
		utils.Error(ctx, http.StatusInternalServerError, "更新订单失败")
		return
	}
	utils.Success(ctx, order)
}

// Delete 删除订单
func (c *DaichongOrderController) Delete(ctx *gin.Context) {
	idStr := ctx.Param("id")
	id, err := strconv.ParseInt(idStr, 10, 64)
	if err != nil {
		utils.Error(ctx, http.StatusBadRequest, "ID参数错误")
		return
	}
	if err := c.service.Delete(ctx, id); err != nil {
		utils.Error(ctx, http.StatusInternalServerError, "删除订单失败")
		return
	}
	utils.Success(ctx, nil)
}

// List 分页查询订单
func (c *DaichongOrderController) List(ctx *gin.Context) {
	page, _ := strconv.Atoi(ctx.DefaultQuery("page", "1"))
	pageSize, _ := strconv.Atoi(ctx.DefaultQuery("page_size", "10"))
	orders, total, err := c.service.List(ctx, page, pageSize)
	if err != nil {
		utils.Error(ctx, http.StatusInternalServerError, "查询订单列表失败")
		return
	}
	utils.Success(ctx, gin.H{"list": orders, "total": total})
}
