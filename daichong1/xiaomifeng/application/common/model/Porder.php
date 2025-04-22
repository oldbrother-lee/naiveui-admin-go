<?php

//decode by http://chiran.taobao.com/
namespace app\common\model;

use app\api\controller\Notify;
use app\common\library\Createlog;
use app\common\library\Notification;
use app\common\library\PayWay;
use app\common\library\Rechargeapi;
use app\common\model\Porder as PorderModel;
use think\Exception;
use think\Model;
use Util\Ispzw;
class Porder extends Model
{
	protected $append = ["status_text", "status_text2", "status_text_color", "state", "state_text", "create_time_text"];
	public static function init()
	{
		self::event('after_insert', function ($porder) {
			$order_number = Porder::prfun() . date('ymd', time()) . $porder->id;
			$porder->where(array('id' => $porder->id))->update(array('order_number' => $order_number));
		});
	}
	public static function prfun()
	{
		return substr(C('PORDER_PR') . 'AAA', 0, 3);
	}
	public static function pr()
	{
		return substr(C('PORDER_PR') . 'AAA', 0, 3);
	}
	public function Customer()
	{
		return $this->belongsTo('Customer', 'customer_id');
	}
	public function getStatusTextAttr($value, $data)
	{
		return C('PORDER_STATUS')[$data['status']];
	}
	public function getStatusText2Attr($value, $data)
	{
		return C('ORDER_STUTAS')[$data['status']];
	}
	public function getStatusTextColorAttr($value, $data)
	{
		$status = $data['status'];
		if (in_array($status, array(2, 10))) {
			return "success";
		} elseif (in_array($status, array(3, 9, 11))) {
			return 'warning';
		} elseif (in_array($status, array(4, 12))) {
			return 'primary';
		} elseif (in_array($status, array(5, 8))) {
			return "danger";
		} else {
			return "default";
		}
	}
	public function getStateAttr($value, $data)
	{
		return self::getState($data['status']);
	}
	public function getStateTextAttr($value, $data)
	{
		return C('ORDER_STATE')[self::getState($data['status'])];
	}
	public function getCreateTimeTextAttr($value, $data)
	{
		return time_format($data['create_time']);
	}
	public function getRebateStatusText($is_rebate, $status)
	{
		if ($is_rebate) {
			return "已返利";
		} elseif (in_array($status, array(5, 6))) {
			return "失败不返";
		} elseif (in_array($status, array(4, 12, 13))) {
			return "待返利";
		} elseif (in_array($status, array(7))) {
			return "取消不返";
		} else {
			return "充值中";
		}
	}
	public static function getState($status)
	{
		if (in_array($status, array(4))) {
			$state = 1;
		} elseif (in_array($status, array(1, 2, 3, 8, 9, 10, 11))) {
			$state = 0;
		} elseif (in_array($status, array(5, 6))) {
			$state = 2;
		} elseif (in_array($status, array(12, 13))) {
			$state = 3;
		} elseif (in_array($status, array(7))) {
			$state = -1;
		} else {
			$state = 0;
		}
		return $state;
	}
	public static function getVoucherUrl($porder_id, $status)
	{
		return $status == 1 ? C('WEB_URL') . 'yrapi.php/open/voucher/id/' . $porder_id . '.html' : '';
	}
	public static function getTypeName($type)
	{
		return M('product_type')->where(array('id' => $type))->value('type_name');
	}
	public static function getCurApiInfos($apiarrstr, $api_cur_index = 0, $apiopen = 1)
	{
		if ($apiopen != 1) {
			return "";
		}
		$apiarr = json_decode($apiarrstr, true);
		if (!$apiarr) {
			return "";
		}
		foreach ($apiarr as $k => $v) {
			if ($api_cur_index == $k) {
				return getReapiName($v['reapi_id']) . '-' . getReapiParamName($v['param_id']);
			}
		}
		return "";
	}

