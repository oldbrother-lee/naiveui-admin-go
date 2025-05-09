package signature

import (
	"crypto/md5"
	"encoding/hex"
	"fmt"
	"math"
	"sort"
	"strconv"
	"strings"
	"time"
)

// GenerateSign 生成签名
func GenerateSign(params map[string]interface{}, appSecret string) string {
	// 1. 过滤掉 sign 字段
	delete(params, "sign")
	fmt.Println("vvvvvvvvvvvvvvvvvvvvv", params)
	// 2. 将参数按照 key 的字典序排序
	keys := make([]string, 0, len(params))
	for k := range params {
		if k != "sign" && k != "datas" {
			keys = append(keys, k)
		}
	}
	sort.Strings(keys)

	// 3. 构建签名字符串
	var signStr strings.Builder
	for _, k := range keys {
		v := params[k]
		switch val := v.(type) {
		case string:
			signStr.WriteString(k + val)
		case float64:
			strVal := strconv.FormatFloat(val, 'f', -1, 64)
			signStr.WriteString(k + strVal)
		case int:
			strVal := strconv.Itoa(val)
			signStr.WriteString(k + strVal)
		case int64:
			strVal := strconv.FormatInt(val, 10)
			signStr.WriteString(k + strVal)
		case bool:
			strVal := strconv.FormatBool(val)
			signStr.WriteString(k + strVal)
		case map[string]interface{}:
			signStr.WriteString("dataArray")

		default:
			strVal := fmt.Sprintf("%v", val)
			signStr.WriteString(k + strVal)
		}
	}

	// 4. 添加 app_secret
	signStr.WriteString(appSecret)
	fmt.Println("signStr.String()-------", appSecret)
	// 5. MD5加密
	h := md5.New()
	h.Write([]byte(signStr.String()))
	return hex.EncodeToString(h.Sum(nil))
}

// VerifySign 验证签名
func VerifySign(params map[string]interface{}, sign string, appSecret string) bool {
	calculatedSign := GenerateSign(params, appSecret)
	return calculatedSign == sign
}

// VerifyTimestamp 验证时间戳
func VerifyTimestamp(timestamp float64, maxDiffSeconds float64) bool {
	now := time.Now().Unix()
	return maxDiffSeconds > math.Abs(float64(now)-timestamp)
}
