package recharge

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"recharge-go/internal/model"
	"recharge-go/pkg/logger"
	"recharge-go/pkg/signature"
	"strconv"
	"time"
)

// XianzhuanxiaPlatform 闲赚侠平台实现
type XianzhuanxiaPlatform struct {
	baseURL string
	apiKey  string
	userID  string
}

// NewXianzhuanxiaPlatform 创建闲赚侠平台实例
func NewXianzhuanxiaPlatform(api *model.PlatformAPI) *XianzhuanxiaPlatform {
	return &XianzhuanxiaPlatform{
		baseURL: api.URL,
		apiKey:  api.AppKey,
		userID:  api.MerchantID,
	}
}

// SubmitOrderResult 提交订单结果
type SubmitOrderResult struct {
	Code    int    `json:"code"`
	Message string `json:"message"`
	Data    struct {
		OrderID string `json:"order_id"`
	} `json:"data"`
}

// QueryOrderStatusResult 查询订单状态结果
type QueryOrderStatusResult struct {
	Code    int    `json:"code"`
	Message string `json:"message"`
	Data    struct {
		Status int `json:"status"`
	} `json:"data"`
}

// SubmitOrder 提交订单
func (p *XianzhuanxiaPlatform) SubmitOrder(ctx context.Context, order *model.Order, api *model.PlatformAPI, apiParam *model.PlatformAPIParam) error {
	logger.Info("开始提交闲赚侠订单",
		"order_id", order.ID,
		"order_number", order.OrderNumber,
		"mobile", order.Mobile,
	)

	// 构建请求参数
	params := map[string]string{
		"user_id":    p.userID,
		"order_id":   order.OrderNumber,
		"mobile":     order.Mobile,
		"goods_id":   apiParam.ProductID,
		"amount":     strconv.FormatFloat(order.TotalPrice, 'f', 2, 64),
		"notify_url": api.CallbackURL,
		"timestamp":  strconv.FormatInt(time.Now().Unix(), 10),
	}

	// 生成签名
	authToken, queryTime, err := signature.GenerateXianzhuanxiaSignature(params, p.apiKey, p.userID)
	if err != nil {
		logger.Error("生成签名失败",
			"error", err,
			"params", params,
		)
		return fmt.Errorf("生成签名失败: %v", err)
	}

	// 发送请求
	jsonData, err := json.Marshal(params)
	if err != nil {
		logger.Error("序列化请求参数失败",
			"error", err,
			"params", params,
		)
		return fmt.Errorf("序列化请求参数失败: %v", err)
	}

	req, err := http.NewRequestWithContext(ctx, "POST", p.baseURL+"/api/recharge/submit", bytes.NewBuffer(jsonData))
	if err != nil {
		logger.Error("创建HTTP请求失败",
			"error", err,
			"url", p.baseURL+"/api/recharge/submit",
		)
		return fmt.Errorf("创建HTTP请求失败: %v", err)
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Authorization", "Bearer "+authToken)
	req.URL.RawQuery = fmt.Sprintf("queryTime=%s", queryTime)

	client := &http.Client{Timeout: 30 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		logger.Error("发送HTTP请求失败",
			"error", err,
			"url", req.URL.String(),
		)
		return fmt.Errorf("发送HTTP请求失败: %v", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		logger.Error("读取响应内容失败",
			"error", err,
			"status_code", resp.StatusCode,
		)
		return fmt.Errorf("读取响应内容失败: %v", err)
	}

	var result SubmitOrderResult
	if err := json.Unmarshal(body, &result); err != nil {
		logger.Error("解析响应内容失败",
			"error", err,
			"body", string(body),
		)
		return fmt.Errorf("解析响应内容失败: %v", err)
	}

	if result.Code != 0 {
		logger.Error("提交订单失败",
			"code", result.Code,
			"message", result.Message,
		)
		return fmt.Errorf("提交订单失败: %s", result.Message)
	}

	// 更新订单信息
	order.APIOrderNumber = result.Data.OrderID
	order.APITradeNum = result.Data.OrderID
	order.APICurID = api.ID
	order.PlatformId = api.ID
	order.PlatformName = api.Name
	order.PlatformCode = api.Code

	logger.Info("提交订单成功",
		"order_id", order.ID,
		"order_number", order.OrderNumber,
		"api_order_id", result.Data.OrderID,
	)

	return nil
}

// QueryOrderStatus 查询订单状态
func (p *XianzhuanxiaPlatform) QueryOrderStatus(order *model.Order) (int, error) {
	logger.Info("开始查询闲赚侠订单状态",
		"order_id", order.ID,
		"order_number", order.OrderNumber,
		"api_order_id", order.APIOrderNumber,
	)

	// 构建请求参数
	params := map[string]string{
		"user_id":   p.userID,
		"order_id":  order.APIOrderNumber,
		"timestamp": strconv.FormatInt(time.Now().Unix(), 10),
	}

	// 生成签名
	authToken, queryTime, err := signature.GenerateXianzhuanxiaSignature(params, p.apiKey, p.userID)
	if err != nil {
		logger.Error("生成签名失败",
			"error", err,
			"params", params,
		)
		return 0, fmt.Errorf("生成签名失败: %v", err)
	}

	// 发送请求
	jsonData, err := json.Marshal(params)
	if err != nil {
		logger.Error("序列化请求参数失败",
			"error", err,
			"params", params,
		)
		return 0, fmt.Errorf("序列化请求参数失败: %v", err)
	}

	req, err := http.NewRequest("POST", p.baseURL+"/api/recharge/query", bytes.NewBuffer(jsonData))
	if err != nil {
		logger.Error("创建HTTP请求失败",
			"error", err,
			"url", p.baseURL+"/api/recharge/query",
		)
		return 0, fmt.Errorf("创建HTTP请求失败: %v", err)
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Authorization", "Bearer "+authToken)
	req.URL.RawQuery = fmt.Sprintf("queryTime=%s", queryTime)

	client := &http.Client{Timeout: 30 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		logger.Error("发送HTTP请求失败",
			"error", err,
			"url", req.URL.String(),
		)
		return 0, fmt.Errorf("发送HTTP请求失败: %v", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		logger.Error("读取响应内容失败",
			"error", err,
			"status_code", resp.StatusCode,
		)
		return 0, fmt.Errorf("读取响应内容失败: %v", err)
	}

	var result QueryOrderStatusResult
	if err := json.Unmarshal(body, &result); err != nil {
		logger.Error("解析响应内容失败",
			"error", err,
			"body", string(body),
		)
		return 0, fmt.Errorf("解析响应内容失败: %v", err)
	}

	if result.Code != 0 {
		logger.Error("查询订单状态失败",
			"code", result.Code,
			"message", result.Message,
		)
		return 0, fmt.Errorf("查询订单状态失败: %s", result.Message)
	}

	logger.Info("查询订单状态成功",
		"order_id", order.ID,
		"order_number", order.OrderNumber,
		"status", result.Data.Status,
	)

	return result.Data.Status, nil
}

// ParseCallbackData 解析回调数据
func (p *XianzhuanxiaPlatform) ParseCallbackData(data []byte) (*model.CallbackData, error) {
	var callback struct {
		OrderID   string  `json:"order_id"`
		Status    int     `json:"status"`
		Message   string  `json:"message"`
		Amount    float64 `json:"amount"`
		Sign      string  `json:"sign"`
		Timestamp string  `json:"timestamp"`
	}

	if err := json.Unmarshal(data, &callback); err != nil {
		return nil, fmt.Errorf("解析回调数据失败: %v", err)
	}

	// 验证签名
	params := map[string]string{
		"order_id":  callback.OrderID,
		"status":    strconv.Itoa(callback.Status),
		"amount":    strconv.FormatFloat(callback.Amount, 'f', 2, 64),
		"timestamp": callback.Timestamp,
	}

	authToken, _, err := signature.GenerateXianzhuanxiaSignature(params, p.apiKey, p.userID)
	if err != nil {
		return nil, fmt.Errorf("生成签名失败: %v", err)
	}

	if authToken != callback.Sign {
		return nil, fmt.Errorf("签名验证失败")
	}

	// 转换状态
	var status string
	switch callback.Status {
	case 1:
		status = "success"
	case 2:
		status = "failed"
	default:
		status = "processing"
	}

	return &model.CallbackData{
		OrderID:       callback.OrderID,
		Status:        status,
		Message:       callback.Message,
		Amount:        strconv.FormatFloat(callback.Amount, 'f', 2, 64),
		Sign:          callback.Sign,
		Timestamp:     callback.Timestamp,
		TransactionID: callback.OrderID,
	}, nil
}
