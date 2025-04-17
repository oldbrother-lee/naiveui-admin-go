package main

import (
	"log"
	"recharge-go/internal/config"
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/router"
	"recharge-go/internal/service"
	"recharge-go/pkg/database"
)

func main() {
	// Load configuration
	_, err := config.LoadConfig("configs/config.yaml")
	if err != nil {
		log.Fatalf("Failed to load config: %v", err)
	}

	// Initialize database
	if err := database.InitDB(); err != nil {
		log.Fatalf("Failed to initialize database: %v", err)
	}

	// Initialize repositories
	userRepo := repository.NewUserRepository(database.DB)
	permissionRepo := repository.NewPermissionRepository(database.DB)
	roleRepo := repository.NewRoleRepository(database.DB)

	// Initialize services
	userService := service.NewUserService(userRepo)
	permissionService := service.NewPermissionService(permissionRepo)
	roleService := service.NewRoleService(roleRepo)

	// Initialize controllers
	userController := controller.NewUserController(userService)
	permissionController := controller.NewPermissionController(permissionService)
	roleController := controller.NewRoleController(roleService)

	// Setup router
	engine := router.SetupRouter(userController, permissionController, roleController, userService)

	// Start server
	if err := engine.Run(":8080"); err != nil {
		log.Fatalf("Failed to start server: %v", err)
	}
}
