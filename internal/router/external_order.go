package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/internal/service/recharge"
	"recharge-go/pkg/database"

	"github.com/gin-gonic/gin"
)

func RegisterExternalOrderRoutes(r *gin.RouterGroup) {
	// 初始化仓库
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	productAPIRelationRepo := repository.NewProductAPIRelationRepository(database.DB)

	// 初始化服务
	rechargeService := service.NewRechargeService(
		orderRepo,
		platformRepo,
		productAPIRelationRepo,
		recharge.NewManager(),
	)
	orderService := service.NewOrderService(orderRepo, rechargeService)

	// 初始化控制器
	externalOrderController := controller.NewExternalOrderController(orderService)

	externalGroup := r.Group("/external")
	{
		externalGroup.POST("/order", externalOrderController.CreateOrder)
		// 可扩展：externalOrderGroup.GET("/:out_trade_num", externalOrderController.GetOrder)
		// 可扩展：externalOrderGroup.POST("/notify", externalOrderController.Notify)
	}
}
