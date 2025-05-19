package repository

import (
	"recharge-go/internal/model"
	"recharge-go/pkg/database"

	"gorm.io/gorm"
)

type TaskConfigRepository struct {
	db *gorm.DB
}

func NewTaskConfigRepository() *TaskConfigRepository {
	return &TaskConfigRepository{
		db: database.DB,
	}
}

// Create 创建取单任务配置
func (r *TaskConfigRepository) Create(config *model.TaskConfig) error {
	return r.db.Create(config).Error
}

// Update 更新取单任务配置
func (r *TaskConfigRepository) Update(config *model.TaskConfig) error {
	return r.db.Save(config).Error
}

// Delete 删除取单任务配置
func (r *TaskConfigRepository) Delete(id string) error {
	return r.db.Delete(&model.TaskConfig{}, id).Error
}

// GetByID 根据ID获取取单任务配置
func (r *TaskConfigRepository) GetByID(id string) (*model.TaskConfig, error) {
	var config model.TaskConfig
	err := r.db.First(&config, id).Error
	if err != nil {
		return nil, err
	}
	return &config, nil
}

// List 获取取单任务配置列表
func (r *TaskConfigRepository) List() ([]model.TaskConfig, error) {
	var configs []model.TaskConfig
	err := r.db.Find(&configs).Error
	if err != nil {
		return nil, err
	}
	return configs, nil
}

// GetEnabledConfigs 获取所有启用的取单任务配置
func (r *TaskConfigRepository) GetEnabledConfigs() ([]model.TaskConfig, error) {
	var configs []model.TaskConfig
	err := r.db.Where("status = ?", 1).Find(&configs).Error
	if err != nil {
		return nil, err
	}
	return configs, nil
}
