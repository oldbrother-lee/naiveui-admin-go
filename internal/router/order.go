package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/pkg/database"

	"github.com/gin-gonic/gin"
)

// RegisterOrderRoutes 注册订单相关路由
func RegisterOrderRoutes(r *gin.RouterGroup) {
	// 初始化仓库
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	productAPIRelationRepo := repository.NewProductAPIRelationRepository(database.DB)

	// 初始化服务
	rechargeService := service.NewRechargeService(
		orderRepo,
		platformRepo,
		productAPIRelationRepo,
	)
	orderService := service.NewOrderService(orderRepo, rechargeService)

	// 初始化控制器
	orderController := controller.NewOrderController(orderService)

	// 订单路由组
	orderGroup := r.Group("/order")
	{
		// 创建订单
		orderGroup.POST("/create", orderController.CreateOrder)
		// 获取订单详情
		orderGroup.GET("/:id", orderController.GetOrderByID)
		// 获取订单列表
		orderGroup.GET("/list/:customer_id", orderController.GetOrdersByCustomerID)
		// 更新订单状态
		orderGroup.PUT("/:id/status", orderController.UpdateOrderStatus)
		// 处理订单支付
		orderGroup.POST("/:id/payment", orderController.ProcessOrderPayment)
		// 处理订单充值
		orderGroup.POST("/:id/recharge", orderController.ProcessOrderRecharge)
		// 处理订单成功
		orderGroup.POST("/:id/success", orderController.ProcessOrderSuccess)
		// 处理订单失败
		orderGroup.POST("/:id/fail", orderController.ProcessOrderFail)
		// 处理订单退款
		orderGroup.POST("/:id/refund", orderController.ProcessOrderRefund)
		// 处理订单取消
		orderGroup.POST("/:id/cancel", orderController.ProcessOrderCancel)
		// 处理订单拆单
		orderGroup.POST("/:id/split", orderController.ProcessOrderSplit)
		// 处理订单部分充值
		orderGroup.POST("/:id/partial", orderController.ProcessOrderPartial)
		orderGroup.GET("/list", orderController.GetOrders)
	}
}
