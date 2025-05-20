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

	r.POST("/task-config", ctrl.Create)
	r.PUT("/task-config", ctrl.Update)
	r.DELETE("/task-config/:id", ctrl.Delete)
	r.GET("/task-config/:id", ctrl.GetByID)
	r.GET("/task-config", ctrl.List)
}
