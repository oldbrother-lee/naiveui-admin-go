package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/handler"
	"recharge-go/internal/middleware"
	"recharge-go/internal/service"

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

		// 外部订单接口 - 不需要认证
		RegisterExternalOrderRoutes(api)

		// 回调路由 - 不需要认证
		RegisterCallbackRoutes(api)

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
		}
	}

	return r
}
