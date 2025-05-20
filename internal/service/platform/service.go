package platform

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"recharge-go/configs"
	"recharge-go/pkg/signature"
	"strconv"
	"time"
)

// PlatformOrder 平台返回的订单数据结构
type PlatformOrder struct {
	OrderNumber            string    `json:"orderNumber"`            // 订单号
	ChannelName            string    `json:"channelName"`            // 渠道名称
	ProductName            string    `json:"productName"`            // 产品名称
	AccountNum             string    `json:"accountNum"`             // 充值账号
	AccountLocation        string    `json:"accountLocation"`        // 归属地
	SettlementAmount       float64   `json:"settlementAmount"`       // 结算金额
	OrderStatus            int       `json:"orderStatus"`            // 订单状态
	SettlementStatus       int       `json:"settlementStatus"`       // 结算状态
	CreateTime             time.Time `json:"createTime"`             // 创建时间
	ExpirationTime         time.Time `json:"expirationTime"`         // 过期时间
	SettlementTime         time.Time `json:"settlementTime"`         // 结算时间
	ExpectedSettlementTime time.Time `json:"expectedSettlementTime"` // 预计结算时间
}

// Channel 渠道信息
type Channel struct {
	ChannelID   int       `json:"channelId"`   // 渠道编号
	ChannelName string    `json:"channelName"` // 渠道名称
	ProductList []Product `json:"productList"` // 渠道对应下的运营商信息
}

// Product 产品信息
type Product struct {
	ProductID   int    `json:"productId"`   // 运营商编号
	ProductName string `json:"productName"` // 运营商名称
}

// StockInfo 库存信息
type StockInfo struct {
	ChannelID int     `json:"channelId"` // 渠道编号
	ProductID int     `json:"productId"` // 运营商编号
	FaceValue float64 `json:"faceValue"` // 面值
	StockList []Stock `json:"stockList"` // 该面值的库存信息
}

// Stock 库存详情
type Stock struct {
	SettleAmount float64 `json:"settleAmount"` // 结算金额
	Stock        int     `json:"stock"`        // 库存数量
}

// PageResult 分页结果
type PageResult struct {
	EndRow   int64 `json:"endRow"`   // 结束行数
	PageNum  int64 `json:"pageNum"`  // 当前页码
	PageSize int64 `json:"pageSize"` // 每页多少条
	Pages    int64 `json:"pages"`    // 总页码
	StartRow int64 `json:"startRow"` // 开始行数
	Total    int64 `json:"total"`    // 总数
}

type Service struct {
	apiKey  string
	userID  string
	baseURL string
}

func NewService() *Service {
	cfg := configs.GetConfig()
	return &Service{
		apiKey:  cfg.API.Key,
		userID:  cfg.API.UserID,
		baseURL: cfg.API.BaseURL,
	}
}

