package platform

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"recharge-go/configs"
	"recharge-go/internal/repository"
	"recharge-go/pkg/logger"
	"recharge-go/pkg/signature"
	"strconv"
	"time"
)

// MilliTime 支持毫秒时间戳自动转 time.Time
type MilliTime struct {
	time.Time
}

func (mt *MilliTime) UnmarshalJSON(b []byte) error {
	// 去掉引号
	s := string(b)
	if len(s) >= 2 && s[0] == '"' && s[len(s)-1] == '"' {
		s = s[1 : len(s)-1]
	}
	millis, err := strconv.ParseInt(s, 10, 64)
	if err != nil {
		mt.Time = time.Time{}
		return err
	}
	mt.Time = time.UnixMilli(millis)
	return nil
}

// PlatformOrder 平台返回的订单数据结构
type PlatformOrder struct {
	OrderNumber            string    `json:"orderNumber"`            // 订单号
	ChannelName            string    `json:"channelName"`            // 渠道名称
	ProductName            string    `json:"productName"`            // 产品名称
	AccountNum             string    `json:"accountNum"`             // 充值账号
	AccountLocation        string    `json:"accountLocation"`        // 归属地
	SettlementAmount       float64   `json:"settlementAmount"`       // 结算金额
	FaceValue              float64   `json:"faceValue"`              // 面值
	OrderStatus            int       `json:"orderStatus"`            // 订单状态
	SettlementStatus       int       `json:"settlementStatus"`       // 结算状态
	CreateTime             MilliTime `json:"createTime"`             // 创建时间
	ExpirationTime         MilliTime `json:"expirationTime"`         // 过期时间
	SettlementTime         MilliTime `json:"settlementTime"`         // 结算时间
	ExpectedSettlementTime MilliTime `json:"expectedSettlementTime"` // 预计结算时间
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
	apiKey    string
	userID    string
	baseURL   string
	tokenRepo *repository.PlatformTokenRepository
}

func NewService(tokenRepo *repository.PlatformTokenRepository) *Service {
	cfg := configs.GetConfig()
	return &Service{
		apiKey:    cfg.API.Key,
		userID:    cfg.API.UserID,
		baseURL:   cfg.API.BaseURL,
		tokenRepo: tokenRepo,
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
	authToken, _, err := signature.GenerateXianzhuanxiaSignature(params, apiKey, userID)
	if err != nil {
		return nil, fmt.Errorf("生成签名失败: %v", err)
	}
	jsonData, err := json.Marshal(params)
	if err != nil {
		return nil, err
	}

	// url := fmt.Sprintf("%s/api/task/recharge/query", "https://cusapitest.xianzhuanxia.com")
	url := "http://ip.jikelab.com:5000/api/orders"
	req, err := http.NewRequest("POST", url, bytes.NewBuffer(jsonData))
	if err != nil {
		return nil, fmt.Errorf("创建请求失败: %v", err)
	}
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

// 获取有效 token
func (s *Service) GetToken(channelID, productID int, provinces, faceValues, minSettleAmounts string) (string, error) {
	tokenData, err := s.tokenRepo.Get()
	if err != nil || tokenData == nil || time.Since(tokenData.CreatedAt) >= 5*time.Minute {
		// 申请新 token
		logger.Info(fmt.Sprintf("申请新 token: ChannelID=%d, ProductID=%d, provinces=%s, faceValues=%s, minSettleAmounts=%s", channelID, productID, provinces, faceValues, minSettleAmounts))
		token, err := s.SubmitTask(channelID, productID, provinces, faceValues, minSettleAmounts)
		if err != nil {
			return "", err
		}
		_ = s.tokenRepo.Save(token)
		return token, nil
	}
	return tokenData.Token, nil
}

// 匹配到订单后让 token 失效
func (s *Service) InvalidateToken() error {
	return s.tokenRepo.Delete()
}

// PushToThirdParty 推送订单到第三方平台
func (s *Service) PushToThirdParty(order *PlatformOrder, notifyUrl string) error {
	params := map[string]interface{}{
		"orderNumber":            order.OrderNumber,
		"channelName":            order.ChannelName,
		"productName":            order.ProductName,
		"accountNum":             order.AccountNum,
		"accountLocation":        order.AccountLocation,
		"settlementAmount":       order.SettlementAmount,
		"orderStatus":            order.OrderStatus,
		"settlementStatus":       order.SettlementStatus,
		"createTime":             order.CreateTime.UnixMilli(),
		"expirationTime":         order.ExpirationTime.UnixMilli(),
		"settlementTime":         order.SettlementTime.UnixMilli(),
		"expectedSettlementTime": order.ExpectedSettlementTime.UnixMilli(),
	}
	jsonData, err := json.Marshal(params)
	if err != nil {
		return fmt.Errorf("参数序列化失败: %v", err)
	}

	req, err := http.NewRequest("POST", notifyUrl, bytes.NewBuffer(jsonData))
	if err != nil {
		return fmt.Errorf("创建请求失败: %v", err)
	}
	req.Header.Set("Content-Type", "application/json")

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

	// 可根据第三方返回内容做进一步处理
	return nil
}
