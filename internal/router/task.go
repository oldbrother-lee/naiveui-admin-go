package router

import (
	"recharge-go/internal/handler"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/internal/service/platform"

	"github.com/gin-gonic/gin"
)

func RegisterTaskRoutes(r *gin.RouterGroup) {
	taskConfigRepo := repository.NewTaskConfigRepository()
	taskOrderRepo := repository.NewTaskOrderRepository()
	platformSvc := platform.NewService()

	taskConfigHandler := handler.NewTaskConfigHandler(taskConfigRepo)
	taskOrderHandler := handler.NewTaskOrderHandler(taskOrderRepo)
	taskSvc := service.NewTaskService(taskConfigRepo, taskOrderRepo, platformSvc)

	// 启动自动取单任务
	taskSvc.StartTask()

	// 取单任务配置路由
	taskConfig := r.Group("/task-config")
	{
		taskConfig.POST("", taskConfigHandler.Create)
		taskConfig.PUT("", taskConfigHandler.Update)
		taskConfig.DELETE("/:id", taskConfigHandler.Delete)
		taskConfig.GET("/:id", taskConfigHandler.Get)
		taskConfig.GET("", taskConfigHandler.List)
	}

	// 取单任务订单路由
	taskOrder := r.Group("/task-order")
	{
		taskOrder.GET("", taskOrderHandler.List)
		taskOrder.GET("/:order_number", taskOrderHandler.GetByOrderNumber)
	}
}
