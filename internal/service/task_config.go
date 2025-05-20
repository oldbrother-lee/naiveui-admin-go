package service

import (
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
)

type TaskConfigService struct {
	repo *repository.TaskConfigRepository
}

func NewTaskConfigService(repo *repository.TaskConfigRepository) *TaskConfigService {
	return &TaskConfigService{repo: repo}
}

type Product struct {
	ProductID int `json:"productId"`
}

type TaskConfigPayload struct {
	ChannelID        int    `json:"channelId"`
	FaceValues       string `json:"FaceValues"`
	MinSettleAmounts string `json:"MinSettleAmounts"`
	ProductID        int    `json:"ProductID"`
}

func (s *TaskConfigService) BatchCreate(payloads []TaskConfigPayload) error {
	for _, p := range payloads {
		config := &model.TaskConfig{
			ChannelID:        p.ChannelID,
			ProductID:        p.ProductID,
			FaceValues:       p.FaceValues,
			MinSettleAmounts: p.MinSettleAmounts,
			Status:           1,
		}
		if err := s.repo.Create(config); err != nil {
			return err
		}
	}
	return nil
}
