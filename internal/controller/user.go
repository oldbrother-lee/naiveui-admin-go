package controller

import (
	"recharge-go/internal/model"
	"recharge-go/internal/service"
	"recharge-go/internal/utils"

	"strconv"

	"github.com/gin-gonic/gin"
)

type UserController struct {
	userService *service.UserService
}

func NewUserController(userService *service.UserService) *UserController {
	return &UserController{userService: userService}
}

func (c *UserController) Register(ctx *gin.Context) {
	var req model.UserRegisterRequest
	if err := ctx.ShouldBindJSON(&req); err != nil {
		utils.Error(ctx, 400, err.Error())
		return
	}

	if err := c.userService.Register(&req); err != nil {
		utils.Error(ctx, 500, err.Error())
		return
	}

	utils.Success(ctx, nil)
}

func (c *UserController) Login(ctx *gin.Context) {
	var req model.UserLoginRequest
	if err := ctx.ShouldBindJSON(&req); err != nil {
		utils.Error(ctx, 400, err.Error())
		return
	}

	resp, err := c.userService.Login(&req)
	if err != nil {
		utils.Error(ctx, 401, err.Error())
		return
	}

	utils.Success(ctx, resp)
}

func (c *UserController) UpdateProfile(ctx *gin.Context) {
	userID := ctx.GetInt64("user_id")
	var req model.UserUpdateRequest
	if err := ctx.ShouldBindJSON(&req); err != nil {
		utils.Error(ctx, 400, err.Error())
		return
	}

	if err := c.userService.UpdateProfile(userID, &req); err != nil {
		utils.Error(ctx, 500, err.Error())
		return
	}

	utils.Success(ctx, nil)
}

func (c *UserController) ChangePassword(ctx *gin.Context) {
	userID := ctx.GetInt64("user_id")
	var req model.UserChangePasswordRequest
	if err := ctx.ShouldBindJSON(&req); err != nil {
		utils.Error(ctx, 400, err.Error())
		return
	}

	if err := c.userService.ChangePassword(userID, &req); err != nil {
		utils.Error(ctx, 500, err.Error())
		return
	}

	utils.Success(ctx, nil)
}

func (c *UserController) GetProfile(ctx *gin.Context) {
	userID := ctx.GetInt64("user_id")
	user, err := c.userService.GetUserProfile(userID)
	if err != nil {
		utils.Error(ctx, 500, err.Error())
		return
	}

	utils.Success(ctx, user)
}

func (c *UserController) ListUsers(ctx *gin.Context) {
	current, _ := strconv.Atoi(ctx.DefaultQuery("current", "1"))
	size, _ := strconv.Atoi(ctx.DefaultQuery("size", "10"))

	users, total, err := c.userService.ListUsers(current, size)
	if err != nil {
		utils.Error(ctx, 500, err.Error())
		return
	}

	utils.Success(ctx, gin.H{
		"records": users,
		"total":   total,
		"current": current,
		"size":    size,
	})
}
