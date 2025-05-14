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

// KekebangPlatform 可客帮平台
type KekebangPlatform struct {
	api *model.PlatformAPI
}

// NewKekebangPlatform 创建可客帮平台实例
func NewKekebangPlatform(api *model.PlatformAPI) *KekebangPlatform {
	return &KekebangPlatform{
		api: api,
	}
}

// GetName 获取平台名称
func (p *KekebangPlatform) GetName() string {
	return "kekebang"
}

// SubmitOrder 提交订单
func (p *KekebangPlatform) SubmitOrder(ctx context.Context, order *model.Order, api *model.PlatformAPI) error {
	logger.Info("【开始提交可客帮订单】order_id: %d", order.ID)

	// 构建请求参数
	fmt.Println("提交订单到平台,构建请求参数!!!!!", api)
	params := map[string]interface{}{
		"app_key":    api.AppKey,
		"timestamp":  strconv.FormatInt(time.Now().Unix(), 10),
		"biz_code":   "1", // 充值业务
		"order_id":   order.OrderNumber,
		"sku_code":   "SKU00000007",
		"notify_url": api.CallbackURL,
		"data": map[string]string{
			"account": order.Mobile,
		},
	}

	// 使用客帮帮平台的签名方法
	sign := signature.GenerateKekebangSign(params, api.SecretKey)
	params["sign"] = sign

	// 发送请求
	fmt.Println("提交订单到平台,发送请求", params)
	resp, err := p.sendRequest(ctx, api.URL, params)
	if err != nil {
		logger.Error("【提交订单失败】order_id: %d, error: %v", order.ID, err)
		return fmt.Errorf("submit order failed: %v", err)
	}

	// 确保 Code 是字符串类型
	code := fmt.Sprintf("%v", resp.Code)
	if code != "00000" {
		logger.Error("【提交订单失败】order_id: %d, code: %s, message: %s",
			order.ID, code, resp.Message)
		return fmt.Errorf("submit order failed: %s", resp.Message)
	}

	logger.Info("【提交订单成功】order_id: %d", order.ID)
	return nil
}

// QueryOrderStatus 查询订单状态
func (p *KekebangPlatform) QueryOrderStatus(order *model.Order) (int, error) {
	logger.Info("【开始查询可客帮订单状态】order_id: %d", order.ID)

	// 构建请求参数
	params := map[string]interface{}{
		"app_key":   p.api.AppKey,
		"timestamp": strconv.FormatInt(time.Now().Unix(), 10),
		"biz_code":  "2", // 查询订单状态
		"order_id":  order.OrderNumber,
	}

	// 使用客帮帮平台的签名方法
	sign := signature.GenerateKekebangSign(params, p.api.SecretKey)
	params["sign"] = sign

	// 发送请求
	resp, err := p.sendRequest(context.Background(), p.api.URL, params)
	if err != nil {
		logger.Error("【查询订单状态失败】order_id: %d, error: %v", order.ID, err)
		return 0, fmt.Errorf("query order status failed: %v", err)
	}

	// 确保 Code 是字符串类型
	code := fmt.Sprintf("%v", resp.Code)
	if code != "00000" {
		logger.Error("【查询订单状态失败】order_id: %d, code: %s, message: %s",
			order.ID, code, resp.Message)
		return 0, fmt.Errorf("query order status failed: %s", resp.Message)
	}

	// 根据状态码返回订单状态
	// 这里需要根据实际平台返回的状态码进行映射
	// 假设 2 表示成功，其他表示失败
	status := 0 // 默认失败
	if resp.Status == "2" {
		status = 2 // 成功
	}

	logger.Info("【查询订单状态完成】order_id: %d, status: %d", order.ID, status)
	return status, nil
}

// ParseCallbackData 解析回调数据
func (p *KekebangPlatform) ParseCallbackData(data []byte) (*model.CallbackData, error) {
	// 解析平台返回的数据
	resp := &KekebangCallbackResponse{}
	if err := json.Unmarshal(data, resp); err != nil {
		return nil, fmt.Errorf("parse callback data failed: %v", err)
	}

	// 转换订单状态
	// kekebang状态码：
	// 1：处理中
	// 2：成功
	// 3：失败
	// 4：异常（需人工核实）
	var status string
	switch resp.OrderState {
	case 1:
		status = strconv.Itoa(int(model.OrderStatusRecharging)) // 充值中
	case 2:
		status = strconv.Itoa(int(model.OrderStatusSuccess)) // 成功
	case 3:
		status = strconv.Itoa(int(model.OrderStatusFailed)) // 失败
	case 4:
		status = strconv.Itoa(int(model.OrderStatusProcessing)) // 处理中（异常状态）
	default:
		status = strconv.Itoa(int(model.OrderStatusFailed)) // 默认失败
	}

	// 直接返回解析后的数据，不验证平台返回码

	return &model.CallbackData{
		OrderID:       resp.TerraceID,
		OrderNumber:   resp.OrderID,
		Status:        status, //订单状态
		Message:       resp.ReturnMsg,
		CallbackType:  "order_status",
		Amount:        strconv.FormatFloat(resp.Amount, 'f', 2, 64),
		Sign:          resp.Sign,
		Timestamp:     resp.Time,
		TransactionID: resp.Proof,
	}, nil
}

// sendRequest 发送请求
func (p *KekebangPlatform) sendRequest(ctx context.Context, url string, params map[string]interface{}) (*KekebangResponse, error) {
	// 将参数转换为 JSON
	jsonData, err := json.Marshal(params)
	if err != nil {
		return nil, fmt.Errorf("marshal params failed: %v", err)
	}

	// 创建请求
	req, err := http.NewRequestWithContext(ctx, "POST", url, bytes.NewBuffer(jsonData))
	if err != nil {
		return nil, fmt.Errorf("create request failed: %v", err)
	}

	// 设置请求头
	req.Header.Set("Content-Type", "application/json")

	// 发送请求
	client := &http.Client{}
	resp, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("send request failed: %v", err)
	}
	defer resp.Body.Close()

	// 读取响应
	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("read response failed: %v", err)
	}

	// 解析响应
	var result KekebangResponse
	if err := json.Unmarshal(body, &result); err != nil {
		return nil, fmt.Errorf("unmarshal response failed: %v", err)
	}

	return &result, nil
}

// KekebangResponse 可客帮响应
type KekebangResponse struct {
	Code    interface{} `json:"code"`
	Message string      `json:"message"`
	OrderID string      `json:"order_id"`
	Status  string      `json:"status"`
}

// KekebangCallbackResponse 可客帮回调响应
type KekebangCallbackResponse struct {
	OrderID    string  `json:"order_id"`
	TerraceID  string  `json:"terrace_id"`
	Account    string  `json:"account"`
	Time       string  `json:"time"`
	ReturnMsg  string  `json:"return_msg"`
	Amount     float64 `json:"amount"`
	Proof      string  `json:"proof"`
	CardNo     string  `json:"card_no"`
	OrderState int     `json:"order_state"`
	ErrorCode  *string `json:"error_code"`
	Sign       string  `json:"sign"`
}
