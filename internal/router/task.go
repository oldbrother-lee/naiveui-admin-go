package router

import (
	"recharge-go/internal/handler"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/internal/service/platform"
	"time"

	"github.com/gin-gonic/gin"
)

func RegisterTaskRoutes(r *gin.RouterGroup) {
	taskConfigRepo := repository.NewTaskConfigRepository()
	taskOrderRepo := repository.NewTaskOrderRepository()
	platformSvc := platform.NewService()

	taskConfig := &service.TaskConfig{
		Interval:      5 * time.Minute, // 每5分钟执行一次
		MaxRetries:    3,               // 最大重试3次
		RetryDelay:    1 * time.Minute, // 重试间隔1分钟
		MaxConcurrent: 5,               // 最大并发5个任务
		APIKey:        "",              // API密钥
		UserID:        "",              // 用户ID
		BaseURL:       "",              // API基础URL
	}

	taskConfigHandler := handler.NewTaskConfigHandler(taskConfigRepo)
	taskOrderHandler := handler.NewTaskOrderHandler(taskOrderRepo)
	taskSvc := service.NewTaskService(taskConfigRepo, taskOrderRepo, platformSvc, taskConfig)

	// 启动自动取单任务
	taskSvc.StartTask()

	// 取单任务配置路由
	taskConfigGroup := r.Group("/task-config")
	{
		taskConfigGroup.POST("", taskConfigHandler.Create)
		taskConfigGroup.PUT("", taskConfigHandler.Update)
		taskConfigGroup.DELETE("/:id", taskConfigHandler.Delete)
		taskConfigGroup.GET("/:id", taskConfigHandler.Get)
		taskConfigGroup.GET("", taskConfigHandler.List)
	}

	// 取单任务订单路由
	taskOrder := r.Group("/task-order")
	{
		taskOrder.GET("", taskOrderHandler.List)
		taskOrder.GET("/:order_number", taskOrderHandler.GetByOrderNumber)
	}
}
