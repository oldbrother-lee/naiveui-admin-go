package handler

import (
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/internal/utils"

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
		utils.Error(c, 400, "Invalid parameters")
		return
	}

	if err := h.taskConfigRepo.Create(&config); err != nil {
		utils.Error(c, 500, "Internal server error")
		return
	}

	utils.Success(c, config)
}

// Update 更新取单任务配置
func (h *TaskConfigHandler) Update(c *gin.Context) {
	var config model.TaskConfig
	if err := c.ShouldBindJSON(&config); err != nil {
		utils.Error(c, 400, "Invalid parameters")
		return
	}

	if err := h.taskConfigRepo.Update(&config); err != nil {
		utils.Error(c, 500, "Internal server error")
		return
	}

	utils.Success(c, config)
}

// Delete 删除取单任务配置
func (h *TaskConfigHandler) Delete(c *gin.Context) {
	id := c.Param("id")
	if err := h.taskConfigRepo.Delete(id); err != nil {
		utils.Error(c, 500, "Internal server error")
		return
	}

	utils.Success(c, nil)
}

// Get 获取取单任务配置
func (h *TaskConfigHandler) Get(c *gin.Context) {
	id := c.Param("id")
	config, err := h.taskConfigRepo.GetByID(id)
	if err != nil {
		utils.Error(c, 500, "Internal server error")
		return
	}

	utils.Success(c, config)
}

// List 获取取单任务配置列表
func (h *TaskConfigHandler) List(c *gin.Context) {
	configs, err := h.taskConfigRepo.List()
	if err != nil {
		utils.Error(c, 500, "Internal server error")
		return
	}

	utils.Success(c, configs)
}
