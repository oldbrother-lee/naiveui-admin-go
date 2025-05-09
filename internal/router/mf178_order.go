package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/middleware"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/pkg/database"

	"github.com/gin-gonic/gin"
)

func init() {
	// 初始化仓库
	taskRepo := repository.NewRechargeTaskRepository(database.DB)
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)

	// 初始化平台服务
	platformService := service.NewPlatformService(platformRepo)

	// 初始化充值服务 - 移除未使用的变量
	_ = service.NewRechargeService(
		taskRepo,
		orderRepo,
		platformService,
	)
}

func RegisterMF178OrderRoutes(r *gin.RouterGroup) {
	// 初始化仓库
	orderRepo := repository.NewOrderRepository(database.DB)
	taskRepo := repository.NewRechargeTaskRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)

	// 初始化服务
	orderService := service.NewOrderService(orderRepo)
	platformService := service.NewPlatformService(platformRepo)
	rechargeService := service.NewRechargeService(
		taskRepo,
		orderRepo,
		platformService,
	)

	mf178OrderController := controller.NewMF178OrderController(
		orderService,
		rechargeService,
	)

	mf178Group := r.Group("/mf178")
	{
		// 使用MF178特定的认证中间件
		mf178Group.Use(middleware.MF178Auth())
		mf178Group.POST("/order", mf178OrderController.CreateOrder)
		mf178Group.POST("/query", mf178OrderController.QueryOrder) // 查询订单
	}
}
