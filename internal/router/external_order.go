package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	notificationRepo "recharge-go/internal/repository/notification"
	"recharge-go/internal/service"
	"recharge-go/internal/service/recharge"
	"recharge-go/pkg/database"
	"recharge-go/pkg/queue"

	"github.com/gin-gonic/gin"
)

// RegisterExternalOrderRoutes 注册外部订单相关路由
func RegisterExternalOrderRoutes(r *gin.RouterGroup) {
	// 创建服务实例
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	callbackLogRepo := repository.NewCallbackLogRepository(database.DB)
	manager := recharge.NewManager(database.DB)
	rechargeService := service.NewRechargeService(orderRepo, platformRepo, manager, callbackLogRepo, database.DB)

	// 创建通知仓库
	notificationRepo := notificationRepo.NewRepository(database.DB)

	// 创建队列实例
	queueInstance := queue.NewRedisQueue()

	orderService := service.NewOrderService(orderRepo, rechargeService, notificationRepo, queueInstance)

	// 创建控制器
	externalOrderController := controller.NewExternalOrderController(orderService)

	// 注册路由
	externalOrder := r.Group("/external/order")
	{
		externalOrder.POST("", externalOrderController.CreateOrder)
		externalOrder.GET("/:id", externalOrderController.GetOrder)
	}
}
