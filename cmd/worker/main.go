// cmd/worker/main.go - 充值工作器启动文件
package main

import (
	"recharge-go/internal/config"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/pkg/database"
	"recharge-go/pkg/logger"

	"go.uber.org/zap"
)

func main() {
	// 初始化日志
	if err := logger.InitLogger(); err != nil {
		panic(err)
	}
	defer logger.Close()

	// 初始化配置
	_, err := config.LoadConfig("configs/config.yaml")
	if err != nil {
		logger.Log.Fatal("加载配置失败", zap.Error(err))

	}
	// 初始化数据库
	if err := database.InitDB(); err != nil {
		logger.Log.Fatal("初始化数据库失败", zap.Error(err))
	}

	// 初始化仓库
	taskRepo := repository.NewRechargeTaskRepository(database.DB)
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)

	// 初始化平台服务
	platformService := service.NewPlatformService(platformRepo)

	// 初始化充值服务
	rechargeService := service.NewRechargeService(
		taskRepo,
		orderRepo,
		platformService,
	)

	// 启动 worker
	worker := service.NewRechargeWorker(rechargeService)
	worker.Start()
}
