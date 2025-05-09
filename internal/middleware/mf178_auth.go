package middleware

import (
	"crypto/md5"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"io"
	"math"
	"net/http"
	"sort"
	"strings"
	"time"

	"recharge-go/pkg/signature"

	"github.com/gin-gonic/gin"
)

// MF178Auth MF178认证中间件
func MF178Auth() gin.HandlerFunc {
	return func(c *gin.Context) {
		// 读取请求体
		body, err := io.ReadAll(c.Request.Body)
		if err != nil {
			fmt.Printf("读取请求体失败: %v\n", err)
			response := gin.H{
				"code":    "FAIL",
				"message": "读取请求体失败",
				"data":    gin.H{},
			}
			c.JSON(http.StatusOK, response)
			c.Abort()
			return
		}
		// 恢复请求体，因为后续还需要使用
		c.Request.Body = io.NopCloser(strings.NewReader(string(body)))
		fmt.Printf("收到请求体: %s\n", string(body))

		// 解析请求体
		var req map[string]interface{}
		if err := json.Unmarshal(body, &req); err != nil {
			fmt.Printf("解析请求体失败: %v\n", err)
			response := gin.H{
				"code":    "FAIL",
				"message": "解析请求体失败",
				"data":    gin.H{},
			}
			c.JSON(http.StatusOK, response)
			c.Abort()
			return
		}

		// 从请求体中获取签名
		sign, ok := req["sign"].(string)
		if !ok || sign == "" {
			fmt.Printf("签名不能为空, 请求体: %+v\n", req)
			response := gin.H{
				"code":    "FAIL",
				"message": "签名不能为空",
				"data":    gin.H{},
			}
			c.JSON(http.StatusOK, response)
			c.Abort()
			return
		}
		// 获取时间戳
		timestamp, ok := req["timestamp"].(float64)
		if !ok {
			fmt.Printf("时间戳不能为空, 请求体: %+v\n", req)
			response := gin.H{
				"code":    "FAIL",
				"message": "时间戳不能为空",
				"data":    gin.H{},
			}
			c.JSON(http.StatusOK, response)
			c.Abort()
			return
		}

		// 验证签名
		if !signature.VerifySign(req, sign, getAppSecret("1675958551")) {
			fmt.Printf("签名验证失败, 请求体: %+v\n", req)
			response := gin.H{
				"code":    "FAIL",
				"message": "签名验证失败",
				"data":    gin.H{},
			}
			c.JSON(http.StatusOK, response)
			c.Abort()
			return
		}

		// 验证时间戳
		if !signature.VerifyTimestamp(timestamp, 300) { // 5分钟有效期
			fmt.Printf("验证签名失败: 时间戳过期, timestamp: %v, now: %v, diff: %v秒\n",
				timestamp, time.Now().Unix(), math.Abs(float64(time.Now().Unix())-timestamp))
			response := gin.H{
				"code":    "FAIL",
				"message": "签名验证失败: 时间戳过期",
				"data":    gin.H{},
			}
			c.JSON(http.StatusOK, response)
			c.Abort()
			return
		}

		c.Next()
	}
}

// verifySign 验证签名
func verifySign(params map[string]interface{}, sign string) bool {
	// 1. 获取 app_key
	appKey, ok := params["app_key"].(string)
	if !ok {
		fmt.Printf("验证签名失败: app_key 类型错误或不存在, params: %+v\n", params)
		return false
	}

	// 2. 获取 timestamp 并验证时间戳
	timestamp, ok := params["timestamp"].(float64)
	if !ok {
		fmt.Printf("验证签名失败: timestamp 类型错误或不存在, params: %+v\n", params)
		return false
	}

	// 验证时间戳是否在有效期内（比如5分钟内）
	now := time.Now().Unix()
	if math.Abs(float64(now)-timestamp) > 300 { // 300秒 = 5分钟
		fmt.Printf("验证签名失败: 时间戳过期, timestamp: %v, now: %v, diff: %v秒\n",
			timestamp, now, math.Abs(float64(now)-timestamp))
		return false
	}

	// 3. 获取 app_secret (从配置中获取)
	appSecret := getAppSecret(appKey)
	if appSecret == "" {
		fmt.Printf("验证签名失败: 未找到对应的 app_secret, app_key: %s\n", appKey)
		return false
	}

	// 4. 构建签名字符串
	signStr := buildSignString(params, appSecret)
	fmt.Printf("签名字符串: %s\n", signStr)

	// 5. 计算签名
	calculatedSign := calculateSign(signStr)
	fmt.Printf("计算的签名: %s, 接收到的签名: %s\n", calculatedSign, sign)

	// 6. 比对签名
	if calculatedSign != sign {
		fmt.Printf("验证签名失败: 签名不匹配\n计算签名: %s\n接收签名: %s\n", calculatedSign, sign)
		return false
	}

	return true
}

// buildSignString 构建签名字符串
func buildSignString(params map[string]interface{}, appSecret string) string {
	// 1. 过滤掉 sign 字段

	keys := make([]string, 0, len(params))
	for k := range params {
		if k != "sign" && k != "datas" {
			keys = append(keys, k)
		}
	}
	sort.Strings(keys)

	// 3. 构建签名字符串
	var signString strings.Builder
	for _, k := range keys {
		v := params[k]
		// 将键和值都转换为字符串并拼接
		signString.WriteString(k)
		signString.WriteString(fmt.Sprint(v))
	}

	// 4. 添加 appSecret
	signString.WriteString(appSecret)

	return signString.String()
}

// calculateSign 计算签名
func calculateSign(signStr string) string {
	h := md5.New()
	h.Write([]byte(signStr))
	return hex.EncodeToString(h.Sum(nil))
}

// getAppSecret 获取 app_secret
func getAppSecret(appKey string) string {
	// TODO: 从配置或数据库中获取 app_secret
	appSecrets := map[string]string{
		"1675958551": "3649c621b6945721", // 测试用例中的 app_secret
	}
	return appSecrets[appKey]
}
