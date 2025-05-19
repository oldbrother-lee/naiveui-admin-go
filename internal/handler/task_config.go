package handler

import (
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/internal/utils/response"

	"github.com/gin-gonic/gin"
)

type TaskConfigHandler struct {
	taskConfigRepo *repository.TaskConfigRepository
}

func NewTaskConfigHandler(taskConfigRepo *repository.TaskConfigRepository) *TaskConfigHandler {
	return &TaskConfigHandler{
		taskConfigRepo: taskConfigRepo,
	}
}

// Create 创建取单任务配置
func (h *TaskConfigHandler) Create(c *gin.Context) {
	var config model.TaskConfig
	if err := c.ShouldBindJSON(&config); err != nil {
		response.Error(c, response.ErrInvalidParams)
		return
	}

	if err := h.taskConfigRepo.Create(&config); err != nil {
		response.Error(c, response.ErrInternalServer)
		return
	}

	response.Success(c, config)
}

// Update 更新取单任务配置
func (h *TaskConfigHandler) Update(c *gin.Context) {
	var config model.TaskConfig
	if err := c.ShouldBindJSON(&config); err != nil {
		response.Error(c, response.ErrInvalidParams)
		return
	}

	if err := h.taskConfigRepo.Update(&config); err != nil {
		response.Error(c, response.ErrInternalServer)
		return
	}

	response.Success(c, config)
}

// Delete 删除取单任务配置
func (h *TaskConfigHandler) Delete(c *gin.Context) {
	id := c.Param("id")
	if err := h.taskConfigRepo.Delete(id); err != nil {
		response.Error(c, response.ErrInternalServer)
		return
	}

	response.Success(c, nil)
}

// Get 获取取单任务配置
func (h *TaskConfigHandler) Get(c *gin.Context) {
	id := c.Param("id")
	config, err := h.taskConfigRepo.GetByID(id)
	if err != nil {
		response.Error(c, response.ErrInternalServer)
		return
	}

	response.Success(c, config)
}

// List 获取取单任务配置列表
func (h *TaskConfigHandler) List(c *gin.Context) {
	configs, err := h.taskConfigRepo.List()
	if err != nil {
		response.Error(c, response.ErrInternalServer)
		return
	}

	response.Success(c, configs)
}
