package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/internal/service/recharge"
	"recharge-go/pkg/queue"

	"github.com/gin-gonic/gin"
	"gorm.io/gorm"
)

// RegisterCallbackRoutes 注册回调相关路由
func RegisterCallbackRoutes(r *gin.RouterGroup, db *gorm.DB) {
	// 创建服务实例
	orderRepo := repository.NewOrderRepository(db)
	platformRepo := repository.NewPlatformRepository(db)
	callbackLogRepo := repository.NewCallbackLogRepository(db)
	manager := recharge.NewManager(db)

	// 创建通知仓库
	notificationRepo := repository.NewNotificationRepository(db)

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
		db,
		orderService,
		repository.NewProductAPIRelationRepository(db),
		service.NewPlatformAPIParamService(repository.NewPlatformAPIParamRepository(db)),
		repository.NewRetryRepository(db),
	)

	// 设置 orderService 的 rechargeService
	orderService.SetRechargeService(rechargeService)

	// 创建控制器
	callbackController := controller.NewCallbackController(rechargeService, platformRepo, orderRepo)

	// 注册路由
	callback := r.Group("/callback")
	{
		// 修改客客帮回调路由
		kekebangOrder := callback.Group("/kekebang/:userid")
		{
			kekebangOrder.POST("", callbackController.HandleKekebangCallback)
		}

		// 添加米师师回调路由
		mishiOrder := callback.Group("/mishi/:userid")
		{
			mishiOrder.POST("", callbackController.HandleMishiCallback)
		}
	}
}
