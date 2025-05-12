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

// RegisterMF178OrderRoutes 注册MF178订单相关路由
func RegisterMF178OrderRoutes(r *gin.RouterGroup) {
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
	mf178OrderController := controller.NewMF178OrderController(orderService, rechargeService)

	// 注册路由
	mf178Order := r.Group("/mf178/order")
	{
		mf178Order.POST("", mf178OrderController.CreateOrder)
		mf178Order.POST("/query", mf178OrderController.QueryOrder)
	}
}
