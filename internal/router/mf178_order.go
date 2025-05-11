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
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	productAPIRelationRepo := repository.NewProductAPIRelationRepository(database.DB)

	// 初始化充值服务
	_ = service.NewRechargeService(
		orderRepo,
		platformRepo,
		productAPIRelationRepo,
	)
}

func RegisterMF178OrderRoutes(r *gin.RouterGroup) {
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
