package main

import (
	"fmt"
	"os"
	"os/signal"
	"recharge-go/internal/config"
	"recharge-go/internal/controller"
	"recharge-go/internal/handler"
	"recharge-go/internal/repository"
	"recharge-go/internal/router"
	"recharge-go/internal/service"
	"recharge-go/internal/service/recharge"
	"recharge-go/pkg/database"
	"recharge-go/pkg/logger"
	"recharge-go/pkg/queue"
	"recharge-go/pkg/redis"
	"syscall"

	"go.uber.org/zap"
)

// @title Recharge Go API
// @version 1.0
// @description This is a recharge system API server.
// @termsOfService http://swagger.io/terms/

// @contact.name API Support
// @contact.url http://www.swagger.io/support
// @contact.email support@swagger.io

// @license.name Apache 2.0
// @license.url http://www.apache.org/licenses/LICENSE-2.0.html

// @host localhost:8080
// @BasePath /api/v1
// @securityDefinitions.apikey ApiKeyAuth
// @in header
// @name Authorization
func main() {
	// 初始化日志
	if err := logger.InitLogger(); err != nil {
		panic(fmt.Sprintf("初始化日志失败: %v", err))
	}

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
	platformRepo := repository.NewPlatformRepository(database.DB)
	orderRepo := repository.NewOrderRepository(database.DB)
	productAPIRelationRepo := repository.NewProductAPIRelationRepository(database.DB)
	userRepo := repository.NewUserRepository(database.DB)
	userGradeRepo := repository.NewUserGradeRepository(database.DB)
	userTagRepo := repository.NewUserTagRepository(database.DB)
	userTagRelationRepo := repository.NewUserTagRelationRepository(database.DB)
	userGradeRelationRepo := repository.NewUserGradeRelationRepository(database.DB)
	userLogRepo := repository.NewUserLogRepository(database.DB)
	permissionRepo := repository.NewPermissionRepository(database.DB)
	roleRepo := repository.NewRoleRepository(database.DB)
	productRepo := repository.NewProductRepository(database.DB)
	phoneLocationRepo := repository.NewPhoneLocationRepository(database.DB)
	productTypeRepo := repository.NewProductTypeRepository(database.DB)
	productTypeCategoryRepo := repository.NewProductTypeCategoryRepository(database.DB)
	platformAPIRepo := repository.NewPlatformAPIRepository(database.DB)
	platformAPIParamRepo := repository.NewPlatformAPIParamRepository(database.DB)
	callbackLogRepo := repository.NewCallbackLogRepository(database.DB)

	// 创建平台管理器
	manager := recharge.NewManager(database.DB)

	// 从数据库加载平台配置
	if err := manager.LoadPlatforms(); err != nil {
		logger.Error("load platforms failed: %v", err)
		os.Exit(1)
	}

	// 创建队列实例
	queueInstance := queue.NewRedisQueue()

	// 创建通知仓储
	notificationRepo := repository.NewNotificationRepository(database.DB)

	// 创建订单服务
	orderService := service.NewOrderService(
		orderRepo,
		nil, // 先传入 nil，后面再设置
		notificationRepo,
		queueInstance,
	)

	// 创建平台API参数服务
	platformAPIParamService := service.NewPlatformAPIParamService(platformAPIParamRepo)

	// 创建充值服务
	rechargeService := service.NewRechargeService(
		orderRepo,
		platformRepo,
		manager,
		callbackLogRepo,
		database.DB,
		orderService,
		productAPIRelationRepo,
		platformAPIParamService,
		repository.NewRetryRepository(database.DB),
	)

	// 设置 orderService 的 rechargeService
	orderService.SetRechargeService(rechargeService)

	// 创建用户服务
	userService := service.NewUserService(
		userRepo,
		userGradeRepo,
		userTagRepo,
		userTagRelationRepo,
		userGradeRelationRepo,
		userLogRepo,
	)
	userGradeService := service.NewUserGradeService(userGradeRepo, userGradeRelationRepo)
	userTagService := service.NewUserTagService(userTagRepo, userTagRelationRepo)
	userLogService := service.NewUserLogService(userLogRepo)
	permissionService := service.NewPermissionService(permissionRepo)
	roleService := service.NewRoleService(roleRepo)
	productService := service.NewProductService(productRepo)
	phoneLocationService := service.NewPhoneLocationService(phoneLocationRepo)
	productTypeService := service.NewProductTypeService(productTypeRepo, productTypeCategoryRepo)
	platformService := service.NewPlatformService(platformRepo, orderRepo)
	platformAPIService := service.NewPlatformAPIService(platformAPIRepo)
	productAPIRelationService := service.NewProductAPIRelationService(productAPIRelationRepo)

	// 创建重试服务
	retryService := service.NewRetryService(
		repository.NewRetryRepository(database.DB),
		repository.NewOrderRepository(database.DB),
		repository.NewPlatformRepository(database.DB),
		repository.NewProductRepository(database.DB),
		productAPIRelationRepo,
		rechargeService,
	)

	// 创建处理器实例
	rechargeHandler := handler.NewRechargeHandler(rechargeService)
	userController := controller.NewUserController(userService, userGradeService, userTagService)
	userLogController := controller.NewUserLogController(userLogService)
	permissionController := controller.NewPermissionController(permissionService)
	roleController := controller.NewRoleController(roleService)
	productController := controller.NewProductController(productService)
	phoneLocationController := controller.NewPhoneLocationController(phoneLocationService)
	productTypeController := controller.NewProductTypeController(productTypeService)
	platformController := controller.NewPlatformController(platformService)
	platformAPIController := controller.NewPlatformAPIController(platformAPIService, platformService)
	platformAPIParamController := controller.NewPlatformAPIParamController(platformAPIParamService)
	productAPIRelationController := controller.NewProductAPIRelationController(productAPIRelationService)
	userGradeController := controller.NewUserGradeController(userGradeService)

	// 注册路由
	engine := router.SetupRouter(
		userController,               // userController
		permissionController,         // permissionController
		roleController,               // roleController
		productController,            // productController
		userService,                  // userService
		phoneLocationController,      // phoneLocationController
		productTypeController,        // productTypeController
		platformController,           // platformController
		platformAPIController,        // platformAPIController
		platformAPIParamController,   // platformAPIParamController
		productAPIRelationController, // productAPIRelationController
		userLogController,            // userLogController
		userGradeController,          // userGradeController
		rechargeHandler,
		retryService, // retryService
		userRepo,     // 新增，确保参数数量和类型一致
	)

	// 启动HTTP服务器
	go func() {
		addr := fmt.Sprintf(":%d", cfg.Server.Port)
		logger.Log.Info("HTTP服务器启动", zap.String("addr", addr))
		if err := engine.Run(addr); err != nil {
			logger.Log.Error("HTTP服务器启动失败", zap.Error(err))
		}
	}()

	// 等待中断信号
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
	<-quit

	// 关闭Redis连接
	if err := redis.Close(); err != nil {
		logger.Log.Error("关闭Redis连接失败", zap.Error(err))
	}

	logger.Log.Info("服务已关闭")
}
