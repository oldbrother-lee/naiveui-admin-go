package controller

import (
	"net/http"
	"recharge-go/internal/service"
	"recharge-go/internal/utils"

	"github.com/gin-gonic/gin"
)

type TaskConfigController struct {
	svc *service.TaskConfigService
}

func NewTaskConfigController(svc *service.TaskConfigService) *TaskConfigController {
	return &TaskConfigController{svc: svc}
}

func (c *TaskConfigController) BatchCreate(ctx *gin.Context) {
	var payload []service.TaskConfigPayload
	if err := ctx.ShouldBindJSON(&payload); err != nil {
		utils.Error(ctx, http.StatusBadRequest, "参数错误")
		return
	}
	if err := c.svc.BatchCreate(payload); err != nil {
		utils.Error(ctx, http.StatusInternalServerError, err.Error())
		return
	}
	utils.Success(ctx, "保存成功")
}
