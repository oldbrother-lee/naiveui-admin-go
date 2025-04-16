package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/middleware"

	"github.com/gin-gonic/gin"
)

func RegisterUserRoutes(r *gin.RouterGroup, userController *controller.UserController) {
	// Public routes
	user := r.Group("/user")
	{
		user.POST("/register", userController.Register)
		user.POST("/login", userController.Login)
	}

	// Protected routes
	auth := r.Group("/user")
	auth.Use(middleware.Auth())
	{
		auth.GET("/profile", userController.GetProfile)
		auth.PUT("/profile", userController.UpdateProfile)
		auth.PUT("/password", userController.ChangePassword)
		auth.GET("/list", userController.ListUsers)
	}
}
