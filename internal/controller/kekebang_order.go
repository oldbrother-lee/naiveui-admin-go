package controller

import (
	"encoding/json"
	"fmt"
	"recharge-go/internal/model"
	"recharge-go/internal/service"
	"recharge-go/internal/utils"
	"recharge-go/pkg/logger"
	"recharge-go/pkg/signature"
	"recharge-go/pkg/utils/response"
	"strconv"
	"time"

	"github.com/gin-gonic/gin"
)

// KekebangOrderController 可客帮订单控制器
type KekebangOrderController struct {
	orderService    service.OrderService
	rechargeService service.RechargeService
}

// NewKekebangOrderController 创建可客帮订单控制器
func NewKekebangOrderController(orderService service.OrderService, rechargeService service.RechargeService) *KekebangOrderController {
	return &KekebangOrderController{
		orderService:    orderService,
		rechargeService: rechargeService,
	}
}

// CreateOrder 创建订单
func (c *KekebangOrderController) CreateOrder(ctx *gin.Context) {
	var req model.KekebangOrderRequest
	if err := ctx.ShouldBindJSON(&req); err != nil {
		logger.Error("【解析请求参数失败】error: %v", err)
		utils.Error(ctx, 400, "解析请求参数失败")
		return
	}

	// 记录原始请求数据
	logger.Info("【收到可客帮订单请求】request: %+v", req)

	// 验证签名
	// if !c.verifySign(req) {
	// 	logger.Error("【签名验证失败】request: %+v", req)
	// 	response.Error(ctx, 400, "签名验证失败")
	// 	return
	// }

	// 创建订单
	order := &model.Order{
		Mobile:         req.Target,
		TotalPrice:     req.Datas.Amount,
		ProductID:      1, // 需要根据 OuterGoodsCode 查询对应的商品ID
		Status:         model.OrderStatusPendingRecharge,
		PlatformCode:   "kekebang",
		PlatformId:     req.VenderID,
		OutTradeNum:    strconv.FormatInt(req.UserOrderID, 10),     // 外部交易号
		UserOrderId:    strconv.FormatInt(req.UserOrderID, 10),     // 用户订单ID
		APIOrderNumber: strconv.FormatInt(req.UserOrderID, 10),     // API订单号
		ISP:            getISPFromOperatorID(req.Datas.OperatorID), // 根据运营商ID获取ISP
		Param1:         req.Datas.ProvCode,                         // 省份代码
		Param2:         req.GoodsName,                              // 商品名称
		Param3:         req.OuterGoodsCode,                         // 外部商品编码
		Remark:         fmt.Sprintf("可客帮订单，商品ID：%s", req.GoodsID),
	}

	// 调用订单服务创建订单
	if err := c.orderService.CreateOrder(ctx, order); err != nil {
		logger.Error("【创建订单失败】error: %v", err)
		utils.Error(ctx, 500, "创建订单失败")
		return
	}

	// 创建充值任务
	if err := c.rechargeService.CreateRechargeTask(ctx, order.ID); err != nil {
		logger.Error("【创建充值任务失败】error: %v", err)
		utils.Error(ctx, 500, "创建充值任务失败")
		return
	}

	response.Success(ctx, gin.H{
		"order_id": order.ID,
		"status":   "success",
	})
}

// verifySign 验证签名
func (c *KekebangOrderController) verifySign(req model.KekebangOrderRequest) bool {
	// 将请求参数转换为 map
	jsonData, err := json.Marshal(req)
	if err != nil {
		logger.Error("【序列化请求参数失败】error: %v", err)
		return false
	}

	var params map[string]interface{}
	if err := json.Unmarshal(jsonData, &params); err != nil {
		logger.Error("【反序列化请求参数失败】error: %v", err)
		return false
	}

	// TODO: 从配置或数据库获取 secretKey
	secretKey := "ab4e90e8bd504a5a8d290b5d4b8235c9"

	// 验证签名
	return signature.VerifyKekebangSign(params, req.Sign, secretKey)
}

// getISPFromOperatorID 根据运营商ID获取ISP
func getISPFromOperatorID(operatorID string) int {
	switch operatorID {
	case "1":
		return 1 // 移动
	case "2":
		return 2 // 联通
	case "3":
		return 3 // 电信
	default:
		return 0 // 未知
	}
}

// QueryOrder 查询订单
func (c *KekebangOrderController) QueryOrder(ctx *gin.Context) {
	var req struct {
		AppKey      string `json:"app_key" binding:"required"`       // 应用密钥
		UserOrderID int64  `json:"user_order_id" binding:"required"` // 用户订单ID
		Sign        string `json:"sign" binding:"required"`          // 签名
		Timestamp   int64  `json:"timestamp" binding:"required"`     // 时间戳
	}

	if err := ctx.ShouldBindJSON(&req); err != nil {
		logger.Error("【解析请求参数失败】error: %v", err)
		response.Error(ctx, 400, "解析请求参数失败")
		return
	}

	// 记录原始请求数据
	logger.Info("【收到可客帮订单查询请求】request: %+v", req)

	// 验证签名
	params := map[string]interface{}{
		"app_key":       req.AppKey,
		"user_order_id": req.UserOrderID,
		"timestamp":     req.Timestamp,
	}

	// TODO: 从配置或数据库获取 secretKey
	secretKey := "your_secret_key"

	if !signature.VerifyKekebangSign(params, req.Sign, secretKey) {
		logger.Error("【签名验证失败】request: %+v", req)
		utils.Error(ctx, 400, "签名验证失败")
		return
	}

	// 查询订单
	order, err := c.orderService.GetOrderByOutTradeNum(ctx, strconv.FormatInt(req.UserOrderID, 10))
	if err != nil {
		logger.Error("【查询订单失败】error: %v", err)
		utils.Error(ctx, 500, "查询订单失败")
		return
	}

	// 转换订单状态为可客帮状态
	status := convertOrderStatus(order.Status)

	response.Success(ctx, gin.H{
		"order_id":     order.ID,
		"order_number": order.OrderNumber,
		"status":       status,
		"amount":       order.TotalPrice,
		"mobile":       order.Mobile,
		"create_time":  order.CreateTime.Unix(),
		"finish_time":  getFinishTime(order.FinishTime),
	})
}

// convertOrderStatus 转换订单状态为可客帮状态
func convertOrderStatus(status model.OrderStatus) string {
	switch status {
	case model.OrderStatusPendingPayment:
		return "pending"
	case model.OrderStatusPendingRecharge:
		return "pending"
	case model.OrderStatusRecharging:
		return "processing"
	case model.OrderStatusSuccess:
		return "success"
	case model.OrderStatusFailed:
		return "failed"
	case model.OrderStatusRefunded:
		return "refunded"
	case model.OrderStatusCancelled:
		return "cancelled"
	case model.OrderStatusPartial:
		return "partial"
	case model.OrderStatusSplit:
		return "split"
	case model.OrderStatusProcessing:
		return "processing"
	default:
		return "unknown"
	}
}

// getFinishTime 获取完成时间
func getFinishTime(t *time.Time) int64 {
	if t == nil {
		return 0
	}
	return t.Unix()
}
