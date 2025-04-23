package controller

import (
	"bytes"
	"context"
	"encoding/json"
	"net/http"
	"net/http/httptest"
	"testing"

	"recharge-go/internal/model"

	"github.com/gin-gonic/gin"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
)

// MockPlatformAPIParamService 模拟服务
type MockPlatformAPIParamService struct {
	mock.Mock
}

func (m *MockPlatformAPIParamService) CreateParam(ctx context.Context, param *model.PlatformAPIParam) error {
	args := m.Called(ctx, param)
	return args.Error(0)
}

func (m *MockPlatformAPIParamService) UpdateParam(ctx context.Context, param *model.PlatformAPIParam) error {
	args := m.Called(ctx, param)
	return args.Error(0)
}

func (m *MockPlatformAPIParamService) DeleteParam(ctx context.Context, id int64) error {
	args := m.Called(ctx, id)
	return args.Error(0)
}

func (m *MockPlatformAPIParamService) GetParam(ctx context.Context, id int64) (*model.PlatformAPIParam, error) {
	args := m.Called(ctx, id)
	if args.Get(0) == nil {
		return nil, args.Error(1)
	}
	return args.Get(0).(*model.PlatformAPIParam), args.Error(1)
}

func (m *MockPlatformAPIParamService) ListParams(ctx context.Context, apiID int64, page, pageSize int) ([]*model.PlatformAPIParam, int64, error) {
	args := m.Called(ctx, apiID, page, pageSize)
	return args.Get(0).([]*model.PlatformAPIParam), args.Get(1).(int64), args.Error(2)
}

func setupRouter() *gin.Engine {
	gin.SetMode(gin.TestMode)
	r := gin.Default()
	return r
}

func TestPlatformAPIParamController(t *testing.T) {
	mockService := new(MockPlatformAPIParamService)
	controller := NewPlatformAPIParamController(mockService)
	router := setupRouter()

	// 测试数据
	param := &model.PlatformAPIParam{
		APIID:       1,
		Name:        "测试参数",
		Code:        "test_param",
		Value:       "test_value",
		Description: "测试描述",
		Cost:        10.5,
		MinCost:     10.0,
		MaxCost:     11.0,
		Status:      1,
	}

	// 测试创建
	t.Run("CreateParam", func(t *testing.T) {
		router.POST("/params", controller.CreateParam)
		jsonData, _ := json.Marshal(param)
		req, _ := http.NewRequest("POST", "/params", bytes.NewBuffer(jsonData))
		req.Header.Set("Content-Type", "application/json")
		w := httptest.NewRecorder()
		router.ServeHTTP(w, req)

		assert.Equal(t, http.StatusOK, w.Code)
	})

	// 测试更新
	t.Run("UpdateParam", func(t *testing.T) {
		router.PUT("/params/:id", controller.UpdateParam)
		param.ID = 1
		jsonData, _ := json.Marshal(param)
		req, _ := http.NewRequest("PUT", "/params/1", bytes.NewBuffer(jsonData))
		req.Header.Set("Content-Type", "application/json")
		w := httptest.NewRecorder()
		router.ServeHTTP(w, req)

		assert.Equal(t, http.StatusOK, w.Code)
	})

	// 测试删除
	t.Run("DeleteParam", func(t *testing.T) {
		router.DELETE("/params/:id", controller.DeleteParam)
		req, _ := http.NewRequest("DELETE", "/params/1", nil)
		w := httptest.NewRecorder()
		router.ServeHTTP(w, req)

		assert.Equal(t, http.StatusOK, w.Code)
	})

	// 测试获取
	t.Run("GetParam", func(t *testing.T) {
		router.GET("/params/:id", controller.GetParam)
		req, _ := http.NewRequest("GET", "/params/1", nil)
		w := httptest.NewRecorder()
		router.ServeHTTP(w, req)

		assert.Equal(t, http.StatusOK, w.Code)
	})

	// 测试列表
	t.Run("ListParams", func(t *testing.T) {
		router.GET("/params", controller.ListParams)
		req, _ := http.NewRequest("GET", "/params?api_id=1&page=1&page_size=10", nil)
		w := httptest.NewRecorder()
		router.ServeHTTP(w, req)

		assert.Equal(t, http.StatusOK, w.Code)
	})
}
