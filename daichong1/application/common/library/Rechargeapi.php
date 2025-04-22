<?php

//decode by http://chiran.taobao.com/
namespace app\common\library;

use app\common\model\Porder;
use Util\Ispzw;
class Rechargeapi
{
	public static function recharge($porder_id)
	{
		$res = Porder::getCurApi($porder_id);
		if ($res['errno'] != 0) {
			return rjson($res['errno'], $res['errmsg']);
		}
		$api = $res['data']['api'];
		$cur_num = $res['data']['num'];
		$cur_index = $res['data']['index'];
		$porder = M('porder')->where(array('id' => $porder_id, 'status' => 2, 'api_open' => 1))->find();
		$config = M('reapi')->where(array('id' => $api['reapi_id']))->find();
		Createlog::porderLog($porder['id'], "查询到配置信息：" . json_encode($config, JSON_UNESCAPED_UNICODE));
		//绑定是否接到通知后再失败订单字段
           if($porder['is_sb']<1){
            $porder['is_sb'] = $config['is_nfystau'];
            M('porder')->where(array('id' => $porder_id))->setField(array('is_sb'=>$config['is_nfystau']));


        }



        $param = M('reapi_param')->where(array('id' => $api['param_id']))->find();
		if (!$config || !$param) {
			return rjson(1, '接口未找到');
		}
		$api_order_number = Porder::getApiOrderNumber($porder['order_number'], $porder['api_cur_index'], $porder['api_cur_count'], $cur_num);
		M('porder')->where(array('id' => $porder_id))->setField(array('api_order_number' => $api_order_number, 'apireq_time' => time(), 'api_cur_index' => $cur_index, 'api_cur_num' => $cur_num, 'api_cur_id' => $config['id'], 'api_cur_param_id' => $param['id']));
		$porder['api_order_number'] = $api_order_number;
		Createlog::porderLog($porder['id'], "准备提交到API的单号：" . $api_order_number);
		if (C('ISP_ZHUANW_SW') == 3 && $config['is_zwback'] == 1 && in_array($porder['type'], array(1, 2))) {
			$res = Ispzw::isZhuanw(C('ISP_ZHUANW_CFG.apikey'), $porder['mobile']);
			if ($res['errno'] == 0) {
				Createlog::porderLog($porder['id'], '携号转网查询：' . $res['errmsg']);
				Porder::rechargeFail($porder['order_number'], "提交接口|[" . $cur_index . '][' . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "，|检测到是转网号码|已被驳回", "转网号码驳回");
				return rjson(1, "订单手机号携号转网");
			} else {
				Createlog::porderLog($porder['id'], '携号转网查询：' . $res['errmsg']);
			}
		}
		if ($config['mb_limit_day'] > 0) {
			if ($config['mb_limit_price'] > 0) {
				$allprice = M('porder_apilog')->where(array('reapi_id' => $config['id'], 'account' => $porder['mobile'], 'state' => array('in', '0,1,3'), 'create_time' => array('egt', time() - intval($config['mb_limit_day']) * 86400)))->sum('CAST(product_name as DECIMAL(10,2))');
				if ($allprice >= $config['mb_limit_price']) {
					Porder::rechargeFail($porder['order_number'], "提交接口|[" . $cur_index . '][' . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "，|超出单号码" . $config['mb_limit_day'] . "天内面值" . $config['mb_limit_price'] . "的限制|已被驳回", "接口充值限量");
					return rjson(1, "超出单号码时间段内金额限制");
				}
			}
			if ($config['mb_limit_count'] > 0) {
				$allcount = M('porder_apilog')->where(array('reapi_id' => $config['id'], 'account' => $porder['mobile'], 'state' => array('in', '0,1,3'), 'create_time' => array('egt', time() - intval($config['mb_limit_day']) * 86400)))->count();
				if ($allcount >= $config['mb_limit_count']) {
					Porder::rechargeFail($porder['order_number'], "提交接口|[" . $cur_index . '][' . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "，|超出单号码" . $config['mb_limit_day'] . "天内" . $config['mb_limit_count'] . "单的限制|已被驳回", "接口充值限量");
					return rjson(1, "超出单号码时间段内单量限制");
				}
			}
		}
		if ($config['mb_alllimit_day'] > 0) {
			if ($config['mb_alllimit_price'] > 0) {
				$allprice = M('porder_apilog')->where(array('reapi_id' => $config['id'], 'state' => array('in', '0,1,3'), 'create_time' => array('egt', time() - intval($config['mb_alllimit_day']) * 86400)))->sum('CAST(product_name as DECIMAL(10,2))');
				if ($allprice >= $config['mb_alllimit_price']) {
					Porder::rechargeFail($porder['order_number'], "提交接口|[" . $cur_index . '][' . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "，|超出" . $config['mb_alllimit_day'] . "天内总面值" . $config['mb_alllimit_price'] . "的限制|已被驳回", "接口充值限量");
					return rjson(1, "超出时间段内总金额限制");
				}
			}
			if ($config['mb_alllimit_count'] > 0) {
				$allcount = M('porder_apilog')->where(array('reapi_id' => $config['id'], 'state' => array('in', '0,1,3'), 'create_time' => array('egt', time() - intval($config['mb_alllimit_day']) * 86400)))->count();
				if ($allcount >= $config['mb_alllimit_count']) {
					Porder::rechargeFail($porder['order_number'], "提交接口|[" . $cur_index . '][' . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "，|超出" . $config['mb_alllimit_day'] . "天内总数" . $config['mb_alllimit_count'] . "单的限制|已被驳回", "接口充值限量");
					return rjson(1, "超出时间段内总单量限制");
				}
			}
		}
		if (in_array($porder['type'], array(1, 2)) && isset($api['isp']) && $api['isp']) {
			$ispstr = getISPText($api['isp']);
			if (strpos($ispstr, $porder['isp']) === false) {
				$errmsg = "不在该接口的可充值运营商:" . $ispstr . "，当前：" . $porder['isp'];
				Porder::rechargeFail($porder['order_number'], "提交接口|[" . $cur_index . '][' . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "，" . $errmsg);
				return rjson(1, $errmsg);
			}
		}
		if (in_array($porder['type'], array(1, 2, 3))) {
			if ($porder['guishu_pro'] && $param['allow_pro'] && !strstr($param['allow_pro'], $porder['guishu_pro'])) {
				$errmsg = "不在该接口的可充值地区,允许:" . $param['allow_pro'] . ",当前：" . $porder['guishu_pro'];
				Porder::rechargeFail($porder['order_number'], "提交接口|[" . $cur_index . '][' . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "，" . $errmsg);
				return rjson(1, $errmsg);
			}
			if ($porder['guishu_city'] && $param['allow_city'] && !strstr($param['allow_city'], $porder['guishu_city'])) {
				$errmsg = "不在该接口的可充值地区,允许:" . $param['allow_city'] . ",当前：" . $porder['guishu_city'];
				Porder::rechargeFail($porder['order_number'], "提交接口|[" . $cur_index . '][' . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "，" . $errmsg);
				return rjson(1, $errmsg);
			}
			if ($porder['guishu_pro'] && $param['forbid_pro'] && strstr($param['forbid_pro'], $porder['guishu_pro'])) {
				$errmsg = "不在该接口的可充值地区,禁止:" . $param['forbid_pro'] . ",当前：" . $porder['guishu_pro'];
				Porder::rechargeFail($porder['order_number'], "提交接口|[" . $cur_index . '][' . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "，" . $errmsg);
				return rjson(1, $errmsg);
			}
			if ($porder['guishu_city'] && $param['forbid_city'] && strstr($param['forbid_city'], $porder['guishu_city'])) {
				$errmsg = "不在该接口的可充值地区,禁止:" . $param['forbid_city'] . ",当前：" . $porder['guishu_city'];
				Porder::rechargeFail($porder['order_number'], "提交接口|[" . $cur_index . '][' . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "，" . $errmsg);
				return rjson(1, $errmsg);
			}
		}
		if ($config['callapi'] == 'Test') {
			Createlog::porderLog($porder['id'], "提交接口空接口|" . $config['name'] . '-' . $param['desc'] . "|保持原状态");
			M('porder')->where(array('id' => $porder_id))->setField(array('cost' => $param['cost']));
			return rjson(0, '提交成功');
		}
		$ret = self::callApi($porder, $config, $param); //提交订单口
        Createlog::porderLog($porder['id'], "提交接口||返回|".json_encode($ret, JSON_UNESCAPED_UNICODE));
		if ($ret['errno'] == 1000) {
			Createlog::porderLog($porder['id'], "提交接口|[" . $cur_index . "][" . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "|重复|拦截任务");
			return rjson(1, $ret['errmsg']);
		}
		if ($ret['errno'] == 500) {
			Porder::rechargeError($porder['order_number'], "提交接口|[" . $cur_index . "][" . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "|异常|" . $ret['errmsg']);
			return rjson(1, $ret['errmsg']);
		}
		if ($ret['errno'] != 0) {
			Porder::rechargeFail($porder['order_number'], "提交接口|[" . $cur_index . "][" . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "|失败|" . $ret['errmsg']);
			return rjson(1, $ret['errmsg']);
		}
		Createlog::porderLog($porder['id'], "提交接口|[" . $cur_index . "][" . $cur_num . ']' . $config['name'] . '-' . $param['desc'] . "|成功|平台返回：" . json_encode($ret['data']));
		M('porder')->where(array('id' => $porder_id))->setField(array('status' => 3, 'cost' => $param['cost'], 'apireq_time' => time()));
		M('porder_apilog')->insertGetId(array('account' => $porder['mobile'], 'porder_id' => $porder['id'], 'reapi_id' => $config['id'], 'param_id' => $param['id'], 'api_order_number' => $api_order_number, 'create_time' => time(), 'product_name' => floatval(preg_replace('/\\D/', '', $porder['product_name'])), 'remark' => ''));
		return rjson(0, '提交成功');
	}