// SubmitTask 申请做单
func (s *Service) SubmitTask(channelID int, productID int, provinces string, faceValues, minSettleAmounts string) (string, error) {
	params := map[string]string{
		"channelId":        strconv.Itoa(channelID),
		"productIds":       strconv.Itoa(productID),
		"provinces":        "",
		"faceValues":       faceValues,
		"minSettleAmounts": minSettleAmounts,
	}
	apiKey := "c362d30409744d7584abcbd3b58124c2"
	userID := "558203"
	authToken, _, err := signature.GenerateXianzhuanxiaSignature(params, apiKey, userID)
	if err != nil {
		return "", fmt.Errorf("生成签名失败: %v", err)
	}
	url := fmt.Sprintf("%s/api/task/recharge/submit", "https://cusapitest.xianzhuanxia.com")
	fmt.Printf("申请做单url: %s\n", url)
	//添加请求头
	// 创建请求体
	jsonData, err := json.Marshal(params)
	if err != nil {
		return "", fmt.Errorf("创建请求体失败: %v", err)
	}
	req, err := http.NewRequest("POST", url, bytes.NewBuffer(jsonData))
	if err != nil {
		return "", fmt.Errorf("创建请求失败: %v", err)
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Auth_Token", authToken)

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return "", fmt.Errorf("发送请求失败: %v", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return "", fmt.Errorf("读取响应失败: %v", err)
	}

	if resp.StatusCode != http.StatusOK {
		return "", fmt.Errorf("请求失败: %s", string(body))
	}

	var result struct {
		Code   int    `json:"code"`
		Msg    string `json:"msg"`
		Result struct {
			Token string `json:"token"`
		} `json:"result"`
	}

	if err := json.Unmarshal(body, &result); err != nil {
		return "", fmt.Errorf("解析响应失败: %v", err)
	}

	if result.Code != 0 {
		return "", fmt.Errorf("业务错误: %s", result.Msg)
	}

	return result.Result.Token, nil
}

// QueryTask 查询申请做单是否匹配到订单
func (s *Service) QueryTask(token string) (*PlatformOrder, error) {
	params := map[string]string{
		"token": token,
	}
	apiKey := "c362d30409744d7584abcbd3b58124c2"
	userID := "558203"
	authToken, queryTime, err := signature.GenerateXianzhuanxiaSignature(params, apiKey, userID)
	if err != nil {
		return nil, fmt.Errorf("生成签名失败: %v", err)
	}
	jsonData, err := json.Marshal(params)
	if err != nil {
		return nil, err
	}

	url := fmt.Sprintf("%s/api/task/recharge/query", "https://cusapitest.xianzhuanxia.com")
	req, err := http.NewRequest("POST", url, bytes.NewBuffer(jsonData))
	if err != nil {
		return nil, fmt.Errorf("创建请求失败: %v", err)
	}
	fmt.Printf("queryTime: %s\n", queryTime)
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Auth_Token", authToken)

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("发送请求失败: %v", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("读取响应失败: %v", err)
	}

	if resp.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("请求失败: %s", string(body))
	}

	var result struct {
		Code   int    `json:"code"`
		Msg    string `json:"msg"`
		Result struct {
			MatchStatus int             `json:"matchStatus"`
			Orders      []PlatformOrder `json:"orders"`
		} `json:"result"`
	}

	if err := json.Unmarshal(body, &result); err != nil {
		return nil, fmt.Errorf("解析响应失败: %v", err)
	}

	if result.Code != 0 {
		return nil, fmt.Errorf("业务错误: %s", result.Msg)
	}

	if result.Result.MatchStatus != 3 || len(result.Result.Orders) == 0 {
		return nil, nil
	}

	return &result.Result.Orders[0], nil
}

