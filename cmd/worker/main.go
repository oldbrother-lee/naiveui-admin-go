// cmd/worker/main.go - 充值工作器启动文件
package main

import (
	"context"
	"os"
	"os/signal"
	"recharge-go/internal/config"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/pkg/database"
	"recharge-go/pkg/logger"
	"syscall"
	"time"

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
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	productAPIRelationRepo := repository.NewProductAPIRelationRepository(database.DB)

	// 初始化充值服务
	rechargeService := service.NewRechargeService(
		orderRepo,
		platformRepo,
		productAPIRelationRepo,
	)

	// 创建充值工作器
	worker := service.NewRechargeWorker(
		rechargeService,
		5*time.Second, // 每5秒检查一次
		10,            // 每次处理10个任务
	)

	// 创建上下文，用于优雅退出
	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	// 启动工作器
	go worker.Start(ctx)

	// 等待中断信号
	sigChan := make(chan os.Signal, 1)
	signal.Notify(sigChan, syscall.SIGINT, syscall.SIGTERM)
	<-sigChan

	// 收到信号后，取消上下文
	cancel()

	// 等待工作器停止
	time.Sleep(time.Second)
}