    /**
     * Class createOrder
     * @package app\common\model
     * @服务须知: 用户使用本服务的行为若有任何违反国家法律法规或侵犯任何第三方的合法权益的情形时，本人有权直接删除该等违反规定之信息，并可以暂停或终止向该用户提供服务。
     * 若用户利用本服务从事任何违法或侵权行为，由用户自行承担全部责任，本人不承担任何法律及连带责任。因此给本人或任何第三方造成任何损失，用户应负责全额赔偿。
     * 如确定需要服务时即默认同意本服务协议，开始服务时即生效日期！！！
     * @email: 1006000670@qq.com
     * @Time: 2023/1/28   19:07
     */
	public static function createOrder($mobile, $product_id, $extparam, $customer_id, $client = 1, $remark = '', $out_trade_num = '', $amount = '', $price = '', $isfxh5 = 0)
	{
		$mobile = trim($mobile);
		$prd = M('product p')->join('product_type pt', 'p.type=pt.id')->where(array('p.id' => $product_id, 'p.added' => 1))->field('p.*,pt.typec_id')->find();
		if (!$prd) {
			return rjson(1, '未找到相关产品(产品ID不正确或已下架)');
		}
		if ($out_trade_num && ($reod = M('porder')->where(array('out_trade_num' => $out_trade_num, 'status' => array('gt', 1), 'customer_id' => $customer_id))->find())) {
			return rjson(208, '已经存在相同商户订单号的订单', $reod['order_number']);
		}
		if (M('mobile_blacklist')->where(array('mobile' => $mobile, 'limit_time' => array('gt', time())))->find()) {
			return rjson(1, '该号码无法充值');
		}
		if (C('LIMIT_ONE_PORDER') == 1 && M('porder')->where(array('mobile' => $mobile, 'status' => array('not in', array(4, 5, 6, 7, 12, 13)), 'is_del' => 0))->count()) {
			return rjson(1, '当前号码已经存在订单');
		}
		$province = '';
		$city = '';
		$user = M('customer')->where(array('id' => $customer_id))->find();
		switch ($prd['typec_id']) {
			case 1:
			case 2:
				if (!is_numeric($mobile) || mb_strlen($mobile) != 11) {
					return rjson(1, '手机号格式不正确');
				}
				$guishu = QCellCore($mobile);
				if ($guishu['errno'] != 0) {
					return rjson($guishu['errno'], $guishu['errmsg']);
				}
				$guishu = Product::Ispzhan($mobile, $customer_id, $guishu);
				$map['p.isp'] = array('like', '%' . $guishu['data']['isp'] . '%');
				$data['isp'] = $guishu['data']['ispstr'];
				$data['guishu'] = $guishu['data']['prov'] . $guishu['data']['city'];
				$data['guishu_pro'] = $guishu['data']['prov'];
				$data['guishu_city'] = $guishu['data']['city'];
				$province = $guishu['data']['prov'];
				$city = $guishu['data']['city'];
				break;
			case 3:
				if (!isset($extparam['prov']) || !$extparam['prov']) {
					return rjson(1, '请选择电费地区！');
				}
				$ecity = M('electricity_city')->where(array('city_name' => array('like', '%' . $extparam['prov'] . '%'), 'is_del' => 0))->find();
				if (!$ecity) {
					return rjson(1, '不支持的电费地区！');
				}
				$data['isp'] = $ecity['city_name'];
				$data['guishu_pro'] = $ecity['city_name'];
				$province = $ecity['city_name'];
				$city = isset($extparam['city']) ? $extparam['city'] : '';
				if (isset($extparam['id_card_no']) && $extparam['id_card_no'] && (!isset($extparam['ytype']) || !$extparam['ytype'] || !in_array(intval($extparam['ytype']), array(1, 2, 3)))) {
					return rjson(1, '电费充值三要素验证类型错误，必须是1/2/3！', $ecity);
				}
				if (isset($extparam['id_card_no']) && $extparam['id_card_no'] && mb_strlen($extparam['id_card_no']) != 6) {
					return rjson(1, '电费充值身份证/银行卡/营业执照后六位不正确！', $ecity);
				}
				if ($ecity['need_ytype'] == 1 && $ecity['force_ytype'] == 1 && (!isset($extparam['id_card_no']) || !$extparam['id_card_no'])) {
					return rjson(1, '电费充值身份证/银行卡/营业执照后六位必填！');
				}
				if ($ecity['need_city'] == 1 && $ecity['force_city'] == 1 && (!isset($extparam['city']) || !$extparam['city'])) {
					return rjson(1, '电费充值请选择地级市！');
				}
				$data['param1'] = isset($extparam['id_card_no']) ? $extparam['id_card_no'] : '';
				$data['param1'] && ($data['param2'] = isset($extparam['ytype']) ? $extparam['ytype'] : '');
				$data['param3'] = isset($extparam['city']) ? $extparam['city'] : '';
				$data['guishu_city'] = $data['param3'];
				break;
			default:
				$data['param1'] = isset($extparam['param1']) ? $extparam['param1'] : '';
				$data['param2'] = isset($extparam['param2']) ? $extparam['param2'] : '';
				$data['param3'] = isset($extparam['param3']) ? $extparam['param3'] : '';
				break;
		}
		$map['p.id'] = $product_id;
		$map['p.added'] = 1;
		if (in_array($client, array(Client::CLIENT_API, Client::CLIENT_AGA)) || $isfxh5 == 1) {
			$map['p.show_style'] = array('in', '1,3');
		}
		if (in_array($client, array(Client::CLIENT_WX, Client::CLIENT_H5, Client::CLIENT_MP)) && ($isfxh5 = 0)) {
			$map['p.show_style'] = array('in', '1,2');
		}
		$resdata = Product::getProduct($map, $user['id'], $province, $city);
		if ($resdata['errno'] != 0) {
			return rjson(1, $resdata['errmsg']);
		}
		$product = $resdata['data'];
		if (!isset($product['price']) || !$product['price']) {
			return rjson(1, '要充值的产品还没有准备好，请联系平台！');
		}
		$real_amount = floatval(preg_replace('/\\D/', '', $product['name']));
		if ($amount && $real_amount && $amount != $real_amount) {
			return rjson(1, '面值检测不相同，提单不通过！');
		}
		if ($price && $product['price'] > $price) {
			return rjson(1, '成本限制，提单不通过！');
		}
		$data['product_id'] = $product['id'];
		$data['customer_id'] = $customer_id;
		$data['total_price'] = $product['price'];
		$data['create_time'] = time();
		$data['status'] = 1;
		$data['remark'] = $remark;
		$data['mobile'] = $mobile;
		$data['type'] = $product['type'];
		$data['typec'] = $product['typec_id'];
		$data['title'] = $product['yname'] . $product['type_name'];
		$data['product_name'] = $product['yname'];
		$data['product_desc'] = $product['desc'];
		$data['body'] = '为账号' . $mobile . '充值' . $product['yname'] . $product['type_name'];
		$data['api_open'] = $product['api_open'];
		$data['api_arr'] = $product['api_arr'];
		$data['api_cur_index'] = -1;
		$data['out_trade_num'] = $out_trade_num;
		$data['pay_way'] = PayWay::PAY_WAY_NULL;
		$data['api_cur_count'] = 0;
		$data['client'] = $client;
		$data['weixin_appid'] = $user['weixin_appid'];
		$data['delay_time'] = $product['delay_api'] > 0 ? time() + $product['delay_api'] * 60 * 60 : 0;
		$data['is_jiema'] = $product['is_jiema'];
		$model = new self();
		$model->save($data);
		if (!($aid = $model->id)) {
			return rjson(1, '下单失败，请重试！');
		}
		return rjson(0, '下单成功', $model->id);
	}
	public static function create_pay($aid, $payway, $client)
	{
		$order = self::where(array('id' => $aid, 'status' => 1))->find();
		if (!$order) {
			return rjson(1, '订单无需支付' . $aid);
		}
		$customer = M('customer')->where(array('id' => $order['customer_id']))->find();
		if (!$customer) {
			return rjson(1, '用户数据不存在');
		}
		return PayWay::create($payway, $client, array('openid' => $customer['wx_openid'] ?: $customer['ap_openid'], 'body' => $order['body'], 'order_number' => $order['order_number'], 'total_price' => $order['total_price'], 'appid' => $customer['weixin_appid']));
	}
	public static function notify($order_number, $payway, $serial_number)
	{
		$porder = M('porder')->where(array('order_number' => $order_number, 'status' => 1))->find();
		if (!$porder) {
			return rjson(1, '不存在订单');
		}
		Createlog::porderLog($porder['id'], "用户支付回调成功，总金额：￥" . $porder['total_price']);
		M('porder')->where(array('id' => $porder['id'], 'status' => 1))->setField(array('status' => 2, 'pay_time' => time(), 'pay_way' => $payway, 'serial_number' => $serial_number));
		Notification::paySus($porder['id']);
		try {
			$h5res = self::h5AgentChildPay($porder['id']);
			if ($h5res['errno'] != 500 && $h5res['errno'] != 0) {
				self::rechargeFailDo($porder['order_number'], 'H5代理商扣款失败，订单被拦截，直接失败！原因：' . $h5res['errmsg']);
				M('porder')->where(array('id' => $porder['id']))->setField(array('remark' => "H5代理商扣款失败"));
				return rjson(1, 'H5代理商扣款失败');
			}
		} catch (Exception $e) {
			self::rechargeFailDo($porder['order_number'], 'H5代理商扣款失败，订单被拦截，直接失败！原因：系统报错-' . $e->getMessage());
			M('porder')->where(array('id' => $porder['id']))->setField(array('remark' => "H5代理商扣款失败"));
			return rjson(1, 'H5代理商扣款失败');
		}
		if ($porder['is_jiema']) {
			return self::jmnotify($porder);
		}
		if (C('ISP_ZHUANW_SW') == 1 && in_array($porder['type'], array(1, 2))) {
			$res = Ispzw::isZhuanw(C('ISP_ZHUANW_CFG.apikey'), $porder['mobile']);
			if ($res['errno'] == 0) {
				Createlog::porderLog($porder['id'], '携号转网查询：' . $res['errmsg']);
				self::rechargeFailDo($porder['order_number'], '订单手机号已携号转网，订单被拦截，直接失败！');
				M('porder')->where(array('id' => $porder['id']))->setField(array('remark' => "手机号携号转网"));
				return rjson(1, '订单手机号携号转网');
			} else {
				Createlog::porderLog($porder['id'], '携号转网查询：' . $res['errmsg']);
			}
		}
		if ($porder['delay_time'] <= time()) {
			$porder['api_open'] == 1 && queue('app\\queue\\job\\Work@porderSubApi', $porder['id']);
		} else {
			Createlog::porderLog($porder['id'], '订单开启了延迟提交API，设定的提交时间:' . time_format($porder['delay_time']));
		}
		return rjson(0, '回调处理完成');
	}
	public static function jmnotify($porder)
	{
		$product = M('product')->where(array('id' => $porder['product_id']))->field('id,jmapi_id,jmapi_param_id')->find();
		if (!$product) {
			return djson(1, '产品未找到');
		}
		$config = M('jmapi')->where(array('id' => $product['jmapi_id'], 'is_del' => 0))->find();
		$param = M('jmapi_param')->where(array('id' => $product['jmapi_param_id']))->find();
		if (!$config || !$param) {
			return djson(1, '接码api信息未查询到');
		}
		$classname = 'Jiema\\' . $config['callapi'];
		$model = new $classname($config);
		$param['extend_param1'] = $porder['extend_param1'];
		$param['extend_param2'] = $porder['extend_param2'];
		$param['extend_param3'] = $porder['extend_param3'];
		$res = $model->recharge($porder['mobile'], $porder['order_number'], $param);
		if ($res['errno'] != 0) {
			self::rechargeFail($porder['order_number'], '接码订单提交api充值失败|' . $res['errmsg'] . '|' . var_export($res['data']), "接码api提交失败");
			return rjson(0, '接码订单提交api充值失败');
		}
		M('porder')->where(array('id' => $porder['id'], 'status' => 2))->setField(array('status' => 3));
		Createlog::porderLog($porder['id'], '接码订单提交api成功|订单变成充值中状态|接口返回信息:' . $res['errmsg']);
		return rjson(0, '回调处理完成');
	}
	public static function subApi($porder_id)
	{
		$porder = M('porder')->where(array('id' => $porder_id, 'status' => 2, 'api_open' => 1))->find();
		if (!$porder) {
			return rjson(1, '订单无需提交接口充值');
		}
		Rechargeapi::recharge($porder['id']);
		return rjson(0, '提交接口工作完成');
	}
	public static function getCurApi($porder_id)
	{
		$porder = M('porder')->where(array('id' => $porder_id, 'status' => array('in', '2,3'), 'api_open' => 1))->find();
		if (!$porder) {
			return rjson(1, '自动充值订单无效');
		}
		$api_arr = json_decode($porder['api_arr'], true);
		if (count($api_arr) == 0) {
			return rjson(1, '自动充值接口为空');
		}
		if ($porder['api_cur_index'] >= count($api_arr) - 1 && $api_arr[$porder['api_cur_index']]['num'] <= $porder['api_cur_num']) {
			return rjson(1, '无可继续调用的API');
		}
		if ($porder['api_cur_index'] >= 0) {
			$num = $api_arr[$porder['api_cur_index']]['num'];
			$cur_num = $porder['api_cur_num'];
			if ($cur_num >= $num) {
				$index = $porder['api_cur_index'] + 1;
				$cnum = 1;
			} else {
				$index = $porder['api_cur_index'];
				$cnum = $porder['api_cur_num'] + 1;
			}
			return rjson(0, '请继续提交接口充值', array('api' => $api_arr[$index], 'index' => $index, 'num' => $cnum));
		} else {
			$index = $porder['api_cur_index'] + 1;
			return rjson(0, '请继续提交接口充值', array('api' => $api_arr[$index], 'index' => $index, 'num' => 1));
		}
	}
	public static function rechargeSusApi($api, $api_order_number, $data, $remark = '', $kami = '')
	{
		$flag = self::apinotify_log($api, $api_order_number, $data);
		if (!$flag) {
			return rjson(1, '接口已回调过了');
		}
		$porder = M('porder')->where(array('api_order_number' => $api_order_number, 'status' => array('in', '2,3,9')))->find();
		if (!$porder) {
			return rjson(1, '订单未找到');
		}
		$remark && M('porder')->where(array('id' => $porder['id']))->setField(array('remark' => $remark));
		$kami && M('porder')->where(array('id' => $porder['id']))->setField(array('charge_kami' => $kami));
		M('porder_apilog')->where(array('api_order_number' => $api_order_number))->setField(array('state' => 1));
		return self::rechargeSus($porder['order_number'], "充值成功|接口回调|" . $remark . '|' . var_export($data, true));
	}
	public static function rechargeSus($order_number, $remark)
	{
		$porder = M('porder')->where(array('order_number' => $order_number, 'status' => array('in', '2,3,8,9,10,11')))->find();
		if (!$porder) {
			return rjson(1, '订单未找到');
		}
		M('porder')->where(array('id' => $porder['id']))->setField(array('status' => 4, 'finish_time' => time(), 'apply_refund' => 0, 'apply_break' => 0));
		Createlog::porderLog($porder['id'], $remark);
		queue('app\\queue\\job\\Work@callFunc', array('class' => '\\app\\common\\library\\Notification', 'func' => 'rechargeSus', 'param' => $porder['id']));
		self::rebate($porder['id']);
		$porder['is_apart'] == 1 && self::childFinishTrigger($porder['id']);
		return rjson(0, '操作成功');
	}

