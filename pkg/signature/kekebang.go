package signature

import (
	"crypto/md5"
	"encoding/hex"
	"fmt"
	"sort"
	"strconv"
	"strings"
	"time"
)

// GenerateKekebangSign 生成客帮帮平台签名
func GenerateKekebangSign(params map[string]interface{}, secretKey string) string {
	// 创建用于签名的参数集合（排除data字段）
	signParams := make(map[string]string)

	// 验证params下的time字段是否为时间戳，不是时间戳则转换成时间戳
	timeStr, ok := params["time"].(string)
	if !ok {
		timeStr = fmt.Sprintf("%d", time.Now().Unix())
	} else {
		// 尝试将字符串转换为时间戳
		if _, err := strconv.ParseInt(timeStr, 10, 64); err != nil {
			// 如果不是时间戳，则转换为时间戳
			timeStr = fmt.Sprintf("%d", time.Now().Unix())
		}
	}
	fmt.Println("time:xxxxxx", timeStr)
	params["timestamp"] = timeStr
	for k, v := range params {
		if k == "data" {
			continue // 跳过data字段
		}
		//过滤空值
		if v == nil || v == "" || k == "sign" {
			fmt.Printf("k%s v%s:xxxxxx\n", k, v)
			continue
		}
		// 类型转换
		switch val := v.(type) {
		case string:
			signParams[k] = val
		default:
			signParams[k] = fmt.Sprintf("%v", val)
		}
	}

	// 对签名参数进行首字母升序排序
	keys := make([]string, 0, len(signParams))
	for k := range signParams {
		keys = append(keys, k)
	}
	sort.Strings(keys)

	// 构建键值对列表
	var keyValueList []string
	for _, k := range keys {
		keyValueList = append(keyValueList, k+"="+signParams[k])
	}

	// 拼接签名明文
	plainText := strings.Join(keyValueList, "&")
	plainText += "&secret=" + secretKey
	fmt.Println("MD5签名前串:", plainText)
	// 计算MD5签名
	hasher := md5.New()
	hasher.Write([]byte(plainText))
	return hex.EncodeToString(hasher.Sum(nil))
}

// VerifyKekebangSign 验证客帮帮平台签名
func VerifyKekebangSign(params map[string]interface{}, sign string, secretKey string) bool {
	return GenerateKekebangSign(params, secretKey) == sign
}
