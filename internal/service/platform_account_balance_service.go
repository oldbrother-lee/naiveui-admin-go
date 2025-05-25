package service

import (
	"context"
	"errors"
	"recharge-go/internal/model"
	"recharge-go/internal/repository"
	"recharge-go/pkg/logger"
	"time"

	"gorm.io/gorm"
)

// PlatformAccountBalanceService 平台账号余额服务
type PlatformAccountBalanceService struct {
	db                  *gorm.DB
	platformAccountRepo *repository.PlatformAccountRepository
	userRepo            *repository.UserRepository
}

// NewPlatformAccountBalanceService 创建平台账号余额服务实例
func NewPlatformAccountBalanceService(
	db *gorm.DB,
	platformAccountRepo *repository.PlatformAccountRepository,
	userRepo *repository.UserRepository,
) *PlatformAccountBalanceService {
	return &PlatformAccountBalanceService{
		db:                  db,
		platformAccountRepo: platformAccountRepo,
		userRepo:            userRepo,
	}
}

// DeductBalance 扣除余额
func (s *PlatformAccountBalanceService) DeductBalance(ctx context.Context, accountID int64, amount float64, orderID int64, remark string) error {
	logger.Info("开始扣除本地账号余额",
		"platform_account_id", accountID,
		"amount", amount,
		"order_id", orderID,
		"remark", remark)

	// 1. 获取平台账号信息
	account, err := s.platformAccountRepo.GetByID(accountID)
	if err != nil {
		logger.Error("获取平台账号信息失败", "error", err, "account_id", accountID)
		return err
	}

	// 2. 获取本地用户账号（通过 bind_user_id 字段）
	if account.BindUserID == nil {
		logger.Error("平台账号未绑定本地用户", "account_id", accountID)
		return errors.New("平台账号未绑定本地用户")
	}
	userID := *account.BindUserID
	user, err := s.userRepo.GetByID(ctx, userID)
	if err != nil {
		logger.Error("获取本地用户账号失败", "error", err, "user_id", userID)
		return err
	}

	// 3. 检查余额
	if user.Balance < amount {
		logger.Error("本地用户余额不足", "user_id", userID, "current_balance", user.Balance, "required_amount", amount)
		return errors.New("本地用户余额不足")
	}

	// 4. 扣减余额
	before := user.Balance
	user.Balance -= amount
	if err := s.userRepo.Update(ctx, user); err != nil {
		logger.Error("更新本地用户余额失败", "error", err, "user_id", userID)
		return err
	}

	// 5. 写用户余额变动日志
	userLog := &model.BalanceLog{
		UserID:            userID,
		OrderID:           orderID,
		PlatformAccountID: accountID,
		PlatformID:        account.PlatformID,
		PlatformCode:      account.Platform.Code,
		PlatformName:      account.Platform.Name,
		Amount:            -amount,
		Type:              model.BalanceTypeExpense,
		Style:             model.BalanceStyleOrderDeduct,
		Balance:           user.Balance,
		BalanceBefore:     before,
		Remark:            remark,
		Operator:          "system",
		CreatedAt:         time.Now(),
	}
	if err := s.userRepo.DB().WithContext(ctx).Create(userLog).Error; err != nil {
		logger.Error("创建用户余额变动日志失败", "error", err, "user_id", userID)
		return err
	}

	logger.Info("扣除本地账号余额成功", "user_id", userID, "amount", amount, "balance_before", before, "balance_after", user.Balance)
	return nil
}

// RefundBalance 退还余额，支持外部事务
func (s *PlatformAccountBalanceService) RefundBalance(ctx context.Context, tx *gorm.DB, accountID int64, amount float64, orderID int64, remark string) error {
	logger.Info("[RefundBalance] 开始退还余额",
		"account_id", accountID,
		"amount", amount,
		"order_id", orderID,
		"remark", remark)

	// 如果没有传入事务，则新建事务
	newTx := false
	if tx == nil {
		tx = s.db.Begin()
		if tx.Error != nil {
			logger.Error("[RefundBalance] 开启事务失败",
				"error", tx.Error,
				"account_id", accountID)
			return tx.Error
		}
		newTx = true
	}
	defer func() {
		if r := recover(); r != nil {
			tx.Rollback()
			logger.Error("[RefundBalance] panic, 事务回滚", "panic", r)
		}
	}()

	// 1. 获取平台账号信息
	account, err := s.platformAccountRepo.GetByID(accountID)
	if err != nil {
		if newTx {
			tx.Rollback()
		}
		logger.Error("[RefundBalance] 获取平台账号信息失败",
			"error", err,
			"account_id", accountID)
		return err
	}
	logger.Info("[RefundBalance] 获取平台账号信息成功", "account", account)

	// 2. 获取本地用户账号（通过 bind_user_id 字段）
	if account.BindUserID == nil {
		if newTx {
			tx.Rollback()
		}
		logger.Error("[RefundBalance] 平台账号未绑定本地用户", "account_id", accountID)
		return errors.New("平台账号未绑定本地用户")
	}
	userID := *account.BindUserID
	var user model.User
	if err := tx.Where("id = ?", userID).First(&user).Error; err != nil {
		if newTx {
			tx.Rollback()
		}
		logger.Error("[RefundBalance] 获取本地用户账号失败", "error", err, "user_id", userID)
		return err
	}
	logger.Info("[RefundBalance] 获取本地用户账号成功", "user_id", userID, "balance_before", user.Balance)

	// 3. 退还余额到本地用户账号
	before := user.Balance
	user.Balance += amount
	if err := tx.Model(&model.User{}).Where("id = ?", userID).Update("balance", user.Balance).Error; err != nil {
		if newTx {
			tx.Rollback()
		}
		logger.Error("[RefundBalance] 更新本地用户余额失败",
			"error", err,
			"user_id", userID,
			"balance_before", before,
			"balance_after", user.Balance)
		return err
	}
	logger.Info("[RefundBalance] 更新本地用户余额成功", "user_id", userID, "balance_before", before, "balance_after", user.Balance)

	// 4. 写用户余额变动日志
	userLog := &model.BalanceLog{
		UserID:            userID,
		OrderID:           orderID,
		PlatformAccountID: accountID,
		PlatformID:        account.PlatformID,
		PlatformCode:      account.Platform.Code,
		PlatformName:      account.Platform.Name,
		Amount:            amount,
		Type:              model.BalanceTypeIncome,
		Style:             model.BalanceStyleRefund,
		Balance:           user.Balance,
		BalanceBefore:     before,
		Remark:            remark,
		Operator:          "system",
		CreatedAt:         time.Now(),
	}
	if err := tx.Create(userLog).Error; err != nil {
		if newTx {
			tx.Rollback()
		}
		logger.Error("[RefundBalance] 创建用户余额变动日志失败",
			"error", err,
			"user_id", userID)
		return err
	}
	logger.Info("[RefundBalance] 创建用户余额变动日志成功", "user_id", userID, "log_id", userLog.ID)

	// 提交事务（仅当本方法新建事务时）
	if newTx {
		if err := tx.Commit().Error; err != nil {
			logger.Error("[RefundBalance] 提交事务失败",
				"error", err,
				"account_id", accountID)
			return err
		}
	}

	logger.Info("[RefundBalance] 退还余额成功",
		"user_id", userID,
		"amount", amount,
		"balance_before", before,
		"balance_after", user.Balance)
	return nil
}

