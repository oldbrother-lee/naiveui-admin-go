package logger

import (
	"fmt"
	"os"
	"path/filepath"

	"go.uber.org/zap"
	"go.uber.org/zap/zapcore"
	"gopkg.in/natefinch/lumberjack.v2"
)

var (
	Log *zap.Logger
)

// InitLogger 初始化日志
func InitLogger() error {
	// 创建日志目录
	logDir := "logs"
	if err := os.MkdirAll(logDir, 0755); err != nil {
		return err
	}

	// 配置日志轮转
	logFile := &lumberjack.Logger{
		Filename:   filepath.Join(logDir, "app.log"),
		MaxSize:    100,  // 单个文件最大100MB
		MaxBackups: 30,   // 保留30个备份
		MaxAge:     30,   // 保留30天
		Compress:   true, // 压缩旧文件
	}

	// 配置编码器
	encoderConfig := zapcore.EncoderConfig{
		TimeKey:        "time",
		LevelKey:       "level",
		NameKey:        "logger",
		CallerKey:      "caller",
		MessageKey:     "msg",
		StacktraceKey:  "stacktrace",
		LineEnding:     zapcore.DefaultLineEnding,
		EncodeLevel:    zapcore.CapitalLevelEncoder,
		EncodeTime:     zapcore.ISO8601TimeEncoder,
		EncodeDuration: zapcore.SecondsDurationEncoder,
		EncodeCaller:   zapcore.ShortCallerEncoder,
	}

	// 创建核心
	core := zapcore.NewCore(
		zapcore.NewJSONEncoder(encoderConfig),
		zapcore.NewMultiWriteSyncer(zapcore.AddSync(logFile), zapcore.AddSync(os.Stdout)),
		zapcore.InfoLevel,
	)

	// 创建logger
	Log = zap.New(core, zap.AddCaller(), zap.AddStacktrace(zapcore.ErrorLevel))
	return nil
}

// Close 关闭日志
func Close() {
	if Log != nil {
		Log.Sync()
	}
}

// 添加一个默认的 logger，防止空指针
func init() {
	// 创建一个基本的控制台 logger
	config := zap.NewDevelopmentConfig()
	config.EncoderConfig.EncodeLevel = zapcore.CapitalColorLevelEncoder
	logger, err := config.Build()
	if err != nil {
		// 如果创建失败，使用一个 no-op logger
		Log = zap.NewNop()
	} else {
		Log = logger
	}
}

// 在 logger 包中添加便捷方法
func Info(msg string, fields ...interface{}) {
	Log.Info(fmt.Sprintf(msg, fields...))
}

func Error(msg string, fields ...interface{}) {
	Log.Error(fmt.Sprintf(msg, fields...))
}
