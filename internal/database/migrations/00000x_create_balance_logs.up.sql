CREATE TABLE IF NOT EXISTS `balance_logs` (
  `id` BIGINT PRIMARY KEY AUTO_INCREMENT COMMENT '主键',
  `user_id` BIGINT NOT NULL COMMENT '用户ID',
  `amount` DECIMAL(10,2) NOT NULL COMMENT '变动金额(正为收入,负为支出)',
  `type` TINYINT NOT NULL COMMENT '1-收入 2-支出',
  `style` TINYINT NOT NULL COMMENT '变动类型(1订单 2奖励 3提现 4充值 5退款等)',
  `balance` DECIMAL(10,2) NOT NULL COMMENT '变动后余额',
  `balance_before` DECIMAL(10,2) NOT NULL COMMENT '变动前余额',
  `remark` VARCHAR(255) DEFAULT '' COMMENT '备注',
  `operator` VARCHAR(100) DEFAULT '' COMMENT '操作人',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户余额流水表'; 