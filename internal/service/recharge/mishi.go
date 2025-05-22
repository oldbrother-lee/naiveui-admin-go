package recharge

import (
	"bytes"
	"context"
	"crypto/md5"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/pkg/logger"
	"strconv"
	"time"

	"gorm.io/gorm"
)

// MishiPlatform 秘史平台实现
type MishiPlatform struct {
	platformRepo repository.PlatformRepository
}

// NewMishiPlatform 创建秘史平台实例
func NewMishiPlatform(db *gorm.DB) *MishiPlatform {
	return &MishiPlatform{
		platformRepo: repository.NewPlatformRepository(db),
	}
}

// GetName 获取平台名称
func (p *MishiPlatform) GetName() string {
	return "mishi"
}

// getAPIKeyAndSecret 获取API密钥和密钥
func (p *MishiPlatform) getAPIKeyAndSecret(accountID int64) (string, string, error) {
	account, err := p.platformRepo.GetAccountByID(context.Background(), accountID)
	if err != nil {
		return "", "", fmt.Errorf("获取平台账号信息失败: %v", err)
	}
	return account.AppKey, account.AppSecret, nil
}

// SubmitOrder 提交订单
func (p *MishiPlatform) SubmitOrder(ctx context.Context, order *model.Order, api *model.PlatformAPI, apiParam *model.PlatformAPIParam) error {
	logger.Info("开始提交秘史订单",
		"order_id", order.ID,
		"order_number", order.OrderNumber,
		"mobile", order.Mobile,
	)

	// 获取API密钥和密钥
	appKey, appSecret, err := p.getAPIKeyAndSecret(api.AccountID)
	if err != nil {
		return fmt.Errorf("获取API密钥失败: %v", err)
	}

	// 构建请求参数
	params := url.Values{}
	params.Add("szAgentId", appKey)
	params.Add("szOrderId", order.OutTradeNum)
	params.Add("szPhoneNum", order.Mobile)
	params.Add("nMoney", strconv.FormatInt(int64(order.TotalPrice), 10))
	params.Add("nSortType", "1")
	params.Add("nProductClass", "1")
	params.Add("nProductType", "1")
	params.Add("szProductId", apiParam.ProductID)

	// 生成时间戳
	timestamp := time.Now().Format("2006-01-02 15:04:05")
	params.Add("szTimeStamp", timestamp)

	// 生成签名
	signStr := fmt.Sprintf("szAgentId=%s&szOrderId=%s&szPhoneNum=%s&nMoney=%d&nSortType=%d&nProductClass=1&nProductType=1&szTimeStamp=%s&szKey=%s",
		appKey, order.OutTradeNum, order.Mobile, int(order.TotalPrice), 1, timestamp, appSecret)
	sign := fmt.Sprintf("%x", md5.Sum([]byte(signStr)))
	params.Add("szVerifyString", sign)

	// 添加回调地址
	params.Add("szNotifyUrl", api.CallbackURL)

	// 发送请求
	resp, err := p.sendRequest(ctx, api.URL, params)
	if err != nil {
		return fmt.Errorf("发送请求失败: %v", err)
	}

	// 处理响应
	if resp.Code != "0" {
		return fmt.Errorf("提交订单失败: %s", resp.Message)
	}

	// 更新订单信息
	order.APIOrderNumber = resp.OrderID
	order.APITradeNum = resp.OrderID

	logger.Info("提交订单成功",
		"order_id", order.ID,
		"order_number", order.OrderNumber,
		"api_order_id", resp.OrderID,
	)

	return nil
}

