package service

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/pkg/signature"
	"strconv"
	"time"
)

// PlatformService 平台服务
type PlatformService struct {
	platformRepo repository.PlatformRepository
	orderRepo    repository.OrderRepository
}

// NewPlatformService 创建平台服务
func NewPlatformService(platformRepo repository.PlatformRepository, orderRepo repository.OrderRepository) *PlatformService {
	return &PlatformService{
		platformRepo: platformRepo,
		orderRepo:    orderRepo,
	}
}

// ListPlatforms 获取平台列表
func (s *PlatformService) ListPlatforms(req *model.PlatformListRequest) ([]model.Platform, int64) {
	platforms, total, _ := s.platformRepo.ListPlatforms(req)
	return platforms, total
}

// CreatePlatform 创建平台
func (s *PlatformService) CreatePlatform(req *model.PlatformCreateRequest) error {
	platform := &model.Platform{
		Name:        req.Name,
		Code:        req.Code,
		ApiURL:      req.ApiURL,
		Description: req.Description,
		Status:      1, // 默认启用
	}
	return s.platformRepo.CreatePlatform(platform)
}

// GetPlatformByID 根据ID获取平台
func (s *PlatformService) GetPlatformByID(id int64) (*model.Platform, error) {
	return s.platformRepo.GetPlatformByID(id)
}

// UpdatePlatform 更新平台
func (s *PlatformService) UpdatePlatform(id int64, req *model.PlatformUpdateRequest) error {
	platform := &model.Platform{
		ID:          id,
		Name:        req.Name,
		ApiURL:      req.ApiURL,
		Description: req.Description,
	}
	if req.Status != nil {
		platform.Status = *req.Status
	}
	return s.platformRepo.UpdatePlatform(platform)
}

// DeletePlatform 删除平台
func (s *PlatformService) DeletePlatform(id int64) error {
	return s.platformRepo.Delete(id)
}

// GetPlatform 获取平台
func (s *PlatformService) GetPlatform(id int64) (*model.Platform, error) {
	return s.platformRepo.GetPlatformByID(id)
}

// ListPlatformAccounts 获取平台账号列表
func (s *PlatformService) ListPlatformAccounts(req *model.PlatformAccountListRequest) (*model.PlatformAccountListResponse, error) {
	return s.platformRepo.ListPlatformAccounts(req)
}

// CreatePlatformAccount 创建平台账号
func (s *PlatformService) CreatePlatformAccount(req *model.PlatformAccountCreateRequest) error {
	account := &model.PlatformAccount{
		PlatformID:   req.PlatformID,
		AccountName:  req.AccountName,
		Type:         req.Type,
		AppKey:       req.AppKey,
		AppSecret:    req.AppSecret,
		Description:  req.Description,
		DailyLimit:   req.DailyLimit,
		MonthlyLimit: req.MonthlyLimit,
		Priority:     req.Priority,
	}
	if req.Status != nil {
		account.Status = *req.Status
	} else {
		account.Status = 1 // 默认启用
	}
	return s.platformRepo.CreatePlatformAccount(account)
}

// GetPlatformAccount 获取平台账号
func (s *PlatformService) GetPlatformAccount(id int64) (*model.PlatformAccount, error) {
	return s.platformRepo.GetPlatformAccountByID(id)
}

// UpdatePlatformAccount 更新平台账号
func (s *PlatformService) UpdatePlatformAccount(account *model.PlatformAccount) error {
	return s.platformRepo.UpdatePlatformAccount(account)
}

// DeletePlatformAccount 删除平台账号
func (s *PlatformService) DeletePlatformAccount(id int64) error {
	return s.platformRepo.DeleteAccount(context.Background(), id)
}

// SendNotification 发送订单状态通知
func (s *PlatformService) SendNotification(ctx context.Context, order *model.Order) error {
	// 1. 获取平台配置
	// platform, err := s.platformRepo.GetPlatformByID(order.PlatformId)
	// if err != nil {
	// 	return fmt.Errorf("获取平台配置失败: %w", err)
	// }
	data := map[string]interface{}{
		"data": map[string]interface{}{
			"user_order_id": order.OutTradeNum,
			"status":        9,
			"rsp_info":      "充值成功",
		},
	}
	jsonData, err := json.Marshal(data["data"])
	if err != nil {
		fmt.Println(err)
	}
	params := map[string]interface{}{
		"data": string(jsonData),
	}
	params["app_key"] = "1675958551"
	params["timestamp"] = strconv.FormatInt(time.Now().Unix(), 10)

	// 3. 生成签名
	params["sign"] = signature.GenerateSign(params, "3649c621b6945721")
	fmt.Printf("params----------: %v\n", params)
	// 4. 发送通知请求
	url := "http://test.shop.center.mf178.cn/userapi/sgd/updateStatus"
	resp, err := s.sendRequest(ctx, url, params)
	if err != nil {
		return fmt.Errorf("发送通知请求失败: %w", err)
	}

	// 5. 处理响应
	if resp.Code != 0 {
		return fmt.Errorf("通知发送失败: %s", resp.Message)
	}

	return nil
}

// convertOrderStatus 转换订单状态
func (s *PlatformService) convertOrderStatus(status model.OrderStatus) string {
	switch status {
	case model.OrderStatusSuccess:
		return "SUCCESS"
	case model.OrderStatusFailed:
		return "FAILED"
	case model.OrderStatusProcessing:
		return "PROCESSING"
	default:
		return "UNKNOWN"
	}
}

// sendRequest 发送HTTP请求
func (s *PlatformService) sendRequest(ctx context.Context, url string, params map[string]interface{}) (*struct {
	Code    int    `json:"code"`
	Message string `json:"message"`
}, error) {
	// 1. 将参数转换为JSON
	jsonData, err := json.Marshal(params)
	if err != nil {
		return nil, fmt.Errorf("参数序列化失败: %w", err)
	}

	// 2. 创建请求
	req, err := http.NewRequestWithContext(ctx, "POST", url, bytes.NewBuffer(jsonData))
	if err != nil {
		return nil, fmt.Errorf("创建请求失败: %w", err)
	}

	// 3. 设置请求头
	req.Header.Set("Content-Type", "application/json")

	// 4. 发送请求
	client := &http.Client{
		Timeout: 10 * time.Second,
	}
	resp, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("发送请求失败: %w", err)
	}
	defer resp.Body.Close()

	// 5. 读取响应
	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("读取响应失败: %w", err)
	}

	// 6. 解析响应
	var result struct {
		Code    int    `json:"code"`
		Message string `json:"message"`
	}
	if err := json.Unmarshal(body, &result); err != nil {
		return nil, fmt.Errorf("解析响应失败: %w", err)
	}

	return &result, nil
}

// GetOrder 获取订单信息
func (s *PlatformService) GetOrder(ctx context.Context, orderID int64) (*model.Order, error) {
	return s.orderRepo.GetByID(ctx, orderID)
}
