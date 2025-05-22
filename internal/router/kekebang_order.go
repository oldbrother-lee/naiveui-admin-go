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

// RegisterKekebangOrderRoutes 注册可客帮订单相关路由
func RegisterKekebangOrderRoutes(r *gin.RouterGroup) {
	// 创建服务实例
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	callbackLogRepo := repository.NewCallbackLogRepository(database.DB)
	manager := recharge.NewManager(database.DB)

	// 创建通知仓库
	notificationRepo := notificationRepo.NewRepository(database.DB)

	// 创建队列实例
	queueInstance := queue.NewRedisQueue()

	// 创建订单服务
	orderService := service.NewOrderService(
		orderRepo,
		nil, // 先传入 nil，后面再设置
		notificationRepo,
		queueInstance,
	)

	// 创建充值服务
	rechargeService := service.NewRechargeService(
		orderRepo,
		platformRepo,
		manager,
		callbackLogRepo,
		database.DB,
		orderService,
		repository.NewProductAPIRelationRepository(database.DB),
		service.NewPlatformAPIParamService(repository.NewPlatformAPIParamRepository(database.DB)),
		repository.NewRetryRepository(database.DB),
	)

	// 设置 orderService 的 rechargeService
	orderService.SetRechargeService(rechargeService)

	// 创建控制器
	kekebangOrderController := controller.NewKekebangOrderController(orderService, rechargeService)

	// 注册路由
	kekebangOrder := r.Group("/kekebang/order/:userid")
	{
		kekebangOrder.POST("", kekebangOrderController.CreateOrder)
		kekebangOrder.POST("/query", kekebangOrderController.QueryOrder)
	}
}
