package main

import (
	"flag"
	"log"
	"os"
	"os/signal"
	"recharge-go/configs"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/internal/service/platform"
	"recharge-go/pkg/database"
	"syscall"
	"time"
)

func main() {
	// 解析命令行参数
	env := flag.String("env", "dev", "运行环境: dev, test, prod")
	flag.Parse()

	// 初始化配置
	if err := configs.Init(*env); err != nil {
		log.Fatalf("初始化配置失败: %v", err)
	}

	// 初始化数据库连接
	if err := database.InitDB(); err != nil {
		log.Fatalf("初始化数据库失败: %v", err)
	}

	// 获取配置
	cfg := configs.GetConfig()

	// 创建任务配置
	taskConfig := &service.TaskConfig{
		Interval:      time.Duration(cfg.Task.Interval) * time.Second,
		MaxRetries:    cfg.Task.MaxRetries,
		RetryDelay:    time.Duration(cfg.Task.RetryDelay) * time.Second,
		MaxConcurrent: cfg.Task.MaxConcurrent,
		APIKey:        cfg.API.Key,
		UserID:        cfg.API.UserID,
		BaseURL:       cfg.API.BaseURL,
	}

	// 初始化依赖
	taskConfigRepo := repository.NewTaskConfigRepository()
	taskOrderRepo := repository.NewTaskOrderRepository()
	platformSvc := platform.NewService()

	// 创建任务服务
	taskSvc := service.NewTaskService(taskConfigRepo, taskOrderRepo, platformSvc, taskConfig)

	// 启动任务
	taskSvc.StartTask()
	log.Println("任务服务已启动")

	// 等待信号
	sigChan := make(chan os.Signal, 1)
	signal.Notify(sigChan, syscall.SIGINT, syscall.SIGTERM)
	<-sigChan

	// 优雅关闭
	log.Println("正在关闭任务服务...")
	taskSvc.StopTask()
	log.Println("任务服务已关闭")
}
