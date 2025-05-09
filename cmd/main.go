package main

import (
	"fmt"
	"log"
	"recharge-go/internal/config"
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/router"
	"recharge-go/internal/service"
	"recharge-go/pkg/database"
	"recharge-go/pkg/logger"
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
	// 加载配置
	_, err := config.LoadConfig("configs/config.yaml")
	if err != nil {
		log.Fatalf("Failed to load config: %v", err)
	}

	// 初始化日志
	if err := logger.InitLogger(); err != nil {
		// 如果初始化失败，使用默认的 logger
		fmt.Printf("初始化日志失败: %v，将使用默认日志配置\n", err)
	}
	defer logger.Close()

	// 初始化数据库
	if err := database.InitDB(); err != nil {
		log.Fatalf("Failed to initialize database: %v", err)
	}

	// 初始化仓库
	userRepo := repository.NewUserRepository(database.DB)
	permissionRepo := repository.NewPermissionRepository(database.DB)
	roleRepo := repository.NewRoleRepository(database.DB)
	productRepo := repository.NewProductRepository(database.DB)
	phoneLocationRepo := repository.NewPhoneLocationRepository(database.DB)
	productTypeRepo := repository.NewProductTypeRepository(database.DB)
	productTypeCateRepo := repository.NewProductTypeCategoryRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	platformAPIRepo := repository.NewPlatformAPIRepository(database.DB)
	platformAPIParamRepo := repository.NewPlatformAPIParamRepository(database.DB)
	productAPIRelationRepo := repository.NewProductAPIRelationRepository(database.DB)
	userLogRepo := repository.NewUserLogRepository(database.DB)
	userGradeRepo := repository.NewUserGradeRepository(database.DB)
	userGradeRelationRepo := repository.NewUserGradeRelationRepository(database.DB)
	userTagRepo := repository.NewUserTagRepository(database.DB)
	userTagRelationRepo := repository.NewUserTagRelationRepository(database.DB)

	// 初始化服务
	userService := service.NewUserService(userRepo, userGradeRepo, userTagRepo, userTagRelationRepo, userGradeRelationRepo, userLogRepo)
	permissionService := service.NewPermissionService(permissionRepo)
	roleService := service.NewRoleService(roleRepo)
	productService := service.NewProductService(productRepo)
	phoneLocationService := service.NewPhoneLocationService(phoneLocationRepo)
	productTypeService := service.NewProductTypeService(productTypeRepo, productTypeCateRepo)
	platformService := service.NewPlatformService(platformRepo)
	platformAPIService := service.NewPlatformAPIService(platformAPIRepo)
	platformAPIParamService := service.NewPlatformAPIParamService(platformAPIParamRepo)
	productAPIRelationService := service.NewProductAPIRelationService(productAPIRelationRepo)
	userLogService := service.NewUserLogService(userLogRepo)
	userGradeService := service.NewUserGradeService(userGradeRepo, userGradeRelationRepo)
	userTagService := service.NewUserTagService(userTagRepo, userTagRelationRepo)

	// 初始化控制器
	userController := controller.NewUserController(userService, userGradeService, userTagService)
	permissionController := controller.NewPermissionController(permissionService)
	roleController := controller.NewRoleController(roleService)
	productController := controller.NewProductController(productService)
	phoneLocationController := controller.NewPhoneLocationController(phoneLocationService)
	productTypeController := controller.NewProductTypeController(productTypeService)
	platformController := controller.NewPlatformController(platformService)
	platformAPIController := controller.NewPlatformAPIController(platformAPIService, platformService)
	platformAPIParamController := controller.NewPlatformAPIParamController(platformAPIParamService)
	productAPIRelationController := controller.NewProductAPIRelationController(productAPIRelationService)
	userLogController := controller.NewUserLogController(userLogService)
	userGradeController := controller.NewUserGradeController(userGradeService)

	// 设置路由
	r := router.SetupRouter(
		userController,
		permissionController,
		roleController,
		productController,
		userService,
		phoneLocationController,
		productTypeController,
		platformController,
		platformAPIController,
		platformAPIParamController,
		productAPIRelationController,
		userLogController,
		userGradeController,
	)

	// 启动服务器
	if err := r.Run(":8080"); err != nil {
		log.Fatalf("Failed to start server: %v", err)
	}
}