    /**
     * @param $api api接口
     * @param $api_order_number 订单号
     * @param $data 数据
     * @param string $remark 备注
     * @return mixed
     *
     * 订单失败处理
     */
	public static function rechargeFailApi($api, $api_order_number, $data, $remark = '')
	{

        Createlog::porderLog($api, '订单回调信息失败自动话处理'.json_encode($data));
		$flag = self::apinotify_log($api, $api_order_number, $data);
		if (!$flag) {
			return rjson(1, '接口已回调过了');
		}
		$porder = M('porder')->where(array('api_order_number' => $api_order_number, 'status' => array('in', '2,3')))->find();
		if (!$porder) {
			return rjson(1, '订单未找到');
		}
		M('porder_apilog')->where(array('api_order_number' => $api_order_number))->setField(array('state' => 2));
		return self::rechargeFail($porder['order_number'], "充值失败|接口回调|" . $remark . '|' . var_export($data, true), $remark);
	}
	public static function rechargeFail($order_number, $log, $remark = '')
	{
		$porder = M('porder')->where(array('order_number' => $order_number, 'status' => array('in', '2,3,8,9,10,11')))->find();
		if (!$porder) {
			return rjson(1, '订单未找到');
		}
		$remark && M('porder')->where(array('id' => $porder['id']))->setField(array('remark' => $remark));
		M('porder')->where(array('id' => $porder['id']))->setField(array('apifail_time' => time()));
		if ($porder['apply_refund'] == 1) {
			Createlog::porderLog($porder['id'], $log);
			return self::rechargeFailDo($order_number, $log);
		}
		if ($porder['api_open'] == 1 && $porder['apply_break'] == 0) {//
			$res = Porder::getCurApi($porder['id']);
			if ($res['errno'] == 0) {
				Createlog::porderLog($porder['id'], $log);
				M('porder')->where(array('id' => $porder['id']))->setField(array('status' => 2));
				queue('app\\queue\\job\\Work@porderSubApi', $porder['id'], intval(C('API_FAILE_DELAY_MINUTE')) * 60);
			//再次提交订单
				return rjson(0, '处理成功');
			}
		}
		$apifailstyle = intval(C('ODAPI_FAIL_STYLE')) == 3 ? M('product')->where(array('id' => $porder['product_id']))->value('api_fail_style') : intval(C('ODAPI_FAIL_STYLE'));
		if ($apifailstyle == 2 || $porder['apply_break'] == 1) {
			Createlog::porderLog($porder['id'], $log);
			M('porder')->where(array('id' => $porder['id']))->setField(array('status' => 10, 'apply_break' => 0));
			Createlog::porderLog($porder['id'], "api失败,订单到压单状态（压单功能生效，该订单可以手动再次提交接口，如果不想使用此功能，请到 系统->网站设置->用户配置->订单api失败后处理方式 选项 改成:直接失败）");
			return rjson(0, '处理成功');
		} else {
			return self::rechargeFailDo($order_number, $log);
		}
	}
	public static function rechargeFailAgent($order_number, $remark)
	{
		$porder = M('porder')->where(array('order_number' => $order_number, 'status' => array('in', '2,10')))->find();
		if (!$porder) {
			return rjson(1, '订单不可取消');
		}
		if ($porder['status'] == 2 && $porder['api_open'] == 1) {
			return rjson(1, '订单正在提交，暂时不可取消');
		}
		return self::rechargeFailDo($order_number, $remark);
	}
	public static function rechargeFailDo($order_number, $remark)
	{
		$porder = M('porder')->where(array('order_number' => $order_number, 'status' => array('in', '2,3,8,9,10,11')))->find();
		if (!$porder) {
			return rjson(1, '订单未找到');
		}
		M('porder')->where(array('id' => $porder['id']))->setField(array('status' => 5, 'refund_price' => $porder['total_price'], 'apply_refund' => 0, 'apply_break' => 0, 'finish_time' => time()));
		queue('app\\queue\\job\\Work@callFunc', array('class' => '\\app\\common\\library\\Notification', 'func' => 'rechargeFail', 'param' => $porder['id']));
		C('AUTO_REFUND') == 1 && queue('app\\queue\\job\\Work@porderRefund', array('id' => $porder['id'], 'remark' => $remark, 'operator' => '系统'));
		$porder['is_apart'] == 1 && self::childFinishTrigger($porder['id']);
		return rjson(0, '操作成功');
	}
	public static function rechargeRateApi($api, $api_order_number, $data, $charge_amount)
	{
		$porder = M('porder')->where(array('api_order_number' => $api_order_number, 'status' => array('in', '3'), 'charge_amount' => array('neq', $charge_amount)))->find();
		if (!$porder) {
			return rjson(1, '订单未找到');
		}
		if (!$charge_amount) {
			return rjson(1, '充值进度变化不合法');
		}
		Createlog::porderLog($porder['id'], "充值进度|接口回调|已充值" . $charge_amount . '|' . var_export($data, true));
		$flag = M('porder')->where(array('api_order_number' => $api_order_number, 'status' => array('in', array(3))))->setField(array('remark' => "已充值：" . $charge_amount, 'charge_amount' => $charge_amount));
		$flag && queue('app\\queue\\job\\Work@callFunc', array('class' => '\\app\\common\\library\\Notification', 'func' => 'rechargeIng', 'param' => $porder['id']));
		return rjson(0, '操作成功');
	}
	public static function rechargePartApi($api, $api_order_number, $data, $remark, $charge_amount = '')
	{
		$porder = M('porder')->where(array('api_order_number' => $api_order_number, 'status' => array('in', '3,9')))->find();
		if (!$porder) {
			return rjson(1, '订单未找到');
		}
		Createlog::porderLog($porder['id'], "部分充值|接口回调|" . $remark . '|' . var_export($data, true));
		$charge_amount && M('porder')->where(array('id' => $porder['id'], 'status' => array('in', '3,9')))->setField(array('charge_amount' => $charge_amount));
		$remark && M('porder')->where(array('id' => $porder['id'], 'status' => array('in', '3,9')))->setField(array('status' => 9, 'remark' => $remark));
		M('porder_apilog')->where(array('api_order_number' => $api_order_number))->setField(array('state' => 3));
		queue('app\\queue\\job\\Work@callFunc', array('class' => '\\app\\common\\library\\Notification', 'func' => 'rechargeIng', 'param' => $porder['id']));
		return rjson(0, '操作成功');
	}
	public static function rechargePartDo($order_number, $remark, $charge_amount, $reason = '')
	{
		$porder = M('porder')->where(array('order_number' => $order_number, 'status' => array('in', '2,3,8,9,10,11')))->find();
		if (!$porder) {
			return rjson(1, '订单未找到');
		}
		Createlog::porderLog($porder['id'], $remark);
		$allmian = floatval(preg_replace('/\\D/', '', $porder['product_name']));
		if ($allmian <= $charge_amount || $charge_amount <= 0) {
			Createlog::porderLog($porder['id'], '部分充值面值不合法');
			return rjson(1, '部分充值面值不合法');
		}
		$compratio = ($allmian - $charge_amount) / $allmian;
		if ($compratio <= 0 || $compratio >= 1) {
			Createlog::porderLog($porder['id'], '部分充值退款比例不合法');
			return rjson(1, '部分充值退款比例不合法');
		}
		$refund_price = $compratio * $porder['total_price'];
		M('porder')->where(array('id' => $porder['id']))->setField(array('status' => 12, 'apply_refund' => 0, 'apply_break' => 0, 'refund_price' => $refund_price, 'finish_time' => time(), 'remark' => $reason, 'charge_amount' => $charge_amount));
		Createlog::porderLog($porder['id'], "部分充值，总面值：" . $allmian . '，成功面值：' . $charge_amount . "，总金额:￥" . $porder['total_price'] . '，应退款：￥' . $refund_price);
		if (C('AUTO_REFUND') == 1 && C('PART_REG_REFUND') == 1) {
			queue('app\\queue\\job\\Work@porderRefund', array('id' => $porder['id'], 'remark' => $remark, 'operator' => '系统'));
		}
		Notification::rechargePart($porder['id']);
		C('PART_REG_REBATE') == 1 && self::rebate($porder['id'], 1 - $compratio);
		return rjson(0, '操作成功');
	}
	public static function rechargeError($order_number, $remark)
	{
		$porder = M('porder')->where(array('order_number' => $order_number, 'status' => array('in', '2')))->find();
		if (!$porder) {
			return rjson(1, '订单未找到');
		}
		Createlog::porderLog($porder['id'], $remark);
		Createlog::porderLog($porder['id'], "接口充值异常|请人工与渠道方确认后手动操作订单");
		M('porder')->where(array('id' => $porder['id']))->setField(array('status' => 8));
		return rjson(0, '操作成功');
	}
	public static function getApiOrderNumber($order_number, $api_cur_index = 0, $api_cur_count = 0, $num = 1)
	{
		return $order_number . 'A' . $api_cur_count . ($api_cur_index + 1) . 'N' . $num;
	}
	public static function notification($porder_id)
	{
		$t1 = microtime(true);
		$porder = M('porder')->where(array('id' => $porder_id, 'status' => array('in', '3,4,5,6,7,12,13')))->field('id,status')->find();
		if (!$porder) {
			return rjson(1, '未查询到可回调订单');
		}
		if (in_array($porder['status'], array(4))) {
			$res = Notification::rechargeSus($porder['id']);
		} elseif (in_array($porder['status'], array(5, 6, 7))) {
			$res = Notification::rechargeFail($porder['id']);
		} elseif (in_array($porder['status'], array(12, 13))) {
			$res = Notification::rechargePart($porder['id']);
		} elseif (in_array($porder['status'], array(3))) {
			$res = Notification::rechargeIng($porder['id']);
		} else {
			return rjson(1, '状态不可回调');
		}
		$t2 = microtime(true);
		Createlog::porderLog($porder_id, "回调通知耗时：" . round($t2 - $t1, 3) . 's');
		return $res;
	}
	public static function refund($order_id, $remark, $operator)
	{
		$porder = M('porder')->where(array('id' => $order_id, 'status' => array('in', array(5, 12))))->find();
		if (!$porder) {
			return rjson(1, '未查询到可退款订单！');
		}
		if ($porder['refund_time']) {
			Createlog::porderLog($porder['id'], "退款失败|订单已经操作过退款，不可再申请！");
			return rjson(1, '订单已经操作过退款！');
		}
		if ($porder['refund_price'] > 0) {
			switch ($porder['pay_way']) {
				case PayWay::PAY_WAY_JSYS:
					$ret = PayWay::refund($porder['pay_way'], array('weixin_appid' => $porder['weixin_appid'], 'order_number' => $porder['order_number'], 'total_price' => $porder['total_price'], 'refund_price' => $porder['refund_price'], 'reason' => '充值失败退款', 'type' => 1));
					break;
				case PayWay::PAY_WAY_BLA:
					$ret = Balance::revenue($porder['customer_id'], $porder['refund_price'], '[退款]给账号:' . $porder['mobile'] . ',充值产品:' . $porder['title'] . "失败-退款，单号:" . $porder['order_number'], Balance::STYLE_REFUND, $operator);
					break;
				case PayWay::PAY_WAY_OFFL:
					$ret = rjson(0, '线下支付无需退款');
					break;
				case PayWay::PAY_WAY_H5YS:
					$ret = PayWay::refund($porder['pay_way'], array('weixin_appid' => $porder['weixin_appid'], 'order_number' => $porder['order_number'], 'total_price' => $porder['total_price'], 'refund_price' => $porder['refund_price'], 'reason' => '充值失败退款', 'type' => 2));
					break;
				case PayWay::PAY_WAY_ALIH5:
					$ret = PayWay::refund($porder['pay_way'], array('weixin_appid' => $porder['weixin_appid'], 'serial_number' => $porder['serial_number'], 'order_number' => $porder['order_number'], 'total_price' => $porder['total_price'], 'refund_price' => $porder['refund_price'], 'reason' => '充值失败退款', 'type' => 2));
					break;
				default:
					$ret = rjson(1, '不支持');
					break;
			}
			Createlog::porderLog($porder['id'], '退款结果|' . $ret['errmsg']);
			if ($ret['errno'] != 0) {
				Createlog::porderLog($porder['id'], "退款失败|退款金额：" . $porder['refund_price'] . "|" . $remark);
				return rjson(1, $ret['errmsg']);
			}
		}
		if ($porder['total_price'] == $porder['refund_price']) {
			M('porder')->where(array('id' => $porder['id']))->setField(array('status' => 6));
			Createlog::porderLog($porder['id'], "退款成功|全额退款金额：" . $porder['refund_price'] . "|" . $remark);
		} else {
			M('porder')->where(array('id' => $porder['id']))->setField(array('status' => 13));
			Createlog::porderLog($porder['id'], "退款成功|部分退款金额：" . $porder['refund_price'] . "|" . $remark);
		}
		M('porder')->where(array('id' => $porder['id']))->setField(array('refund_time' => time()));
		self::h5AgentChildRefund($porder['id']);
		Notification::refundSus($porder['id']);
		return rjson(0, "退款成功");
	}
	public static function compute_rebate($porder_id)
	{
		$porder = M('porder')->where(array('id' => $porder_id, 'status' => array('in', '1,2'), 'is_del' => 0))->find();
		if (!$porder) {
			return rjson(1, '未找到订单');
		}
		$customer = M('customer')->where(array('id' => $porder['customer_id'], 'is_del' => 0, 'status' => 1))->find();
		if (!$customer) {
			return rjson(1, '用户未找到');
		}
		$rebate_id = $customer['f_id'];
		if (!$rebate_id) {
			Createlog::porderLog($porder_id, '不返利,没有上级');
			return rjson(1, '无上级，无需返利');
		}
		$fcus = M('customer')->where(array('id' => $customer['f_id'], 'is_del' => 0, 'status' => 1))->find();
		if (!$fcus) {
			Createlog::porderLog($porder_id, '不返利，返利上级信息未查询到');
			return rjson(1, '不返利，返利上级信息未查询到');
		}
		$fdres1 = Product::computePrice($porder['product_id'], $customer['id']);
		$fdres2 = Product::computePrice($porder['product_id'], $fcus['id']);
		$prod1_price = $fdres1['data']['price'];
		$prod2_price = $fdres2['data']['price'];
		$rebate_price = $prod1_price - $prod2_price;
		if ($rebate_price > 0) {
			M('porder')->where(array('id' => $porder_id))->setField(array('rebate_id' => $rebate_id, 'rebate_price' => $rebate_price));
			Createlog::porderLog($porder_id, '[1]计算返利ID：' . $rebate_id . '，返利金额:￥' . $rebate_price);
		} else {
			Createlog::porderLog($porder_id, '[1]不返利,计算出金额：' . $rebate_price);
			return rjson(1, '不返利,计算出金额：' . $rebate_price);
		}
		$ffcus = M('customer')->where(array('id' => $fcus['f_id'], 'is_del' => 0, 'status' => 1))->field('id')->find();
		if ($ffcus) {
			$fdres3 = Product::computePrice($porder['product_id'], $ffcus['id']);
			$prod3_price = $fdres3['data']['price'];
			$rebate_price2 = $prod2_price - $prod3_price;
			if ($rebate_price2 > 0) {
				M('porder')->where(array('id' => $porder_id))->setField(array('rebate_id2' => $ffcus['id'], 'rebate_price2' => $rebate_price2));
				Createlog::porderLog($porder_id, '[2]上上级计算返利ID：' . $ffcus['id'] . '，返利金额:￥' . $rebate_price2);
			} else {
				Createlog::porderLog($porder_id, '[2]上上级不返利,计算出金额：' . $rebate_price2);
			}
		} else {
			Createlog::porderLog($porder_id, '[2]上上级不返利,没有查到用户信息');
		}
		return rjson(0, '返利设置成功');
	}
	public static function rebate($porder_id, $compratio = 1)
	{
		$porder = M('porder')->where(array('id' => $porder_id, 'status' => array('in', '4,12,13'), 'rebate_id' => array('gt', 0), 'rebate_price' => array('gt', 0), 'is_del' => 0, 'is_rebate' => 0))->find();
		if ($porder) {
			$rebate_price = $porder['rebate_price'] * $compratio;
			M('porder')->where(array('id' => $porder_id))->setField(array('is_rebate' => 1, 'rebate_time' => time(), 'rebate_price' => $rebate_price));
			Createlog::porderLog($porder_id, "返利给上级用户[" . $porder['rebate_id'] . "]，金额￥" . $rebate_price . "");
			Balance::revenue($porder['rebate_id'], $rebate_price, '[1]用户充值返利，单号' . $porder['order_number'], Balance::STYLE_REWARDS, '系统');
		}
		$porder2 = M('porder')->where(array('id' => $porder_id, 'status' => array('in', '4,12,13'), 'rebate_id2' => array('gt', 0), 'rebate_price2' => array('gt', 0), 'is_del' => 0, 'is_rebate2' => 0))->find();
		if ($porder2) {
			$rebate_price2 = $porder2['rebate_price2'] * $compratio;
			M('porder')->where(array('id' => $porder_id))->setField(array('is_rebate2' => 1, 'rebate_time' => time(), 'rebate_price2' => $rebate_price2));
			Createlog::porderLog($porder_id, "返利给上上级用户[" . $porder2['rebate_id2'] . "]，金额￥" . $rebate_price2 . "");
			Balance::revenue($porder2['rebate_id2'], $rebate_price2, '[2]用户充值返利，单号' . $porder2['order_number'], Balance::STYLE_REWARDS, '系统');
		}
		return rjson(0, '操作完成');
	}
	public static function agentExcelOrder($id)
	{
		$item = M('agent_proder_excel')->where(array('status' => 2, 'id' => $id))->find();
		if (!$item) {
			return rjson(1, '订单不可推送');
		}
		M('agent_proder_excel')->where(array('status' => 2, 'id' => $id))->setField(array('status' => 3));
		$res = PorderModel::createOrder($item['mobile'], $item['product_id'], array('prov' => $item['area'], 'city' => $item['city'], 'ytype' => $item['ytype'], 'id_card_no' => $item['id_card_no']), $item['customer_id'], Client::CLIENT_AGA, '', $item['out_trade_num']);
		if ($res['errno'] != 0) {
			M('agent_proder_excel')->where(array('id' => $item['id']))->setField(array('status' => 5, 'resmsg' => $res['errmsg']));
			return rjson(1, '下单失败,' . $res['errmsg']);
		}
		$aid = $res['data'];
		self::compute_rebate($aid);
		Createlog::porderLog($aid, "代理后台批量导入下单成功");
		$porder = M('porder')->where(array('id' => $aid, 'status' => 1))->field("id,order_number,mobile,product_id,total_price,create_time,guishu,title,out_trade_num")->find();
		if (!$porder) {
			Createlog::porderLog($aid, "该订单状态不可发起支付，状态码：" . $porder['status']);
			return rjson(1, "该订单状态不可发起支付");
		}
		$ret = Balance::expend($item['customer_id'], $porder['total_price'], "[支付]代理商后台为账号:" . $porder['mobile'] . ",充值产品:" . $porder['title'] . "，单号:" . $porder['order_number'], Balance::STYLE_ORDERS, '代理商_导入');
		if ($ret['errno'] != 0) {
			self::payFailCancelOrder($aid, "代理商导入下单时支付失败，取消订单，原因：" . $ret['errmsg']);
			M('agent_proder_excel')->where(array('id' => $item['id']))->setField(array('status' => 5, 'resmsg' => $ret['errmsg']));
			return rjson(1, '下单支付失败,' . $res['errmsg']);
		}
		Createlog::porderLog($aid, "余额支付成功");
		$porder = M('porder')->where(array('id' => $aid))->field("id,order_number")->find();
		M('agent_proder_excel')->where(array('id' => $item['id']))->setField(array('status' => 4, 'order_number' => $porder['order_number']));
		$noticy = new Notify();
		$noticy->balance($porder['order_number']);
		return rjson(1, '下单成功');
	}
	public static function agentApiPayPorder($porder_id, $customer_id, $notify_url)
	{
		self::where(array('id' => $porder_id))->setField(array('notify_url' => $notify_url));
		self::compute_rebate($porder_id);
		Createlog::porderLog($porder_id, "代理API下单成功");
		$porder = M('porder')->where(array('id' => $porder_id, 'status' => 1))->field("id,order_number,remark,mobile,product_id,total_price,create_time,guishu,title,out_trade_num")->find();
		if (!$porder) {
			Createlog::porderLog($porder_id, "该订单状态不可发起支付，状态码：" . $porder['status']);
			return rjson(1, "该订单状态不可发起支付");
		}
		$ret = Balance::expend($customer_id, $porder['total_price'], "[支付]api为账号:" . $porder['mobile'] . ",充值产品:" . $porder['title'] . "，单号:" . $porder['order_number'], Balance::STYLE_ORDERS, '用户自己api');
		if ($ret['errno'] != 0) {
			self::payFailCancelOrder($porder_id, "代理商API下单时支付失败，取消订单，原因：" . $ret['errmsg']);
			queue('app\\queue\\job\\Work@callFunc', array('class' => '\\app\\common\\library\\Notification', 'func' => 'rechargeFail', 'param' => $porder['id']), 10);
			return rjson($ret['errno'], $ret['errmsg']);
		}
		Createlog::porderLog($porder_id, "余额支付成功");
		$noticy = new Notify();
		$noticy->balance($porder['order_number']);
		return rjson(0, '操作成功');
	}
	public static function adminExcelOrder($id)
	{
		$cus = M('customer')->where(array('id' => C('PORDER_EXCEL_CUSID'), 'is_del' => 0))->find();
		if (!$cus) {
			return rjson(1, '未找到正确的导入用户ID,点击导入设置配置用户ID');
		}
		$item = M('proder_excel')->where(array('id' => $id, 'status' => 2))->find();
		if (!$item) {
			return rjson(1, '不可推送');
		}
		M('proder_excel')->where(array('status' => 2, 'id' => $id))->setField(array('status' => 3));
		$res = PorderModel::createOrder($item['mobile'], $item['product_id'], array('prov' => $item['area'], 'city' => $item['city'], 'ytype' => $item['ytype'], 'id_card_no' => $item['id_card_no']), $cus['id'], Client::CLIENT_ADM, '');
		if ($res['errno'] != 0) {
			M('proder_excel')->where(array('id' => $item['id']))->setField(array('status' => 5, 'resmsg' => $res['errmsg']));
			return rjson(1, '下单失败,' . $res['errmsg']);
		}
		$porder = M('porder')->where(array('id' => $res['data']))->field("id,order_number")->find();
		Createlog::porderLog($porder['id'], "总后台导入下单");
		M('proder_excel')->where(array('id' => $item['id']))->setField(array('status' => 4, 'order_number' => $porder['order_number']));
		$noticy = new Notify();
		$noticy->offline($porder['order_number']);
		return rjson('成功推送');
	}
	public static function h5AgentChildPay($porder_id)
	{
		$porder = M('porder')->where(array('id' => $porder_id, 'status' => 2, 'is_apart' => 0, 'h5fxpay_price' => 0, 'client' => array('in', array(Client::CLIENT_WX, Client::CLIENT_H5))))->where('weixin_appid', 'not null')->find();
		if (!$porder) {
			return rjson(500, '订单未找到');
		}
		$weixin = M('weixin')->where(array('appid' => $porder['weixin_appid'], 'type' => $porder['client'], 'is_del' => 0, 'customer_id' => array('gt', 0)))->find();
		if (!$weixin) {
			return rjson(500, '微信配置未找到');
		}
		$cus = M('customer')->where(array('id' => $weixin['customer_id'], 'is_del' => 0))->find();
		if (!$cus) {
			return rjson(1, '未找到用户信息');
		}
		$baseprice = M('product')->where(array('id' => $porder['product_id']))->value('price');
		$fdres = Product::computePrice($porder['product_id'], $cus['id']);
		$fd_price = $fdres['data']['price'];
		$total_price = $baseprice + $fd_price;
		$blret = Balance::expend($cus['id'], $total_price, "[支付]H5代理端id:[" . $cus['id'] . "]为账号:" . $porder['mobile'] . ",充值产品:" . $porder['title'] . "，单号:" . $porder['order_number'], Balance::STYLE_ORDERS, 'H5代理的客户');
		if ($blret['errno'] != 0) {
			return rjson(1, $blret['errmsg'], $blret['data']);
		}
		M('porder')->where(array('id' => $porder_id))->setField(array('h5fxpay_price' => $total_price));
		Createlog::porderLog($porder_id, "H5代理商id:[" . $cus['id'] . "],为该订单支付费用￥" . $total_price);
		return rjson(0, 'H5代理商成功支付');
	}
	public static function h5AgentChildRefund($porder_id)
	{
		$porder = M('porder')->where(array('id' => $porder_id, 'status' => array('in', '6,13'), 'client' => array('in', array(Client::CLIENT_WX, Client::CLIENT_H5)), 'h5fxpay_price' => array('gt', 0), 'is_apart' => array('in', array(0, 2))))->where('weixin_appid', 'not null')->find();
		if (!$porder) {
			return rjson(500, '订单未找到');
		}
		$weixin = M('weixin')->where(array('appid' => $porder['weixin_appid'], 'type' => $porder['client'], 'is_del' => 0, 'customer_id' => array('gt', 0)))->find();
		if (!$weixin) {
			return rjson(500, '微信配置未找到');
		}
		$cus = M('customer')->where(array('id' => $weixin['customer_id'], 'is_del' => 0))->find();
		if (!$cus) {
			return rjson(1, '未找到用户信息');
		}
		if ($porder['total_price'] <= 0 || $porder['h5fxpay_price'] <= 0 || $porder['refund_price'] <= 0) {
			Createlog::porderLog($porder_id, "H5代理商无需退款，退款金额0");
			return rjson(1, 'H5代理商无需退款，退款金额0');
		}
		$refund_price = round($porder['refund_price'] / $porder['total_price'] * $porder['h5fxpay_price'], 2);
		$blret = Balance::revenue($cus['id'], $refund_price, "[退款]H5代理端id:[" . $cus['id'] . "]为账号:" . $porder['mobile'] . ",充值产品:" . $porder['title'] . "失败退款，单号:" . $porder['order_number'], Balance::STYLE_REFUND, '系统');
		if ($blret['errno'] != 0) {
			Createlog::porderLog($porder_id, "订单退款到到H5代理商id:[" . $cus['id'] . "]失败，请人工处理,金额￥" . $refund_price);
			return rjson(1, $blret['errmsg'], $blret['data']);
		}
		Createlog::porderLog($porder_id, "订单退款到到H5代理商id:[" . $cus['id'] . "]成功,金额￥" . $refund_price);
		return rjson(0, 'H5代理商退款成功');
	}
	public static function clientApiPayPorder($porder_id, $customer_id)
	{
		$porder = M('porder')->where(array('id' => $porder_id))->field("id,order_number,remark,mobile,product_id,total_price,create_time,guishu,title,out_trade_num")->find();
		$ret = Balance::expend($customer_id, $porder['total_price'], "[支付]客户端为账号:" . $porder['mobile'] . ",充值产品:" . $porder['title'] . "，单号" . $porder['order_number'], Balance::STYLE_ORDERS, '客户端用户');
		if ($ret['errno'] != 0) {
			Createlog::porderLog($porder_id, '余额支付失败，' . $ret['errmsg']);
			return rjson($ret['errno'], $ret['errmsg']);
		}
		Createlog::porderLog($porder_id, "余额支付成功");
		$noticy = new Notify();
		$noticy->balance($porder['order_number']);
		return rjson(0, '支付成功');
	}
	public static function jinDongOrder($mobile, $product_id, $order_sn, $notify_url)
	{
		$cus = M('customer')->where(array('id' => C('JDCONFIG.userid'), 'is_del' => 0))->find();
		if (!$cus) {
			return rjson(1, '未找到正确的导入用户ID,点击导入设置配置用户ID');
		}
		$res = PorderModel::createOrder($mobile, $product_id, array('prov' => '', 'city' => '', 'ytype' => 0, 'id_card_no' => ''), $cus['id'], Client::CLIENT_ADM, '', $order_sn);
		if ($res['errno'] != 0) {
			return rjson($res['errno'], '下单失败,' . $res['errmsg'], $res['data']);
		}
		M('porder')->where(array('id' => $res['data']))->setField(array('notify_url' => $notify_url));
		$porder = M('porder')->where(array('id' => $res['data']))->field("id,order_number")->find();
		Createlog::porderLog($porder['id'], "京东下单成功");
		$noticy = new Notify();
		$noticy->offline($porder['order_number']);
		return rjson(0, '成功推送', $porder);
	}
	public static function kuaiShouOrder($mobile, $product_id, $order_sn, $biztype, $amount)
	{
		$cus = M('customer')->where(array('id' => C('KSCONFIG.userid'), 'is_del' => 0))->find();
		if (!$cus) {
			return rjson(1, '未找到正确的导入用户ID,点击导入设置配置用户ID');
		}
		$res = PorderModel::createOrder($mobile, $product_id, array('prov' => '', 'city' => '', 'ytype' => 0, 'id_card_no' => ''), $cus['id'], Client::CLIENT_ADM, '', $order_sn);
		if ($res['errno'] != 0) {
			return rjson($res['errno'], '下单失败,' . $res['errmsg'], $res['data']);
		}
		M('porder')->where(array('id' => $res['data']))->setField(array('kuaishou_biztype' => $biztype, 'kuaishou_amount' => $amount));
		$porder = M('porder')->where(array('id' => $res['data']))->field("id,order_number")->find();
		Createlog::porderLog($porder['id'], "快手下单成功");
		$noticy = new Notify();
		$noticy->offline($porder['order_number']);
		return rjson(0, '成功推送', $porder);
	}
	public static function apinotify_log($api, $out_trade_no, $data)
	{
		if (!$out_trade_no) {
			return false;
		}
		$log = M('apinotify_log')->where(array('api' => $api, 'out_trade_no' => $out_trade_no))->find();
		M('apinotify_log')->insertGetId(array('api' => $api, 'out_trade_no' => $out_trade_no, 'data' => var_export($data, true), 'create_time' => time()));
		if ($log) {
			return false;
		} else {
			return true;
		}
	}
	public static function childFinishTrigger($porder_id)
	{
		$porder = M('porder')->where(array('id' => $porder_id, 'status' => array('in', array(4, 5, 6)), 'is_del' => 0, 'is_apart' => 1))->find();
		if (!$porder || !$porder['apart_order_number']) {
			return rjson(1, '未找到订单');
		}
		$morder = M('porder')->where(array('order_number' => $porder['apart_order_number'], 'status' => 11, 'is_del' => 0))->find();
		if (!$morder) {
			return rjson(1, '没有主订单');
		}
		$othct = M('porder')->where(array('apart_order_number' => $porder['apart_order_number'], 'status' => array('not in', array(4, 5, 6)), 'is_del' => 0))->count();
		if ($othct > 0) {
			return rjson(1, '无需变化状态');
		}
		$susct = M('porder')->where(array('apart_order_number' => $porder['apart_order_number'], 'status' => 4, 'is_del' => 0))->count();
		$failct = M('porder')->where(array('apart_order_number' => $porder['apart_order_number'], 'status' => array('in', array(5, 6)), 'is_del' => 0))->count();
		if ($susct > 0 && $failct == 0) {
			return self::rechargeSus($morder['order_number'], '所有子单都已充值成功');
		}
		if ($failct > 0 && $susct == 0) {
			return self::rechargeFailDo($morder['order_number'], '所有子单都已充值失败');
		}
		if ($failct > 0 && $susct > 0) {
			$susmz = M('porder')->where(array('apart_order_number' => $porder['apart_order_number'], 'status' => 4, 'is_del' => 0))->sum("CAST(product_name as DECIMAL(10,2))");
			return self::rechargePartDo($morder['order_number'], '子单部分充值成功：' . $susmz, $susmz, '部分充值：' . $susmz);
		}
		return rjson(0, '处理完成');
	}
	public static function delayTimeOrderSub()
	{
		$porder = M('porder')->where(array('status' => 2, 'api_open' => 1, 'api_cur_index' => -1, 'api_cur_count' => 0, 'delay_time' => array('between', array(1, time()))))->field('id,delay_time')->select();
		if (!$porder) {
			return rjson(1, '没有需要处理的延时api订单');
		}
		foreach ($porder as $order) {
			M('porder')->where(array('id' => $order['id']))->setField(array('delay_time' => 0));
			Createlog::porderLog($order['id'], '延时订单开始执行提交api,到期时间：' . time_format($order['delay_time']));
			queue('app\\queue\\job\\Work@porderSubApi', $order['id']);
		}
		return rjson(0, '提交成功');
	}
    public static function applyCancelOrder($ids, $operator)
    {
        $porders = M('porder')->where(array('id' => array('in', $ids), 'apply_refund' => 0, 'status' => array('in', '2,3,10')))->select();

        if (!$porders) {
            return rjson(1, '订单不可申请撤单');
        }
        $counts = 0;
        $errmsg = '';
        foreach ($porders as $porder) {
            //	dump($porder);die;
        
            if ($porder['status']==3){
                Createlog::porderLog($porder['id'], "为订单申请退单，申请人：" . $operator);
                M('porder')->where(array('id' => $porder['id']))->setField(array('apply_refund' => 1));
                $res = self::cancelApiOrder($porder['id']);
                Createlog::porderLog($porder['id'], "为订单申请退单，申请人：" . json_encode($res));
                if ($res['errno'] == 0) {
                    Createlog::porderLog($porder['id'], "订单API申请取消成功|返回：" . json_encode($res['data'], true));

                    //  M('porder')->where(array('id' => $porder['id']))->setField(array('apply_refund' => 0));

                } else {
                    Createlog::porderLog($porder['id'], "订单API申请取消失败|返回：" . json_encode($res['data']),true);

                    Createlog::porderLog($porder['id'], "该订单进入退单等待期，不再提交后续api|该订单不会立马失败，当前api失败回调后订单才会自动失败，如果api回调充值成功，订单依然会成功");
                    M('porder')->where(array('id' => $porder['id']))->setField(array('apply_refund' => 1));
                }
                $counts++;
            } else {

                if (in_array($porder['status'], array(2, 10))) {
                    if ($porder['status'] == 2 && $porder['api_open'] == 1) {
                        $errmsg .= "订单" . $porder['order_number'] . "正在提交，暂时不可取消；";
                    }
                    self::rechargeFailDo($porder['order_number'], "取消订单|操作人：" . $operator);
                    $counts++;
                }
            }
        }
        if ($counts == 0) {
            return rjson(1, $errmsg);
        }
        return rjson(0, "成功操作" . $counts . "条");
    }


