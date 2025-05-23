package controller

import (
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/internal/service"
	"recharge-go/internal/utils"
	"recharge-go/pkg/logger"
	"recharge-go/pkg/signature"

	"github.com/gin-gonic/gin"
	"go.uber.org/zap"
)

type CallbackController struct {
	rechargeService service.RechargeService
	platformRepo    repository.PlatformRepository
	orderRepo       repository.OrderRepository
}

func NewCallbackController(rechargeService service.RechargeService, platformRepo repository.PlatformRepository, orderRepo repository.OrderRepository) *CallbackController {
	return &CallbackController{
		rechargeService: rechargeService,
		platformRepo:    platformRepo,
		orderRepo:       orderRepo,
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

// MishiCallbackRequest 秘史平台回调参数
type MishiCallbackRequest struct {
	SzAgentId      string  `form:"szAgentId" json:"szAgentId"`
	SzOrderId      string  `form:"szOrderId" json:"szOrderId"`
	SzPhoneNum     string  `form:"szPhoneNum" json:"szPhoneNum"`
	NDemo          float64 `form:"nDemo" json:"nDemo"`
	FSalePrice     float64 `form:"fSalePrice" json:"fSalePrice"`
	NFlag          int     `form:"nFlag" json:"nFlag"`
	SzRtnMsg       string  `form:"szRtnMsg" json:"szRtnMsg"`
	SzVerifyString string  `form:"szVerifyString" json:"szVerifyString"`
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

// HandleMishiCallback 处理秘史平台回调
func (c *CallbackController) HandleMishiCallback(ctx *gin.Context) {
	fmt.Printf("[mishi] 处理秘史平台回调!!!\n")
	// 1. 获取userid
	userIDStr := ctx.Param("userid")
	if userIDStr == "" {
		utils.ErrorWithStatus(ctx, 500, 400, "缺少userid")
		return
	}

	//获取 appkey 等信息
	account, err := c.platformRepo.GetPlatformAccountByAccountName(userIDStr)
	if err != nil {
		logger.Error("返回：401 平台账号不存在", zap.Error(err))
		utils.ErrorWithStatus(ctx, 401, 400, "平台账号不存在")
		return
	}
	// fmt.Printf("[mishi] 获取平台账号信息:  %+v\n", account)

	// 2. 解析回调参数
	var req MishiCallbackRequest
	if err := ctx.ShouldBind(&req); err != nil {
		logger.Error("参数解析失败", zap.Error(err))
		utils.ErrorWithStatus(ctx, 500, 400, "参数解析失败")
		return
	}

	// 4. 签名校验
	signStr := fmt.Sprintf(
		"szAgentId=%s&szOrderId=%s&szPhoneNum=%s&nDemo=%v&fSalePrice=%.2f&nFlag=%d&szKey=%s",
		req.SzAgentId,
		req.SzOrderId,
		req.SzPhoneNum,
		req.NDemo,      // 如果 nDemo 是 int，%v 没问题；如果是 float，建议 %.0f
		req.FSalePrice, // 保留两位小数
		req.NFlag,
		account.AppSecret,
	)

	fmt.Printf("[mishi] 请求的参数: %+v\n", req)
	fmt.Printf("[mishi] 签名校验: %s\n", signature.GetMD5(signStr))
	if signature.GetMD5(signStr) != req.SzVerifyString {
		logger.Error("返回：401 签名校验失败")
		utils.ErrorWithStatus(ctx, 401, 401, "签名校验失败")
		return
	}

	// 5. 查询订单
	order, err := c.orderRepo.GetByOrderNumber(ctx, req.SzOrderId)
	if err != nil {
		utils.Error(ctx, 404, "订单不存在")
		return
	}

	// 6. 幂等性处理
	if order.Status == model.OrderStatusSuccess || order.Status == model.OrderStatusFailed {
		utils.Success(ctx, "订单状态已更新，请勿重复回调")
		return
	}

	// 7. 更新订单状态
	var newStatus model.OrderStatus
	switch req.NFlag {
	case 2:
		newStatus = model.OrderStatusSuccess
	case 3:
		newStatus = model.OrderStatusFailed
	default:
		newStatus = model.OrderStatusProcessing
	}
	if err := c.orderRepo.UpdateStatus(ctx, order.ID, newStatus); err != nil {
		utils.ErrorWithStatus(ctx, 500, 500, "订单状态更新失败")
		return
	}

	// 8. 记录日志
	logger.Info("秘史回调处理完成",
		"order_id", order.ID,
		"order_number", order.OrderNumber,
		"status", newStatus,
		"userid", userIDStr,
	)

	utils.Success(ctx, "success")
}
