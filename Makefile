.PHONY: build-worker run-worker

# 构建工作器
build-worker:
	go build -o bin/worker cmd/worker/main.go

# 运行工作器
run-worker:
	go run cmd/worker/main.go