    public static function applyCancelOrders($ids, $operator)
	{
		$porders = M('porder')->where(array('id' => array('in', $ids), 'apply_refund' => 0, 'status' => array('in', '2,3,10')))->select();
		echo '开始退单！';
		if (!$porders) {
			return rjson(1, '订单不可申请撤单');
		}
		$counts = 0;
		$errmsg = '';
		foreach ($porders as $porder) {
			if (in_array($porder['status'], array(3))) {
				Createlog::porderLog($porder['id'], "为订单申请退单，|申请人：" . $operator);
				$res = self::cancelApiOrder($porder['id']);
				if ($res['errno'] == 0) {
					Createlog::porderLog($porder['id'], "订单API申请取消成功|返回：" . var_export($res['data'], true));
					self::rechargeFailDo($porder['order_number'], "取消订单|操作人：" . $operator);
				} else {
					Createlog::porderLog($porder['id'], "订单API申请取消失败|返回：" . var_export($res['data'], true));
					Createlog::porderLog($porder['id'], "订单进入退单等待期，不再提交后续api|该订单不会立马失败，当前api失败回调后订单才会自动失败，如果api回调充值成功，订单依然会成功");
					M('porder')->where(array('id' => $porder['id']))->setField(array('apply_refund' => 1));
				}
				$counts++;
			} else {
				if (in_array($porder['status'], array(2, 10))) {
					if ($porder['status'] == 2 && $porder['api_open'] == 1) {
						$errmsg .= "订单" . $porder['order_number'] . "正在提交，暂时不可取消；";
					}
					self::rechargeFailDo($porder['order_number'], "取消订单|操作人：" . $operator);
					$counts++;
				}
			}
		}
		if ($counts == 0) {
			return rjson(1, $errmsg);
		}
		return rjson(0, "成功操作" . $counts . "条");
	}
	public static function applyBreakOrder($ids, $operator)
	{
		$porders = M('porder')->where(array('id' => array('in', $ids), 'apply_refund' => 0, 'apply_break' => 0, 'status' => array('in', '3')))->select();
		if (!$porders) {
			return rjson(1, '订单不可申请中断');
		}
		$counts = 0;
		$errmsg = '';
		foreach ($porders as $porder) {
			Createlog::porderLog($porder['id'], "订单进入申请中断状态，不再提交后续api|该订单不会立马失败，当前api失败回调后订单才会自动变成压单中，如果api回调充值成功，订单依然会成功|" . $operator);
			M('porder')->where(array('id' => $porder['id']))->setField(array('apply_break' => 1));
			$counts++;
		}
		if ($counts == 0) {
			return rjson(1, $errmsg);
		}
		return rjson(0, "成功操作" . $counts . "条");
	}
	public static function timeOutCancelOrder()
	{
		$porder = M('porder')->where(array('status' => 1,'is_sb'=>0, 'client' => array('in', array(Client::CLIENT_WX, Client::CLIENT_H5, Client::CLIENT_AGA)), 'create_time' => array('lt', time() - 60 * 30)))->field('id')->select();

		if (!$porder) {
			return rjson(1, '没有需要处理的超时订单');
		}

		foreach ($porder as $order) {

            Createlog::porderLog($order['id'], "系统进入自动化处理订单时间，检测订单大于30分，现在做订单失败处理！".json_encode(array('status' => 1,'is_sb'=>0, 'client' => array('in', array(Client::CLIENT_WX, Client::CLIENT_H5, Client::CLIENT_AGA)), 'create_time' => array('lt', time() - 60 * 30))));
			self::payFailCancelOrder($order['id'], "订单超时未支付，系统自动取消");


		}

		return rjson(0, '处理完成');
	}
	public static function payFailCancelOrder($porder_id, $remark = '订单支付失败，系统取消订单')
	{
		$porder = M('porder')->where(array('id' => $porder_id, 'status' => 1))->field('id')->find();
		//增加订单自动检查

		if (!$porder) {
			Createlog::porderLog($porder_id, "支付失败取消订单的时候发生了错误:订单不是待支付状态");
			return rjson(1, '支付失败取消订单的时候发生了错误');
		}

		//$is_getordersta =self::apiCheck($porder_id);


		Createlog::porderLog($porder_id, $remark);
		M('porder')->where(array('id' => $porder_id))->setField(array('status' => 7, 'remark' => $remark, 'finish_time' => time()));
		return rjson(0, '取消成功');
	}




