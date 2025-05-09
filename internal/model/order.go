package model

import (
	"time"
)

// OrderStatus 订单状态
type OrderStatus int

const (
	OrderStatusPendingPayment  OrderStatus = iota + 1 // 待支付
	OrderStatusPendingRecharge                        // 待充值
	OrderStatusRecharging                             // 充值中
	OrderStatusSuccess                                // 充值成功
	OrderStatusFailed                                 // 充值失败
	OrderStatusRefunded                               // 已退款
	OrderStatusCancelled                              // 已取消
	OrderStatusPartial                                // 部分充值
	OrderStatusSplit                                  // 已拆单
	OrderStatusProcessing                             // 处理中
)

// Order 订单模型
type Order struct {
	ID               int64       `json:"id" gorm:"primaryKey"`
	OrderNumber      string      `json:"order_number" gorm:"size:32;uniqueIndex;comment:订单号"`
	CustomerID       int64       `json:"customer_id" gorm:"index;comment:客户ID"`
	Mobile           string      `json:"mobile" gorm:"size:20;index;comment:手机号"`
	ProductID        int64       `json:"product_id" gorm:"index;comment:产品ID"`
	Status           OrderStatus `json:"status" gorm:"comment:订单状态"`
	TotalPrice       float64     `json:"total_price" gorm:"type:decimal(10,2);comment:总价"`
	Price            float64     `json:"price" gorm:"type:decimal(10,2);comment:单价"`
	PayWay           int         `json:"pay_way" gorm:"comment:支付方式"`
	SerialNumber     string      `json:"serial_number" gorm:"size:64;comment:支付流水号"`
	IsPay            int         `json:"is_pay" gorm:"comment:是否支付"`
	PayTime          *time.Time  `json:"pay_time" gorm:"comment:支付时间"`
	CreateTime       time.Time   `json:"create_time" gorm:"comment:创建时间"`
	FinishTime       *time.Time  `json:"finish_time" gorm:"comment:完成时间"`
	Remark           string      `json:"remark" gorm:"size:255;comment:备注"`
	ISP              int         `json:"isp" gorm:"comment:运营商"`
	Param1           string      `json:"param1" gorm:"size:255;comment:参数1"`
	Param2           string      `json:"param2" gorm:"size:255;comment:参数2"`
	Param3           string      `json:"param3" gorm:"size:255;comment:参数3"`
	OutTradeNum      string      `json:"out_trade_num" gorm:"size:64;comment:外部交易号"`
	APICurID         int64       `json:"api_cur_id" gorm:"comment:当前API ID"`
	APIOrderNumber   string      `json:"api_order_number" gorm:"size:64;comment:API订单号"`
	APITradeNum      string      `json:"api_trade_num" gorm:"size:64;comment:API交易号"`
	APICurParamID    int64       `json:"api_cur_param_id" gorm:"comment:API当前参数ID"`
	APICurCount      int         `json:"api_cur_count" gorm:"comment:API当前计数"`
	APIOpen          int         `json:"api_open" gorm:"comment:API是否开启"`
	APICurIndex      int         `json:"api_cur_index" gorm:"comment:API当前索引"`
	IsApart          int         `json:"is_apart" gorm:"comment:是否拆单"`
	ApartOrderNumber string      `json:"apart_order_number" gorm:"size:32;comment:拆单订单号"`
	DelayTime        int         `json:"delay_time" gorm:"comment:延迟时间"`
	ApplyRefund      int         `json:"apply_refund" gorm:"comment:申请退款"`
	IsRebate         int         `json:"is_rebate" gorm:"comment:是否返利"`
	IsDel            int         `json:"is_del" gorm:"comment:是否删除"`
	Guishu           string      `json:"guishu" gorm:"size:64;comment:归属"`
	Client           int         `json:"client" gorm:"comment:客户端"`
	WeixinAppID      string      `json:"weixin_appid" gorm:"size:64;comment:微信APPID"`
	UpdatedAt        time.Time   `json:"updated_at" gorm:"comment:更新时间"`
	PlatformId       int64       `json:"platform_id" gorm:"comment:平台ID"`
	UserOrderId      string      `json:"user_order_id" gorm:"size:64;comment:用户订单ID"`
}

// TableName 表名
func (Order) TableName() string {
	return "orders"
}
