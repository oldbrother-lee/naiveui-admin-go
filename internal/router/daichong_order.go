package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"

	"github.com/gin-gonic/gin"
)

// RegisterDaichongOrderRoutes 注册代充订单相关路由
func RegisterDaichongOrderRoutes(r *gin.RouterGroup) {
	repo := repository.NewDaichongOrderRepository()
	service := service.NewDaichongOrderService(repo)
	controller := controller.NewDaichongOrderController(service)

	orderGroup := r.Group("/daichong-order")
	{
		orderGroup.POST("", controller.Create)
		orderGroup.GET("/:id", controller.GetByID)
		orderGroup.PUT("", controller.Update)
		orderGroup.DELETE("/:id", controller.Delete)
		orderGroup.GET("", controller.List)
	}
}