    public static function apiCheck($porder_id)
    {
        $porders = M('porder p')->join('reapi r', 'r.id=p.api_cur_id')->where(array('r.is_self_check' => 1, 'p.status' => 3,'id'=>$porder_id))->order('p.pegging_time asc ,p.create_time asc')->limit(1)->field('p.id,p.mobile,p.order_number,p.api_order_number,p.api_trade_num,p.api_cur_param_id,p.api_cur_id')->select();


        foreach ($porders as $k => $order) {
            $config = M('reapi')->where(array('id' => $order['api_cur_id']))->find();
            M('porder')->where(array('id' => $order['id']))->setField(array('pegging_time' => time()));
            $classname = 'Recharge\\' . ucfirst($config['callapi']);
            if (!class_exists($classname)) {
                continue;
            }
            $model = new $classname($config);
            if (!method_exists($model, 'selfcheck')) {
                continue;
            }
            $model->selfcheck($order);
        }
    }











	public static function cancelApiOrder($porder_id)
	{
		$porder = M('porder p')->join('reapi r', 'r.id=p.api_cur_id')->where(array('p.id' => $porder_id, 'p.status' => 3))->field('r.callapi,p.api_cur_param_id,p.api_cur_id,p.api_order_number')->find();
		if (!$porder) {
			return rjson(1, '不能操作取消');
		}
		$config = M('reapi')->where(array('id' => $porder['api_cur_id']))->find();
		$classname = 'Recharge\\' . ucfirst($config['callapi']);
		if (!class_exists($classname)) {
			return rjson(1, '系统错误，接口类:' . $classname . '不存在');
		}
		$model = new $classname($config);
		if (!method_exists($model, 'cancel')) {
			return rjson(1, '系统错误，接口类:' . $classname . '的取消方法（cancel）不存在');
		}
		return $model->cancel($porder['api_order_number']);
	}
	public static function apiSelfCheck()
	{
		$porders = M('porder p')->join('reapi r', 'r.id=p.api_cur_id')->where(array('r.is_self_check' => 1, 'p.status' => 3))->order('p.pegging_time asc ,p.create_time asc')->limit(100)->field('p.id,p.mobile,p.order_number,p.api_order_number,p.api_trade_num,p.api_cur_param_id,p.api_cur_id,r.name,r.param1,r.param2')->select();
		foreach ($porders as $k => $order) {
			$config = M('reapi')->where(array('id' => $order['api_cur_id']))->find();
			M('porder')->where(array('id' => $order['id']))->setField(array('pegging_time' => time()));
			$classname = 'Recharge\\' . ucfirst($config['callapi']);
			if (!class_exists($classname)) {
				continue;
			}
			$model = new $classname($config);
			if (!method_exists($model, 'selfcheck')) {
				continue;
			}
			$model->selfcheck($order);
		}
	}
	public static function untlock($id)
	{
		M('porder')->where(array('id' => $id))->setField(array('tlocking' => 0));
	}
	public static function jiemaCheckOrder()
	{
		$porders = M('porder')->where(array('is_del' => 0, 'status' => 3, 'is_jiema' => 1))->limit(100)->select();
		if (!$porders) {
			return djson(1, '订单未找到');
		}
		foreach ($porders as $porder) {
			$product = M('product')->where(array('id' => $porder['product_id']))->field('id,jmapi_id,jmapi_param_id')->find();
			if (!$product) {
				Createlog::porderLog($porder['id'], "接码检查状态：产品信息未找到");
				continue;
			}
			$config = M('jmapi')->where(array('id' => $product['jmapi_id'], 'is_del' => 0))->find();
			$param = M('jmapi_param')->where(array('id' => $product['jmapi_param_id']))->find();
			if (!$config || !$param) {
				Createlog::porderLog($porder['id'], "接码检查状态：接码api信息未找到");
				continue;
			}
			$classname = 'Jiema\\' . $config['callapi'];
			$model = new $classname($config);
			$param['extend_param1'] = $porder['extend_param1'];
			$param['extend_param2'] = $porder['extend_param2'];
			$param['order_number'] = $porder['order_number'];
			$model->check($porder['mobile'], $porder['order_number'], $param);
		}
		return rjson(0, '查询完成');
	}
}