package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/middleware"

	"github.com/gin-gonic/gin"
)

func SetupRouter(userController *controller.UserController, permissionController *controller.PermissionController, roleController *controller.RoleController) *gin.Engine {
	r := gin.Default()

	// Global middleware
	r.Use(middleware.CORS())
	r.Use(middleware.Logger())

	// API routes
	api := r.Group("/api/v1")
	{
		// Public routes
		api.POST("/user/register", userController.Register)
		api.POST("/user/login", userController.Login)

		// Protected routes
		auth := api.Group("")
		auth.Use(middleware.Auth())
		{
			// User routes
			auth.GET("/user/profile", userController.GetProfile)
			auth.PUT("/user/profile", userController.UpdateProfile)
			auth.PUT("/user/password", userController.ChangePassword)

			// Permission routes
			RegisterPermissionRoutes(auth, permissionController)

			// Role routes
			RegisterRoleRoutes(auth, roleController)
		}
	}

	return r
}