// QueryOrderStatus 查询订单状态
func (p *MishiPlatform) QueryOrderStatus(order *model.Order) (model.OrderStatus, error) {
	logger.Info("开始查询秘史订单状态",
		"order_id", order.ID,
		"order_number", order.OrderNumber,
		"api_order_id", order.APIOrderNumber,
	)

	// 获取API密钥和密钥
	appKey, appSecret, err := p.getAPIKeyAndSecret(order.PlatformAccountID)
	if err != nil {
		return 0, fmt.Errorf("获取API密钥失败: %v", err)
	}

	// 构建请求参数
	params := url.Values{}
	params.Add("szAgentId", appKey)
	params.Add("szOrderId", order.APIOrderNumber)

	// 生成时间戳
	timestamp := time.Now().Format("2006-01-02 15:04:05")
	params.Add("szTimeStamp", timestamp)

	// 生成签名
	signStr := fmt.Sprintf("szAgentId=%s&szOrderId=%s&szTimeStamp=%s&szKey=%s",
		appKey, order.APIOrderNumber, timestamp, appSecret)
	sign := fmt.Sprintf("%x", md5.Sum([]byte(signStr)))
	params.Add("szVerifyString", sign)

	// 发送请求
	resp, err := p.sendRequest(context.Background(), order.PlatformURL+"/query", params)
	if err != nil {
		return 0, fmt.Errorf("查询订单状态失败: %v", err)
	}

	// 处理响应
	if resp.Code != "0" {
		return 0, fmt.Errorf("查询订单状态失败: %s", resp.Message)
	}

	// 转换状态
	var status model.OrderStatus
	switch resp.Status {
	case "1":
		status = model.OrderStatusProcessing
	case "2":
		status = model.OrderStatusSuccess
	case "3":
		status = model.OrderStatusFailed
	default:
		status = model.OrderStatusProcessing
	}

	return status, nil
}

// ParseCallbackData 解析回调数据
func (p *MishiPlatform) ParseCallbackData(data []byte) (*model.CallbackData, error) {
	var callback struct {
		OrderID   string  `json:"order_id"`
		Status    string  `json:"status"`
		Message   string  `json:"message"`
		Amount    float64 `json:"amount"`
		Sign      string  `json:"sign"`
		Timestamp string  `json:"timestamp"`
	}

	if err := json.Unmarshal(data, &callback); err != nil {
		return nil, fmt.Errorf("解析回调数据失败: %v", err)
	}

	// 转换状态
	var status string
	switch callback.Status {
	case "1":
		status = "processing"
	case "2":
		status = "success"
	case "3":
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

// sendRequest 发送请求
func (p *MishiPlatform) sendRequest(ctx context.Context, url string, params url.Values) (*MishiResponse, error) {
	// 创建请求
	req, err := http.NewRequestWithContext(ctx, "POST", url, bytes.NewBufferString(params.Encode()))
	if err != nil {
		return nil, fmt.Errorf("创建请求失败: %v", err)
	}

	// 设置请求头
	req.Header.Set("Content-Type", "application/x-www-form-urlencoded")

	// 发送请求
	client := &http.Client{Timeout: 30 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("发送请求失败: %v", err)
	}
	defer resp.Body.Close()

	// 读取响应
	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("读取响应失败: %v", err)
	}

	// 解析响应
	var result MishiResponse
	if err := json.Unmarshal(body, &result); err != nil {
		return nil, fmt.Errorf("解析响应失败: %v", err)
	}

	return &result, nil
}

// QueryBalance 查询账户余额
func (p *MishiPlatform) QueryBalance(ctx context.Context, accountID int64) (float64, error) {
	logger.Info("开始查询秘史账户余额",
		"account_id", accountID,
	)

	// 获取API密钥和密钥
	appKey, appSecret, err := p.getAPIKeyAndSecret(accountID)
	if err != nil {
		return 0, fmt.Errorf("获取API密钥失败: %v", err)
	}

	// 构建请求参数
	params := url.Values{}
	params.Add("szAgentId", appKey)

	// 生成签名
	signStr := fmt.Sprintf("szAgentId=%s&szKey=%s", appKey, appSecret)
	sign := fmt.Sprintf("%x", md5.Sum([]byte(signStr)))
	params.Add("szVerifyString", sign)

	// 发送请求
	resp, err := p.sendRequest(ctx, "/api/old/queryBalance", params)
	if err != nil {
		return 0, fmt.Errorf("查询余额失败: %v", err)
	}

	// 处理响应
	if resp.Code != "0" {
		return 0, fmt.Errorf("查询余额失败: %s", resp.Message)
	}

	// 解析余额
	balance, err := strconv.ParseFloat(resp.Balance, 64)
	if err != nil {
		return 0, fmt.Errorf("解析余额失败: %v", err)
	}

	logger.Info("查询余额成功",
		"account_id", accountID,
		"balance", balance,
	)

	return balance, nil
}

// MishiResponse 秘史平台响应
type MishiResponse struct {
	Code    string `json:"code"`
	Message string `json:"message"`
	OrderID string `json:"order_id"`
	Status  string `json:"status"`
	Balance string `json:"balance"`
}
