package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/middleware"
	"recharge-go/internal/service"

	"github.com/gin-gonic/gin"
)

func RegisterUserRoutes(r *gin.RouterGroup, userController *controller.UserController, userService *service.UserService) {
	// Protected routes
	auth := r.Group("/user")
	auth.Use(middleware.Auth())
	{
		auth.GET("/profile", userController.GetProfile)
		auth.PUT("/profile", userController.UpdateProfile)
		auth.PUT("/password", userController.ChangePassword)
		auth.GET("/list", middleware.CheckSuperAdmin(userService), userController.ListUsers)
	}
}