// ReportTask 上报做单订单结果
func (s *Service) ReportTask(orderNumber string, status int, remark, payVoucher, verifyData string) error {
	params := map[string]string{
		"orderNumber": orderNumber,
		"status":      strconv.Itoa(status),
		"remark":      remark,
		"payVoucher":  payVoucher,
		"verifyData":  verifyData,
	}

	authToken, queryTime, err := signature.GenerateXianzhuanxiaSignature(params, s.apiKey, s.userID)
	if err != nil {
		return fmt.Errorf("生成签名失败: %v", err)
	}

	url := fmt.Sprintf("%s/api/task/recharge/reported", s.baseURL)
	req, err := http.NewRequest("POST", url, nil)
	if err != nil {
		return fmt.Errorf("创建请求失败: %v", err)
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Auth-Token", authToken)
	req.Header.Set("Query-Time", queryTime)

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return fmt.Errorf("发送请求失败: %v", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return fmt.Errorf("读取响应失败: %v", err)
	}

	if resp.StatusCode != http.StatusOK {
		return fmt.Errorf("请求失败: %s", string(body))
	}

	var result struct {
		Code int    `json:"code"`
		Msg  string `json:"msg"`
	}

	if err := json.Unmarshal(body, &result); err != nil {
		return fmt.Errorf("解析响应失败: %v", err)
	}

	if result.Code != 0 {
		return fmt.Errorf("业务错误: %s", result.Msg)
	}

	return nil
}

// GetOrderDetail 查询做单订单详情（单个）
func (s *Service) GetOrderDetail(orderNumber string) (*PlatformOrder, error) {
	params := map[string]string{
		"orderNumber": orderNumber,
	}

	authToken, queryTime, err := signature.GenerateXianzhuanxiaSignature(params, s.apiKey, s.userID)
	if err != nil {
		return nil, fmt.Errorf("生成签名失败: %v", err)
	}

	url := fmt.Sprintf("%s/api/task/recharge/orderDetail", s.baseURL)
	req, err := http.NewRequest("POST", url, nil)
	if err != nil {
		return nil, fmt.Errorf("创建请求失败: %v", err)
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Auth-Token", authToken)
	req.Header.Set("Query-Time", queryTime)

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("发送请求失败: %v", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("读取响应失败: %v", err)
	}

	if resp.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("请求失败: %s", string(body))
	}

	var result struct {
		Code   int           `json:"code"`
		Msg    string        `json:"msg"`
		Result PlatformOrder `json:"result"`
	}

	if err := json.Unmarshal(body, &result); err != nil {
		return nil, fmt.Errorf("解析响应失败: %v", err)
	}

	if result.Code != 0 {
		return nil, fmt.Errorf("业务错误: %s", result.Msg)
	}

	return &result.Result, nil
}

// GetOrderList 查询做单订单详情（分页）
func (s *Service) GetOrderList(orderNumber string, orderStatus, settlementStatus, pageNum, pageSize int) ([]PlatformOrder, *PageResult, error) {
	params := map[string]string{
		"orderNumber":      orderNumber,
		"orderStatus":      strconv.Itoa(orderStatus),
		"settlementStatus": strconv.Itoa(settlementStatus),
		"pageNum":          strconv.Itoa(pageNum),
		"pageSize":         strconv.Itoa(pageSize),
	}

	authToken, queryTime, err := signature.GenerateXianzhuanxiaSignature(params, s.apiKey, s.userID)
	if err != nil {
		return nil, nil, fmt.Errorf("生成签名失败: %v", err)
	}

	url := fmt.Sprintf("%s/api/task/recharge/page", s.baseURL)
	req, err := http.NewRequest("POST", url, nil)
	if err != nil {
		return nil, nil, fmt.Errorf("创建请求失败: %v", err)
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Auth-Token", authToken)
	req.Header.Set("Query-Time", queryTime)

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return nil, nil, fmt.Errorf("发送请求失败: %v", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, nil, fmt.Errorf("读取响应失败: %v", err)
	}

	if resp.StatusCode != http.StatusOK {
		return nil, nil, fmt.Errorf("请求失败: %s", string(body))
	}

	var result struct {
		Code   int             `json:"code"`
		Msg    string          `json:"msg"`
		Page   PageResult      `json:"page"`
		Result []PlatformOrder `json:"result"`
	}

	if err := json.Unmarshal(body, &result); err != nil {
		return nil, nil, fmt.Errorf("解析响应失败: %v", err)
	}

	if result.Code != 0 {
		return nil, nil, fmt.Errorf("业务错误: %s", result.Msg)
	}

	return result.Result, &result.Page, nil
}

// GetChannelList 查询所有渠道及对应运营商编码
func (s *Service) GetChannelList() ([]Channel, error) {
	params := map[string]string{}
	apiKey := "c362d30409744d7584abcbd3b58124c2"
	userID := "558203"
	authToken, queryTime, err := signature.GenerateXianzhuanxiaSignature(params, apiKey, userID)
	if err != nil {
		return nil, fmt.Errorf("生成签名失败: %v", err)
	}

	url := fmt.Sprintf("%s/api/task/recharge/taskChannelList", "https://cusapitest.xianzhuanxia.com")
	req, err := http.NewRequest("POST", url, nil)
	if err != nil {
		return nil, fmt.Errorf("创建请求失败: %v", err)
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Auth_Token", authToken)
	req.Header.Set("Query-Time", queryTime)

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("发送请求失败: %v", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("读取响应失败: %v", err)
	}

	if resp.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("请求失败: %s", string(body))
	}

	var result struct {
		Code   int       `json:"code"`
		Msg    string    `json:"msg"`
		Result []Channel `json:"result"`
	}

	if err := json.Unmarshal(body, &result); err != nil {
		return nil, fmt.Errorf("解析响应失败: %v", err)
	}

	if result.Code != 0 {
		return nil, fmt.Errorf("业务错误: %s", result.Msg)
	}

	return result.Result, nil
}

// GetStockInfo 查询库存信息
func (s *Service) GetStockInfo(channelID, productID int, provinces string) ([]StockInfo, error) {
	params := map[string]string{
		"channelId": strconv.Itoa(channelID),
		"productId": strconv.Itoa(productID),
		"provinces": provinces,
	}

	authToken, queryTime, err := signature.GenerateXianzhuanxiaSignature(params, s.apiKey, s.userID)
	if err != nil {
		return nil, fmt.Errorf("生成签名失败: %v", err)
	}

	url := fmt.Sprintf("%s/api/task/recharge/stock", s.baseURL)
	req, err := http.NewRequest("POST", url, nil)
	if err != nil {
		return nil, fmt.Errorf("创建请求失败: %v", err)
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Auth-Token", authToken)
	req.Header.Set("Query-Time", queryTime)

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("发送请求失败: %v", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("读取响应失败: %v", err)
	}

	if resp.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("请求失败: %s", string(body))
	}

	var result struct {
		Code   int         `json:"code"`
		Msg    string      `json:"msg"`
		Result []StockInfo `json:"result"`
	}

	if err := json.Unmarshal(body, &result); err != nil {
		return nil, fmt.Errorf("解析响应失败: %v", err)
	}

	if result.Code != 0 {
		return nil, fmt.Errorf("业务错误: %s", result.Msg)
	}

	return result.Result, nil
}
