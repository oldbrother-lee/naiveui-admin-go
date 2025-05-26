package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/middleware"
	"recharge-go/internal/repository"
	notificationRepo "recharge-go/internal/repository/notification"
	"recharge-go/internal/service"
	"recharge-go/pkg/database"
	"recharge-go/pkg/queue"

	"github.com/gin-gonic/gin"
)

func init() {
	// 初始化仓库
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	callbackLogRepo := repository.NewCallbackLogRepository(database.DB)

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

	// 初始化充值服务
	platformAccountRepo := repository.NewPlatformAccountRepository(database.DB)
	userRepo := repository.NewUserRepository(database.DB)
	balanceLogRepo := repository.NewBalanceLogRepository(database.DB)
	balanceService := service.NewPlatformAccountBalanceService(
		database.DB,
		platformAccountRepo,
		userRepo,
		balanceLogRepo,
	)
	platformAPIRepo := repository.NewPlatformAPIRepository(database.DB)
	productAPIRelationRepo := repository.NewProductAPIRelationRepository(database.DB)
	platformAPIParamRepo := repository.NewPlatformAPIParamRepository(database.DB)
	retryRepo := repository.NewRetryRepository(database.DB)

	rechargeService := service.NewRechargeService(
		database.DB,
		orderRepo,
		platformRepo,
		platformAPIRepo,
		retryRepo,
		callbackLogRepo,
		productAPIRelationRepo,
		platformAPIParamRepo,
		balanceService,
		notificationRepo,
		queueInstance,
	)

	// 设置 orderService 的 rechargeService
	orderService.SetRechargeService(rechargeService)
}

// RegisterMF178OrderRoutes 注册MF178订单相关路由
func RegisterMF178OrderRoutes(r *gin.RouterGroup) {
	// 创建服务实例
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	callbackLogRepo := repository.NewCallbackLogRepository(database.DB)

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
	platformAccountRepo := repository.NewPlatformAccountRepository(database.DB)
	userRepo := repository.NewUserRepository(database.DB)
	balanceLogRepo := repository.NewBalanceLogRepository(database.DB)
	balanceService := service.NewPlatformAccountBalanceService(
		database.DB,
		platformAccountRepo,
		userRepo,
		balanceLogRepo,
	)
	platformAPIRepo := repository.NewPlatformAPIRepository(database.DB)
	productAPIRelationRepo := repository.NewProductAPIRelationRepository(database.DB)
	platformAPIParamRepo := repository.NewPlatformAPIParamRepository(database.DB)
	retryRepo := repository.NewRetryRepository(database.DB)

	rechargeService := service.NewRechargeService(
		database.DB,
		orderRepo,
		platformRepo,
		platformAPIRepo,
		retryRepo,
		callbackLogRepo,
		productAPIRelationRepo,
		platformAPIParamRepo,
		balanceService,
		notificationRepo,
		queueInstance,
	)

	// 设置 orderService 的 rechargeService
	orderService.SetRechargeService(rechargeService)

	// 创建控制器
	mf178OrderController := controller.NewMF178OrderController(orderService, rechargeService)

	// 注册路由
	mf178Order := r.Group("/mf178/order/:userid", middleware.MF178Auth())
	{
		mf178Order.POST("", mf178OrderController.CreateOrder)
		mf178Order.POST("/query", mf178OrderController.QueryOrder)
	}
}
