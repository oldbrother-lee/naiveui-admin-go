package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/internal/service/recharge"
	"recharge-go/pkg/database"

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
	orderService := service.NewOrderService(orderRepo, rechargeService)

	// 创建控制器
	orderController := controller.NewExternalOrderController(orderService)

	// 注册路由
	external := r.Group("/external")
	{
		external.POST("/order", orderController.CreateOrder)
		external.GET("/order/:id", orderController.GetOrder)
	}
}