// GetBalanceLogs 获取余额变动记录
func (s *PlatformAccountBalanceService) GetBalanceLogs(ctx context.Context, accountID int64, offset, limit int) ([]*model.BalanceLog, int64, error) {
	// 1. 获取平台账号信息
	account, err := s.platformAccountRepo.GetByID(accountID)
	if err != nil {
		return nil, 0, err
	}

	// 2. 获取本地用户账号（通过 bind_user_id 字段）
	if account.BindUserID == nil {
		return nil, 0, errors.New("平台账号未绑定本地用户")
	}
	userID := *account.BindUserID

	// 3. 查询用户余额变动记录
	var logs []*model.BalanceLog
	var total int64

	if err := s.db.Model(&model.BalanceLog{}).
		Where("user_id = ?", userID).
		Count(&total).Error; err != nil {
		return nil, 0, err
	}

	if err := s.db.Where("user_id = ?", userID).
		Offset(offset).Limit(limit).
		Order("created_at DESC").
		Find(&logs).Error; err != nil {
		return nil, 0, err
	}

	return logs, total, nil
}

// AdjustBalance 手动调整余额
func (s *PlatformAccountBalanceService) AdjustBalance(ctx context.Context, accountID int64, amount float64, style int, remark string, operator string) error {
	logger.Info("开始手动调整余额",
		"account_id", accountID,
		"amount", amount,
		"style", style,
		"remark", remark,
		"operator", operator)

	// 开启事务
	tx := s.db.Begin()
	if tx.Error != nil {
		logger.Error("开启事务失败",
			"error", tx.Error,
			"account_id", accountID)
		return tx.Error
	}
	defer func() {
		if r := recover(); r != nil {
			tx.Rollback()
		}
	}()

	// 1. 获取平台账号信息
	account, err := s.platformAccountRepo.GetByID(accountID)
	if err != nil {
		tx.Rollback()
		logger.Error("获取平台账号信息失败",
			"error", err,
			"account_id", accountID)
		return err
	}

	// 2. 获取本地用户账号（通过 bind_user_id 字段）
	if account.BindUserID == nil {
		tx.Rollback()
		logger.Error("平台账号未绑定本地用户", "account_id", accountID)
		return errors.New("平台账号未绑定本地用户")
	}
	userID := *account.BindUserID
	user, err := s.userRepo.GetByID(ctx, userID)
	if err != nil {
		tx.Rollback()
		logger.Error("获取本地用户账号失败", "error", err, "user_id", userID)
		return err
	}

	// 3. 调整余额
	before := user.Balance
	user.Balance += amount
	if err := tx.Model(&model.User{}).Where("id = ?", userID).Update("balance", user.Balance).Error; err != nil {
		tx.Rollback()
		logger.Error("更新本地用户余额失败",
			"error", err,
			"user_id", userID)
		return err
	}

	// 4. 写用户余额变动日志
	userLog := &model.BalanceLog{
		UserID:            userID,
		PlatformAccountID: accountID,
		PlatformID:        account.PlatformID,
		PlatformCode:      account.Platform.Code,
		PlatformName:      account.Platform.Name,
		Amount:            amount,
		Type:              model.BalanceTypeIncome,
		Style:             style,
		Balance:           user.Balance,
		BalanceBefore:     before,
		Remark:            remark,
		Operator:          operator,
		CreatedAt:         time.Now(),
	}
	if err := tx.Create(userLog).Error; err != nil {
		tx.Rollback()
		logger.Error("创建用户余额变动日志失败",
			"error", err,
			"user_id", userID)
		return err
	}

	// 提交事务
	if err := tx.Commit().Error; err != nil {
		logger.Error("提交事务失败",
			"error", err,
			"account_id", accountID)
		return err
	}

	logger.Info("手动调整余额成功",
		"user_id", userID,
		"amount", amount,
		"balance_before", before,
		"balance_after", user.Balance)
	return nil
}