    /**
     * @param $porder
     * @param $config
     * @param $param
     * @return mixed
     * 根据API 开始下单
     */

	private static function callApi($porder, $config, $param)
	{
		if (S('SUB_' . $porder['api_order_number'])) {
			return rjson(1000, '重复提交');
		}
		S('SUB_' . $porder['api_order_number'], 1, array('expire' => 60));
		$classname = 'Recharge\\' . ucfirst($config['callapi']);

        Createlog::porderLog($porder['id'], '使用接口：' . $classname);
		if (!class_exists($classname)) {
			return rjson(1, '系统错误，接口类:' . $classname . '不存在');
		}


		$model = new $classname($config);
		if (!method_exists($model, 'recharge')) {
			return rjson(1, '系统错误，接口类:' . $classname . '的充值方法（recharge）不存在');
		}
		$param['oparam1'] = $porder['param1'];
		$param['oparam2'] = $porder['param2'];
		$param['oparam3'] = $porder['param3'];
		$param['logid'] =  $porder['id'];
//		$param['notify'] = C('WEB_URL') . 'api.php/apinotify/' . $config['callapi'];
		
		$param['notify'] = 'http://115.126.57.143/'. 'api.php/apinotify/' . $config['callapi'];
		$param['guishu_pro'] = $porder['guishu_pro'];
		$param['guishu_city'] = $porder['guishu_city'];

        Createlog::porderLog($porder['id'], '初始化数据：' . json_encode($param));
		return $model->recharge($porder['api_order_number'], $porder['mobile'], $param, $porder['isp']);
	}
}