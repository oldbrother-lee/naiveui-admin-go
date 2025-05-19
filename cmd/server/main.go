package main

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/router"
	"recharge-go/internal/service"
	"recharge-go/internal/service/platform"
	"recharge-go/pkg/database"
	"recharge-go/pkg/logger"

	"github.com/gin-gonic/gin"
)

func main() {
	// 初始化数据库
	if err := database.Init(); err != nil {
		logger.Fatal("Failed to initialize database", err)
	}

	// 初始化服务
	userService := service.NewUserService()
	platformService := service.NewPlatformService()
	platformSvc := platform.NewService()

	// 初始化控制器
	platformController := controller.NewPlatformController(platformService, platformSvc)

	// 初始化路由
	r := gin.Default()
	api := r.Group("/api")
	router.RegisterPlatformRoutes(api, platformController, userService)

	// 启动服务器
	if err := r.Run(":8080"); err != nil {
		logger.Fatal("Failed to start server", err)
	}
}
