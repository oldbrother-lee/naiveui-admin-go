package handler

import (
	"recharge-go/internal/service/platform"
	"recharge-go/pkg/response"

	"github.com/gin-gonic/gin"
)

type PlatformHandler struct {
	platformSvc *platform.Service
}

func NewPlatformHandler(platformSvc *platform.Service) *PlatformHandler {
	return &PlatformHandler{
		platformSvc: platformSvc,
	}
}

// GetChannelList 获取渠道列表
// @Summary 获取渠道列表
// @Description 获取所有渠道及对应运营商编码
// @Tags 平台接口
// @Accept json
// @Produce json
// @Success 200 {object} response.Response{data=[]platform.Channel}
// @Router /api/platform/channels [get]
func (h *PlatformHandler) GetChannelList(c *gin.Context) {
	channels, err := h.platformSvc.GetChannelList()
	if err != nil {
		response.Error(c, err)
		return
	}
	response.Success(c, channels)
}
