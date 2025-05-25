package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/handler"
	"recharge-go/internal/middleware"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/pkg/database"

	"github.com/gin-gonic/gin"
)

func SetupRouter(
	userController *controller.UserController,
	permissionController *controller.PermissionController,
	roleController *controller.RoleController,
	productController *controller.ProductController,
	userService *service.UserService,
	phoneLocationController *controller.PhoneLocationController,
	productTypeController *controller.ProductTypeController,
	platformController *controller.PlatformController,
	platformAPIController *controller.PlatformAPIController,
	platformAPIParamController *controller.PlatformAPIParamController,
	productAPIRelationController *controller.ProductAPIRelationController,
	userLogController *controller.UserLogController,
	userGradeController *controller.UserGradeController,
	rechargeHandler *handler.RechargeHandler,
	retryService *service.RetryService,
	userRepo *repository.UserRepository,
	statisticsController *controller.StatisticsController,
	callbackController *controller.CallbackController,
) *gin.Engine {
	r := gin.Default()

	// Global middleware
	r.Use(middleware.CORS())
	r.Use(middleware.Logger())
	r.Use(middleware.Recovery())

	// API routes
	api := r.Group("/api/v1")
	{
		// Public routes
		api.POST("/user/register", userController.Register)
		api.POST("/user/login", userController.Login)

		// MF178订单接口
		RegisterMF178OrderRoutes(api)

		// 可客帮订单接口 - 不需要认证
		RegisterKekebangOrderRoutes(api)

		// 外部订单接口 - 不需要认证
		RegisterExternalOrderRoutes(api)
		RegisterCallbackRoutes(api, callbackController)
		// 回调路由 - 不需要认证
		// callback := api.Group("/callback")
		// {
		// 	callback.POST("/kekebang/:userid", callbackController.HandleKekebangCallback)
		// 	callback.POST("/mishi/:userid", callbackController.HandleMishiCallback)
		// 	callback.POST("/dayuanren/:userid", callbackController.HandleDayuanrenCallback)
		// }

		// 重试路由 - 不需要认证
		retryHandler := handler.NewRetryHandler(retryService)
		retryHandler.RegisterRoutes(api)

		// Protected routes
		auth := api.Group("")
		auth.Use(middleware.Auth())
		{
			// User routes
			RegisterUserRoutes(auth, userController, userService, userLogController)

			// Permission routes
			RegisterPermissionRoutes(auth, permissionController)

			// Role routes
			RegisterRoleRoutes(auth, roleController)

			// Product routes
			RegisterProductRoutes(auth, productController, userService)

			// Phone location routes
			RegisterPhoneLocationRoutes(auth, phoneLocationController, userService)

			// Product type routes
			RegisterProductTypeRoutes(auth, productTypeController, userService)

			// Platform routes
			RegisterPlatformRoutes(auth, platformController, userService)

			// Platform API routes
			RegisterPlatformAPIRoutes(auth, platformAPIController, userService)

			// Platform API param routes
			RegisterPlatformAPIParamRoutes(auth, platformAPIParamController, userService)

			// Product API relation routes
			RegisterProductAPIRelationRoutes(auth, productAPIRelationController)

			// User grade routes
			RegisterUserGradeRoutes(auth, userGradeController)

			// Order routes
			RegisterOrderRoutes(auth)

			// Recharge routes
			recharge := auth.Group("/recharge")
			{
				recharge.POST("/callback/:platform", rechargeHandler.HandleCallback)
			}

			// 余额相关接口（仅管理员可访问）
			RegisterBalanceRoutes(auth, database.DB, userRepo, userService)

			// 平台余额查询接口（仅管理员可访问）
			RegisterPlatformBalanceRoutes(auth, userService)

			// 授信相关接口（仅管理员可访问）
			creditLogRepo := repository.NewCreditLogRepository(database.DB)
			creditService := service.NewCreditService(userRepo, creditLogRepo)
			creditController := controller.NewCreditController(creditService)
			RegisterCreditRoutes(auth, creditController)

			// 统计相关路由
			RegisterStatisticsRoutes(auth, statisticsController)

			// Task config routes
			RegisterTaskConfigRoutes(auth)

			// 只允许管理员访问
			RegisterDaichongOrderRoutes(auth)

			// 平台账号相关接口
			RegisterPlatformAccountRoutes(api)
		}
	}

	return r
}

// 注册平台账号相关接口
func RegisterPlatformAccountRoutes(r *gin.RouterGroup) {
	// 这里直接初始化 repository/service/controller，实际项目可根据依赖注入优化
	platformAccountRepo := repository.NewPlatformAccountRepository(database.DB)
	platformAccountSvc := service.NewPlatformAccountService(platformAccountRepo)
	platformAccountCtrl := controller.NewPlatformAccountController(platformAccountSvc)

	r.POST("/platform/account/bind_user", platformAccountCtrl.BindUser)
	r.GET("/platform/account/list", platformAccountCtrl.List)
}
