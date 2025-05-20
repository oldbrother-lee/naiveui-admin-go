package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"

	"github.com/gin-gonic/gin"
)

func RegisterTaskConfigRoutes(r *gin.RouterGroup) {
	repo := repository.NewTaskConfigRepository()
	svc := service.NewTaskConfigService(repo)
	ctrl := controller.NewTaskConfigController(svc)

	r.POST("/task-config", ctrl.BatchCreate)
}
