package controller

import (
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/pkg/signature"

	"github.com/gin-gonic/gin"
)

type CallbackController struct {
	rechargeService service.RechargeService
	platformRepo    repository.PlatformRepository
}

func NewCallbackController(rechargeService service.RechargeService, platformRepo repository.PlatformRepository) *CallbackController {
	return &CallbackController{
		rechargeService: rechargeService,
		platformRepo:    platformRepo,
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
	// 从URL中获取userid
	userID := ctx.Param("userid")
	if userID == "" {
		ctx.JSON(http.StatusBadRequest, gin.H{"code": 400, "message": "missing userid"})
		return
	}

	// 获取账号信息
	account, err := c.platformRepo.GetPlatformAccountByAccountName(userID)
	if err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"code": 500, "message": "failed to get account info"})
		return
	}

	// 读取请求体
	body, err := io.ReadAll(ctx.Request.Body)
	if err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"code": 400, "message": "failed to read request body"})
		return
	}

	// 解析请求体
	var data map[string]interface{}
	fmt.Printf("kekebang【解析请求体】body: %+v\n", body)
	if err := json.Unmarshal(body, &data); err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"code": 400, "message": "invalid request body"})
		return
	}
	fmt.Printf("kekebang【解析请求体】jsonbody: %+v\n", body)
	// 获取签名
	sign, ok := data["sign"].(string)
	if !ok {
		ctx.JSON(http.StatusBadRequest, gin.H{
			"code": "1001",
			"msg":  "invalid sign",
		})
		return
	}

	// 使用账号的AppSecret验证签名
	if !verifySignature(body, sign, account.AppSecret) {
		ctx.JSON(http.StatusBadRequest, gin.H{
			"code": "1001",
			"msg":  "invalid sign",
		})
		return
	}

	// 处理回调
	if err := c.rechargeService.HandleCallback(ctx, "kekebang", body); err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"code": 500, "message": "failed to process callback"})
		return
	}

	ctx.JSON(http.StatusOK, gin.H{"code": 200, "message": "success"})
}

// verifySignature 验证签名
func verifySignature(body []byte, sign string, secretKey string) bool {
	var data map[string]interface{}
	if err := json.Unmarshal(body, &data); err != nil {
		return false
	}
	return signature.VerifyKekebangSign(data, sign, secretKey)
}
