package recharge

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"strconv"
	"time"

	"recharge-go/internal/model"
	"recharge-go/pkg/logger"
	"recharge-go/pkg/signature"
)

// KekebangPlatform 科科帮平台实现
type KekebangPlatform struct {
	*BasePlatform
}

// NewKekebangPlatform 创建科科帮平台实例
func NewKekebangPlatform() *KekebangPlatform {
	return &KekebangPlatform{
		BasePlatform: NewBasePlatform("kekebang"),
	}
}

// GetPlatformName 获取平台名称
func (p *KekebangPlatform) GetPlatformName() string {
	return "kekebang"
}

// SubmitOrder 提交充值订单
func (p *KekebangPlatform) SubmitOrder(ctx context.Context, order *model.Order, api *model.PlatformAPI) error {
	logger.Info("【开始执行可客帮充值】order_id: %d", order.ID)

	// 构建请求参数
	params := map[string]interface{}{
		"app_key":    api.AppKey,
		"timestamp":  strconv.FormatInt(time.Now().Unix(), 10),
		"biz_code":   "1",
		"order_id":   order.OrderNumber,
		"sku_code":   "SKU00000007",
		"notify_url": api.CallbackURL,
		"data": map[string]string{
			"account": order.Mobile,
		},
	}
	logger.Info("【充值请求参数】order_id: %d, params: %+v", order.ID, params)

	// 使用客帮帮平台的签名方法
	sign := signature.GenerateKekebangSign(params, api.SecretKey)
	params["sign"] = sign

	// 发送请求
	resp, err := p.sendRequest(ctx, api.URL, params)
	if err != nil {
		logger.Error("【发送请求失败】order_id: %d, error: %v", order.ID, err)
		return fmt.Errorf("send request failed: %v", err)
	}
	logger.Info("【发送请求成功】order_id: %d, response: %+v", order.ID, resp)

	// 先打印一下 resp.Code 的值和类型
	logger.Info("【平台返回码】order_id: %d, code: %v, code type: %T", order.ID, resp.Code, resp.Code)

	// 确保 Code 是字符串类型
	code := fmt.Sprintf("%v", resp.Code)

	switch code {
	case "0000":
		logger.Info("【下单成功】order_id: %d", order.ID)
	case "10006":
		logger.Info("【订单重复提交】order_id: %d", order.ID)
	default:
		logger.Error("【平台返回错误】order_id: %d, code: %s, message: %s",
			order.ID, code, resp.Message)
		return fmt.Errorf("submit order failed: %s", resp.Message)
	}

	logger.Info("【可客帮充值完成】order_id: %d", order.ID)
	return nil
}

// HandleCallback 处理平台回调
func (p *KekebangPlatform) HandleCallback(ctx context.Context, data []byte) error {
	logger.Info("【开始处理客帮帮回调】data: %s", string(data))

	// 解析回调数据
	var callback KekebangCallbackResponse
	if err := json.Unmarshal(data, &callback); err != nil {
		logger.Error("【解析回调数据失败】error: %v", err)
		return fmt.Errorf("unmarshal callback data failed: %v", err)
	}

	// 将结构体转换为map用于签名验证
	callbackMap := map[string]interface{}{
		"order_id":    callback.OrderID,
		"terrace_id":  callback.TerraceID,
		"account":     callback.Account,
		"time":        callback.Time,
		"return_msg":  callback.ReturnMsg,
		"amount":      callback.Amount,
		"proof":       callback.Proof,
		"card_no":     callback.CardNo,
		"order_state": callback.OrderState,
		"error_code":  callback.ErrorCode,
	}

	// 验证签名
	if !signature.VerifyKekebangSign(callbackMap, callback.Sign, "") {
		logger.Error("【签名验证失败】order_id: %s", callback.OrderID)
		return fmt.Errorf("invalid sign")
	}

	// 处理订单状态
	switch callback.OrderState {
	case 2: // 充值成功
		logger.Info("【充值成功】order_id: %s, terrace_id: %s",
			callback.OrderID, callback.TerraceID)
		// TODO: 更新订单状态为充值成功
	default:
		logger.Error("【充值失败】order_id: %s, order_state: %d, error_code: %v",
			callback.OrderID, callback.OrderState, callback.ErrorCode)
		// TODO: 更新订单状态为充值失败
	}

	logger.Info("【客帮帮回调处理完成】order_id: %s", callback.OrderID)
	return nil
}

// sendRequest 发送HTTP请求
func (p *KekebangPlatform) sendRequest(ctx context.Context, url string, params map[string]interface{}) (*Response, error) {
	// 将参数转换为JSON
	jsonParams, err := json.Marshal(params)
	if err != nil {
		return nil, fmt.Errorf("marshal params failed: %v", err)
	}

	// 创建请求
	req, err := http.NewRequestWithContext(ctx, "POST", url, bytes.NewBuffer(jsonParams))
	if err != nil {
		return nil, fmt.Errorf("create request failed: %v", err)
	}

	// 设置请求头
	req.Header.Set("Content-Type", "application/json")

	// 发送请求
	client := &http.Client{}
	resp, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("do request failed: %v", err)
	}
	defer resp.Body.Close()

	// 读取响应
	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("read response failed: %v", err)
	}

	// 解析响应
	var result Response
	if err := json.Unmarshal(body, &result); err != nil {
		return nil, fmt.Errorf("unmarshal response failed: %v", err)
	}

	return &result, nil
}

// Response 响应结构
type Response struct {
	Code    string `json:"code"`
	Message string `json:"message"`
	OrderNo string `json:"order_no"`
	Status  string `json:"status"`
}

// KekebangCallbackResponse 客帮帮回调响应结构
type KekebangCallbackResponse struct {
	OrderID    string  `json:"order_id"`    // 订单号
	TerraceID  string  `json:"terrace_id"`  // 平台订单号
	Account    string  `json:"account"`     // 充值账号
	Time       string  `json:"time"`        // 回调时间
	ReturnMsg  string  `json:"return_msg"`  // 返回信息
	Amount     float64 `json:"amount"`      // 充值金额
	Proof      string  `json:"proof"`       // 凭证
	CardNo     string  `json:"card_no"`     // 卡号
	OrderState int     `json:"order_state"` // 订单状态：2-充值成功
	ErrorCode  *string `json:"error_code"`  // 错误码
	Sign       string  `json:"sign"`        // 签名
}
