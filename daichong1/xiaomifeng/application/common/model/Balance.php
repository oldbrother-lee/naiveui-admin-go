<?php

//decode by http://chiran.taobao.com/
namespace app\common\model;

use app\common\library\SubscribeMessage;
use app\common\library\Templetmsg;
use think\Model;
class Balance extends Model
{
	const STYLE_ORDERS = 1;
	const STYLE_REWARDS = 2;
	const STYLE_WITHDRAW = 3;
	const STYLE_RECHARGE = 4;
	const STYLE_REFUND = 5;
	public static function revenue($customer_id, $money, $remark, $style, $operator)
	{
		$balance_pr = M('customer')->where(array('id' => $customer_id))->value('balance');
		$uid = M('customer')->where(array('id' => $customer_id))->setInc("balance", $money);
		if (!$uid) {
			return rjson(1, '收入失败');
		}
		$user = M('customer')->where(array('id' => $customer_id))->find();
		$uid && M('balance_log')->insertGetId(array('money' => $money, 'type' => 1, 'remark' => $remark, 'create_time' => time(), 'style' => $style, 'customer_id' => $customer_id, 'balance' => $user['balance'], 'balance_pr' => $balance_pr, 'operator' => $operator));
		$user['client'] == Client::CLIENT_WX && Templetmsg::balanceCg($customer_id, '你有新的余额收入了', time_format(time()), $remark, $money, $user['balance']);
		return rjson(0, '用户余额收入操作成功');
	}
	public static function expend($customer_id, $money, $remark, $style, $operator)
	{
		if ($money <= 0) {
			return rjson(1, '支出金额不合法！');
		}
		$create_time = time();
		$has = M('balance_log')->where(array('money' => $money, 'type' => 2, 'remark' => $remark, 'create_time' => $create_time, 'style' => $style, 'customer_id' => $customer_id, 'operator' => $operator))->find();
		if ($has) {
			return rjson(1, '扣费系统异常，请稍后再试！');
		}
		$user = M('customer')->where(array('id' => $customer_id))->field("balance,shouxin_e")->find();
		$balance_pr = $user['balance'];
		if ($user['balance'] - $money < -1 * $user['shouxin_e']) {
			return rjson(1, '检查到授信额度不足！');
		}
		$uid = M('customer')->where(array('id' => $customer_id, 'balance' => array(array('egt', $money - $user['shouxin_e']), array('eq', $balance_pr), 'and')))->setDec("balance", $money);
		if (!$uid) {
			return rjson(1, '余额支出时发生异常！');
		}
		$user = M('customer')->where(array('id' => $customer_id))->find();
		$uid && M('balance_log')->insertGetId(array('money' => $money, 'type' => 2, 'remark' => $remark, 'create_time' => $create_time, 'style' => $style, 'customer_id' => $customer_id, 'balance' => $user['balance'], 'balance_pr' => $balance_pr, 'operator' => $operator));
		$user['client'] == Client::CLIENT_WX && Templetmsg::balanceCg($customer_id, '你有新的余额支出了', time_format(time()), $remark, $money, $user['balance']);
		return rjson(0, '用户余额支出操作成功');
	}
}