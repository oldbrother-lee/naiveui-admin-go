package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/middleware"
	"recharge-go/internal/service"

	"github.com/gin-gonic/gin"
)

func RegisterPlatformRoutes(r *gin.RouterGroup, platformController *controller.PlatformController, userService *service.UserService) {
	platforms := r.Group("/platforms")
	{
		// 需要管理员权限的路由
		admin := platforms.Group("")
		admin.Use(middleware.CheckSuperAdmin(userService))
		{
			admin.GET("", platformController.ListPlatforms)
			admin.POST("", platformController.CreatePlatform)
			admin.PUT("/:id", platformController.UpdatePlatform)
			admin.DELETE("/:id", platformController.DeletePlatform)
			admin.GET("/accounts", platformController.ListPlatformAccounts)
			admin.POST("/accounts", platformController.CreatePlatformAccount)
			admin.PUT("/accounts/:id", platformController.UpdatePlatformAccount)
			admin.DELETE("/accounts/:id", platformController.DeletePlatformAccount)
		}

		// 公共路由
		platforms.GET("/:id", platformController.GetPlatform)
		platforms.GET("/accounts/:id", platformController.GetPlatformAccount)
	}
}
