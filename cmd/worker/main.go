// cmd/worker/main.go - 充值工作器启动文件
package main

import (
	"os"
	"os/signal"
	"recharge-go/internal/config"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/internal/service/recharge"
	"recharge-go/internal/worker"
	"recharge-go/pkg/database"
	"recharge-go/pkg/logger"
	"recharge-go/pkg/redis"
	"syscall"

	"go.uber.org/zap"
)

func main() {
	// 加载配置
	cfg, err := config.LoadConfig("configs/config.yaml")
	if err != nil {
		logger.Log.Fatal("加载配置失败", zap.Error(err))
	}

	// 初始化数据库连接
	if err := database.InitDB(); err != nil {
		logger.Log.Fatal("初始化数据库失败", zap.Error(err))
	}

	// 初始化Redis连接
	if err := redis.InitRedis(
		cfg.Redis.Host,
		cfg.Redis.Port,
		cfg.Redis.Password,
		cfg.Redis.DB,
	); err != nil {
		logger.Log.Fatal("初始化Redis失败", zap.Error(err))
	}

	// 创建仓储实例
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	productAPIRelationRepo := repository.NewProductAPIRelationRepository(database.DB)

	// 创建充值管理器
	manager := recharge.NewManager()
	// 注册平台
	manager.RegisterPlatform("kekebang", recharge.NewKekebangPlatform())

	// 创建服务实例
	rechargeService := service.NewRechargeService(
		orderRepo,
		platformRepo,
		productAPIRelationRepo,
		manager,
	)

	// 初始化充值工作器
	rechargeWorker := worker.NewRechargeWorker(rechargeService)
	rechargeWorker.Start()
	defer rechargeWorker.Stop()

	// 等待中断信号
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
	<-quit

	// 关闭Redis连接
	if err := redis.Close(); err != nil {
		logger.Log.Error("关闭Redis连接失败", zap.Error(err))
	}

	logger.Log.Info("工作器已关闭")
}
