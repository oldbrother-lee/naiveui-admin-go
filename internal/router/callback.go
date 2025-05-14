package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/internal/service/recharge"
	"recharge-go/pkg/database"
	"recharge-go/pkg/queue"

	"github.com/gin-gonic/gin"
)

// RegisterCallbackRoutes 注册回调相关路由
func RegisterCallbackRoutes(r *gin.RouterGroup) {
	// 创建服务实例
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	callbackLogRepo := repository.NewCallbackLogRepository(database.DB)
	manager := recharge.NewManager(database.DB)

	// 创建通知仓库
	notificationRepo := repository.NewNotificationRepository(database.DB)

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
	)

	// 设置 orderService 的 rechargeService
	orderService.SetRechargeService(rechargeService)

	// 创建控制器
	callbackController := controller.NewCallbackController(rechargeService)

	// 注册路由
	callback := r.Group("/callback")
	{
		callback.POST("/kekebang", callbackController.HandleKekebangCallback)
	}
}
