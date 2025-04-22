package service

import (
	"errors"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/internal/utils"
	"strconv"

	"golang.org/x/crypto/bcrypt"
)

type UserService struct {
	userRepo *repository.UserRepository
}
type UserLoginResponse struct {
	Token        string   `json:"token"`
	RefreshToken string   `json:"refreshToken"`
	UserInfo     UserInfo `json:"userInfo"`
}

type UserInfo struct {
	UserId   string   `json:"userId"`
	UserName string   `json:"userName"`
	Roles    []string `json:"roles"`
	Buttons  []string `json:"buttons"`
}

func NewUserService(userRepo *repository.UserRepository) *UserService {
	return &UserService{userRepo: userRepo}
}

func (s *UserService) Register(req *model.UserRegisterRequest) error {
	// Check if username exists
	_, err := s.userRepo.GetByUsername(req.Username)
	if err == nil {
		return errors.New("username already exists")
	}

	// Hash password
	hashedPassword, err := bcrypt.GenerateFromPassword([]byte(req.Password), bcrypt.DefaultCost)
	if err != nil {
		return err
	}

	user := &model.User{
		Username: req.Username,
		Password: string(hashedPassword),
		Nickname: req.Nickname,
		Email:    req.Email,
		Phone:    req.Phone,
		Status:   1,
	}

	return s.userRepo.Create(user)
}

func (s *UserService) Login(req *model.UserLoginRequest) (*model.UserLoginResponse, error) {
	user, err := s.userRepo.GetByUsername(req.Username)
	if err != nil {
		return nil, errors.New("invalid username or password")
	}

	// Check password
	err = bcrypt.CompareHashAndPassword([]byte(user.Password), []byte(req.Password))
	if err != nil {
		return nil, errors.New("invalid username or password")
	}

	// Generate JWT token
	token, err := utils.GenerateJWT(user.ID, user.Username)
	if err != nil {
		return nil, err
	}

	// Get user roles
	userWithRoles, err := s.userRepo.GetUserWithRoles(user.ID)
	if err != nil {
		return nil, err
	}

	// Convert roles to string array
	roles := make([]string, 0)
	for _, role := range userWithRoles.Roles {
		roles = append(roles, role.Code)
	}

	return &model.UserLoginResponse{
		Token:        token,
		RefreshToken: token, // 暂时使用相同的 token
		UserInfo: model.UserInfo{
			UserId:   strconv.FormatInt(user.ID, 10),
			Username: user.Username,
			Roles:    roles,
			Buttons:  []string{}, // 暂时返回空数组
		},
	}, nil
}

func (s *UserService) UpdateProfile(userID int64, req *model.UserUpdateRequest) error {
	user, err := s.userRepo.GetByID(userID)
	if err != nil {
		return err
	}

	if req.Nickname != nil {
		user.Nickname = req.Nickname
	}
	if req.Email != nil {
		user.Email = req.Email
	}
	if req.Phone != nil {
		user.Phone = req.Phone
	}
	if req.Avatar != nil {
		user.Avatar = req.Avatar
	}

	return s.userRepo.Update(user)
}

func (s *UserService) ChangePassword(userID int64, req *model.UserChangePasswordRequest) error {
	user, err := s.userRepo.GetByID(userID)
	if err != nil {
		return err
	}

	// Verify old password
	err = bcrypt.CompareHashAndPassword([]byte(user.Password), []byte(req.OldPassword))
	if err != nil {
		return errors.New("invalid old password")
	}

	// Hash new password
	hashedPassword, err := bcrypt.GenerateFromPassword([]byte(req.NewPassword), bcrypt.DefaultCost)
	if err != nil {
		return err
	}

	user.Password = string(hashedPassword)
	return s.userRepo.Update(user)
}

func (s *UserService) GetUserProfile(userID int64) (*model.User, error) {
	return s.userRepo.GetByID(userID)
}

func (s *UserService) ListUsers(page, pageSize int) ([]model.User, int64, error) {
	return s.userRepo.List(page, pageSize, "", "", "", nil)
}

func (s *UserService) GetUserWithRoles(userID int64) (*model.UserWithRoles, error) {
	return s.userRepo.GetUserWithRoles(userID)
}

func (s *UserService) GetUserList(Page, PageSize int, userName, phone, email string, status *int) ([]*model.User, int64, error) {
	users, total, err := s.userRepo.List(Page, PageSize, userName, phone, email, status)
	if err != nil {
		return nil, 0, err
	}

	// 转换为指针类型
	userPtrs := make([]*model.User, len(users))
	for i := range users {
		userPtrs[i] = &users[i]
	}

	return userPtrs, total, nil
}
