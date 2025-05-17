package logger

import (
	"context"
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

	// 创建编码器配置
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

	// 创建控制台输出
	consoleEncoder := zapcore.NewConsoleEncoder(encoderConfig)
	consoleCore := zapcore.NewCore(
		consoleEncoder,
		zapcore.AddSync(os.Stdout),
		zapcore.DebugLevel,
	)

	// 创建文件输出
	fileEncoder := zapcore.NewJSONEncoder(encoderConfig)
	fileCore := zapcore.NewCore(
		fileEncoder,
		zapcore.AddSync(logFile),
		zapcore.DebugLevel,
	)

	// 创建日志记录器
	core := zapcore.NewTee(consoleCore, fileCore)
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

// Info 记录信息日志
func Info(msg string, fields ...interface{}) {
	Log.Info(msg, parseFields(fields...)...)
}

// Error 记录错误日志
func Error(msg string, fields ...interface{}) {
	Log.Error(msg, parseFields(fields...)...)
}

// Debug 记录调试日志
func Debug(msg string, fields ...interface{}) {
	Log.Debug(msg, parseFields(fields...)...)
}

// Warn 记录警告日志
func Warn(msg string, fields ...interface{}) {
	Log.Warn(msg, parseFields(fields...)...)
}

// parseFields 解析字段
func parseFields(fields ...interface{}) []zap.Field {
	if len(fields) == 0 {
		return nil
	}

	zapFields := make([]zap.Field, 0, len(fields)/2)
	for i := 0; i < len(fields); i += 2 {
		if i+1 < len(fields) {
			key, ok := fields[i].(string)
			if !ok {
				continue
			}
			zapFields = append(zapFields, zap.Any(key, fields[i+1]))
		}
	}
	return zapFields
}

// WithContext 添加上下文信息
func WithContext(ctx context.Context) *zap.Logger {
	return Log.With(
		zap.String("trace_id", ctx.Value("trace_id").(string)),
		zap.String("user_id", ctx.Value("user_id").(string)),
	)
}
