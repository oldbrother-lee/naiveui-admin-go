package router

import (
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/pkg/database"

	"github.com/gin-gonic/gin"
)

func RegisterExternalOrderRoutes(r *gin.RouterGroup) {
	orderRepo := repository.NewOrderRepository(database.DB)
	orderService := service.NewOrderService(orderRepo)
	externalOrderController := controller.NewExternalOrderController(orderService)

	externalOrderGroup := r.Group("/external/order")
	{
		externalOrderGroup.POST("/create", externalOrderController.CreateOrder)
		// 可扩展：externalOrderGroup.GET("/:out_trade_num", externalOrderController.GetOrder)
		// 可扩展：externalOrderGroup.POST("/notify", externalOrderController.Notify)
	}
}
