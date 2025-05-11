package router

import (
	"io"
	"net/http"
	"recharge-go/internal/controller"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/internal/service/recharge"
	"recharge-go/pkg/database"
	"recharge-go/pkg/logger"

	"github.com/gin-gonic/gin"
)

// RegisterCallbackRoutes 注册回调相关路由
func RegisterCallbackRoutes(r *gin.RouterGroup) {
	// 初始化仓库
	orderRepo := repository.NewOrderRepository(database.DB)
	platformRepo := repository.NewPlatformRepository(database.DB)
	productAPIRelationRepo := repository.NewProductAPIRelationRepository(database.DB)

	// 初始化服务
	rechargeService := service.NewRechargeService(
		orderRepo,
		platformRepo,
		productAPIRelationRepo,
		recharge.NewManager(),
	)

	// 初始化控制器
	callbackController := controller.NewCallbackController(rechargeService)

	// 回调路由组
	callbackGroup := r.Group("/callback")
	{
		callbackGroup.POST("/kekebang", callbackController.HandleKekebangCallback)
	}
}

func InitCallbackRouter(r *gin.Engine, rechargeService service.RechargeService) {
	callback := r.Group("/api/callback")
	{
		// 客帮帮回调接口
		callback.POST("/kekebang", func(c *gin.Context) {
			// 读取请求体
			data, err := io.ReadAll(c.Request.Body)
			if err != nil {
				c.JSON(http.StatusBadRequest, gin.H{
					"code": "1001",
					"msg":  "invalid request body",
				})
				return
			}

			// 处理回调
			if err := rechargeService.HandleCallback(c.Request.Context(), "kekebang", data); err != nil {
				logger.Error("处理客帮帮回调失败: %v", err)
				c.JSON(http.StatusOK, gin.H{
					"code": "1002",
					"msg":  "handle callback failed",
				})
				return
			}

			// 返回成功响应
			c.JSON(http.StatusOK, gin.H{
				"code": "0000",
				"msg":  "success",
			})
		})
	}
}
