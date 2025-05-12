package controller

import (
	"encoding/json"
	"fmt"
	"net/http"
	"recharge-go/internal/service"
	"recharge-go/pkg/logger"

	"github.com/gin-gonic/gin"
)

type CallbackController struct {
	rechargeService service.RechargeService
}

func NewCallbackController(rechargeService service.RechargeService) *CallbackController {
	return &CallbackController{
		rechargeService: rechargeService,
	}
}

// KekebangCallbackRequest 客帮帮回调请求结构
type KekebangCallbackRequest struct {
	OrderID    string `json:"order_id"`    // 平台订单号
	TerraceID  string `json:"terrace_id"`  // 没用
	Account    string `json:"account"`     // 充值账号
	Time       string `json:"time"`        // 回调时间
	Amount     string `json:"amount"`      // 充值金额
	OrderState string `json:"order_state"` // 订单状态
	Sign       string `json:"sign"`        // 签名
}

// HandleKekebangCallback 处理客帮帮回调
func (c *CallbackController) HandleKekebangCallback(ctx *gin.Context) {
	// 读取请求体
	data, err := ctx.GetRawData()
	if err != nil {
		logger.Error("读取回调请求体失败: %v", err)
		ctx.JSON(400, gin.H{
			"code": "1001",
			"msg":  "invalid request body",
		})
		return
	}

	// 解析回调数据为map
	var params map[string]interface{}
	if err := json.Unmarshal(data, &params); err != nil {
		logger.Error("解析回调数据失败: %v", err)
		ctx.JSON(400, gin.H{
			"code": "1001",
			"msg":  "invalid request body",
		})
		return
	}

	// 获取平台API信息
	orderID, ok := params["order_id"].(string)
	if !ok {
		logger.Error("order_id 类型错误")
		ctx.JSON(400, gin.H{
			"code": "1001",
			"msg":  "invalid order_id",
		})
		return
	}

	api, _, err := c.rechargeService.GetPlatformAPIByOrderID(ctx.Request.Context(), orderID)
	if err != nil {
		logger.Error("获取平台API信息失败: %v", err)
		ctx.JSON(http.StatusOK, gin.H{
			"code": "1002",
			"msg":  "get platform api failed",
		})
		return
	}

	// 获取签名
	sign, ok := params["sign"].(string)
	if !ok {
		logger.Error("sign 类型错误")
		ctx.JSON(400, gin.H{
			"code": "1001",
			"msg":  "invalid sign",
		})
		return
	}
	params["app_key"] = api.AppKey
	fmt.Println(sign, "params")
	// 使用客帮帮的签名验证方法
	// kekebangSign := signature.GenerateKekebangSign(params, api.SecretKey)
	// if kekebangSign != sign {
	// 	logger.Error("签名验证失败: order_id=%s", orderID)
	// 	ctx.JSON(400, gin.H{
	// 		"code": "1001",
	// 		"msg":  "invalid sign",
	// 	})
	// 	return
	// }

	// 处理回调
	if err := c.rechargeService.HandleCallback(ctx.Request.Context(), "kekebang", data); err != nil {
		logger.Error("处理客帮帮回调失败1: %v", err)
		ctx.JSON(200, gin.H{
			"code": "1002",
			"msg":  "handle callback failed",
		})
		return
	}

	// 返回成功响应
	ctx.JSON(200, gin.H{
		"code": "0000",
		"msg":  "success",
	})
}
