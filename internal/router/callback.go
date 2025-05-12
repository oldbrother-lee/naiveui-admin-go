package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/internal/service/recharge"
	"recharge-go/pkg/database"

	"github.com/gin-gonic/gin"
)

// RegisterCallbackRoutes 注册回调相关路由
func RegisterCallbackRoutes(r *gin.RouterGroup) {
	// 创建服务实例
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	callbackLogRepo := repository.NewCallbackLogRepository(database.DB)
	manager := recharge.NewManager(database.DB)
	rechargeService := service.NewRechargeService(orderRepo, platformRepo, manager, callbackLogRepo, database.DB)

	// 创建控制器
	callbackController := controller.NewCallbackController(rechargeService)

	// 注册路由
	callback := r.Group("/callback")
	{
		callback.POST("/kekebang", callbackController.HandleKekebangCallback)
	}
}
