package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	notificationRepo "recharge-go/internal/repository/notification"
	"recharge-go/internal/service"
	"recharge-go/internal/service/recharge"
	"recharge-go/pkg/database"

	"github.com/gin-gonic/gin"
)

// RegisterOrderRoutes 注册订单相关路由
func RegisterOrderRoutes(r *gin.RouterGroup) {
	// 创建服务实例
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	callbackLogRepo := repository.NewCallbackLogRepository(database.DB)
	manager := recharge.NewManager(database.DB)
	rechargeService := service.NewRechargeService(orderRepo, platformRepo, manager, callbackLogRepo, database.DB)

	// 创建通知仓库
	notificationRepo := notificationRepo.NewRepository(database.DB)

	orderService := service.NewOrderService(orderRepo, rechargeService, notificationRepo)

	// 创建控制器
	orderController := controller.NewOrderController(orderService)

	// 注册路由
	order := r.Group("/order")
	{
		order.POST("", orderController.CreateOrder)
		order.GET("/:id", orderController.GetOrderByID)
		order.GET("", orderController.GetOrders)
		order.PUT("/:id/status", orderController.UpdateOrderStatus)
		order.POST("/:id/payment", orderController.ProcessOrderPayment)
		order.POST("/:id/recharge", orderController.ProcessOrderRecharge)
		order.POST("/:id/success", orderController.ProcessOrderSuccess)
		order.POST("/:id/fail", orderController.ProcessOrderFail)
		order.POST("/:id/refund", orderController.ProcessOrderRefund)
		order.POST("/:id/cancel", orderController.ProcessOrderCancel)
		order.POST("/:id/split", orderController.ProcessOrderSplit)
		order.POST("/:id/partial", orderController.ProcessOrderPartial)
	}
}
