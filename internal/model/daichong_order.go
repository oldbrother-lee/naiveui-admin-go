package model

// DaichongOrder 代充订单模型
// 对应表：dyr_daichong_orders

type DaichongOrder struct {
	ID           int64  `gorm:"primaryKey;autoIncrement" json:"id"`                    // 主键
	User         string `gorm:"type:varchar(255)" json:"user"`                         // 用户id
	OrderID      string `gorm:"type:varchar(35);not null" json:"order_id"`             // 代付订单号
	Account      string `gorm:"type:varchar(35);not null" json:"account"`              // 充值账号
	Denom        string `gorm:"type:varchar(22);not null" json:"denom"`                // 面值
	SettlePrice  string `gorm:"type:varchar(120);not null" json:"settlePrice"`         // 结算价
	CreateTime   string `gorm:"type:varchar(120);not null" json:"createTime"`          // 订单创建时间
	ChargeTime   string `gorm:"type:varchar(120)" json:"chargeTime"`                   // 接单时间
	UploadTime   string `gorm:"type:varchar(120)" json:"uploadTime"`                   // 上报充值时间
	Status       int    `gorm:"not null" json:"status"`                                // 订单状态
	SettleStatus string `gorm:"type:varchar(12);not null" json:"settleStatus"`         // 结算状态
	YrOrderID    string `gorm:"type:varchar(120)" json:"yr_order_id"`                  // 系统id
	IsPost       int    `gorm:"not null;default:0" json:"is_post"`                     // 是否已推送
	Base         string `gorm:"type:longtext" json:"base"`                             // 凭证图
	Type         int    `gorm:"not null;default:0" json:"type"`                        // 类型
	SbType       int    `gorm:"not null;default:0" json:"sb_type"`                     // sb类型
	Yunying      string `gorm:"type:varchar(255);not null;default:'1'" json:"yunying"` // 运营商
	Beizhu       string `gorm:"type:varchar(225)" json:"beizhu"`                       // 备注
	Prov         string `gorm:"type:varchar(255)" json:"prov"`                         // 省份
	Way          int    `gorm:"default:1" json:"way"`                                  // 1-取单 2-推单
}

func (DaichongOrder) TableName() string {
	return "dyr_daichong_orders"
}
