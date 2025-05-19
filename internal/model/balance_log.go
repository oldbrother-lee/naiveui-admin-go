package model

import (
	"time"
)

// BalanceLog 用户余额流水模型
// 记录每一笔余额变动
type BalanceLog struct {
	ID            int64     `json:"id" gorm:"primaryKey;type:bigint;not null"`
	UserID        int64     `json:"user_id" gorm:"type:bigint;not null;index;comment:用户ID"`
	Amount        float64   `json:"amount" gorm:"type:decimal(10,2);not null;comment:变动金额(正为收入,负为支出)"`
	Type          int       `json:"type" gorm:"type:tinyint;not null;comment:1-收入 2-支出"`
	Style         int       `json:"style" gorm:"type:tinyint;not null;comment:变动类型(1订单 2奖励 3提现 4充值 5退款等)"`
	Balance       float64   `json:"balance" gorm:"type:decimal(10,2);not null;comment:变动后余额"`
	BalanceBefore float64   `json:"balance_before" gorm:"type:decimal(10,2);not null;comment:变动前余额"`
	Remark        string    `json:"remark" gorm:"size:255;comment:备注"`
	Operator      string    `json:"operator" gorm:"size:100;comment:操作人"`
	CreatedAt     time.Time `json:"created_at" gorm:"type:datetime;autoCreateTime"`
}

// TableName 指定表名
func (BalanceLog) TableName() string {
	return "balance_logs"
}
