package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/internal/service/recharge"
	"recharge-go/pkg/database"

	"github.com/gin-gonic/gin"
)

func init() {
	// 初始化仓库
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)

	// 初始化充值服务
	_ = service.NewRechargeService(
		orderRepo,
		platformRepo,
		recharge.NewManager(database.DB),
		repository.NewCallbackLogRepository(database.DB),
		database.DB,
	)
}

func RegisterMF178OrderRoutes(r *gin.RouterGroup) {
	// 创建服务实例
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	callbackLogRepo := repository.NewCallbackLogRepository(database.DB)
	manager := recharge.NewManager(database.DB)
	rechargeService := service.NewRechargeService(orderRepo, platformRepo, manager, callbackLogRepo, database.DB)
	orderService := service.NewOrderService(orderRepo, rechargeService)

	// 创建控制器
	orderController := controller.NewMF178OrderController(orderService, rechargeService)

	// 注册路由
	mf178 := r.Group("/mf178")
	{
		mf178.POST("/order", orderController.CreateOrder)
		mf178.POST("/query", orderController.QueryOrder)
	}
}
