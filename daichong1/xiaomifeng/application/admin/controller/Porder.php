<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use app\common\library\Createlog;
use app\common\library\Templetmsg;
use app\common\model\Balance;
use app\common\model\Porder as PorderModel;
use app\common\model\Product as ProductModel;
class Porder extends Admin
{
	public function _init()
	{
		if (!IS_CLI && (!function_exists('get_shoquan_key') || !S(md5(get_shoquan_key())))) {
			echo C('sqyc_msg');
			exit;
		}
	}
	public function index()
	{
		$map = $this->create_map();
		if (I('sort')) {
			$sort = I('sort');
		} else {
			$sort = "id desc";
		}
		$list = M('porder')->where($map)->field("*,(select username from dyr_customer where id=customer_id) as username,(select type_name from dyr_product_type where id=type) as type_name")->order($sort)->paginate(C('LIST_ROWS'));
		$this->assign('total_price', M('porder')->where($map)->sum("total_price"));
		$this->assign('sus_total_price', M('porder')->where($map)->where(array('status' => array('in', array(4))))->sum("total_price"));
		$this->assign('sus_cost', M('porder')->where($map)->where(array('status' => array('in', array(4))))->sum("cost"));
		$this->assign('rebate_price', M('porder')->where($map)->where(array('is_rebate' => 1, 'status' => array('in', array(4))))->sum("rebate_price") + M('porder')->where($map)->where(array('is_rebate' => 1, 'status' => array('in', array(4))))->sum("rebate_price2"));
		$this->assign('apsus_total_price', M('porder')->where($map)->where(array('status' => array('in', array(12, 13))))->sum("total_price-refund_price"));
		$this->assign('apsus_cost', M('porder')->where($map)->where(array('status' => array('in', array(12, 13))))->sum("cost*(1-refund_price/total_price)"));
		$this->assign('aprebate_price', M('porder')->where($map)->where(array('is_rebate' => 1, 'status' => array('in', array(12, 13))))->sum("rebate_price") + M('porder')->where($map)->where(array('is_rebate' => 1, 'status' => array('in', array(12, 13))))->sum("rebate_price2"));
		$this->assign('reapi', M('reapi')->where(array('is_del' => 0))->select());
		$this->assign('_list', $list);
		$this->assign('_total', $list->total());
		$this->assign('_types', M('product_type')->where(array('status' => 1))->order('sort asc,id asc')->select());
		return view();
	}
	public function batch_api()
	{
		$map = $this->create_map();
		$list = M('porder')->where($map)->field("id")->select();
		if (count($list) == 0) {
			return $this->error('没有筛选到订单');
		}
		$ids = array_column($list, 'id');
		$list = M('porder')->where(array('id' => array('in', $ids), 'status' => array('in', '2,10')))->field("id")->select();
		if (count($list) == 0) {
			return $this->error('没有筛选到订单（系统会自动过滤非待充值、压单状态的订单）');
		}
		$ids = array_column($list, 'id');
		$this->assign('_total', count($ids));
		$this->assign('_ids', implode(',', $ids));
		$this->assign('reapi', M('reapi')->where(array('is_del' => 0))->order('id desc')->select());
		$miangroup = M('porder')->where(array('id' => array('in', $ids)))->group('product_name')->field('product_name')->select();
		$this->assign('is_same', count($miangroup) == 1 ? 1 : 0);
		foreach ($miangroup as &$v) {
			$v['mianzhi'] = floatval(preg_replace('/\\D/', '', $v['product_name']));
			$this->assign('mianzhi', $v['mianzhi']);
		}
		$this->assign('miangroup', $miangroup);
		return view();
	}
	public function batch_api_do()
	{
		$ids_y = I('ids');
		if ($ids_y == '') {
			return $this->error('没有选择订单');
		}
		$porders = M('porder')->where(array('id' => array('in', $ids_y), 'tlocking' => 0))->field('id')->select();
		if (!$porders) {
			return $this->error('没有满足提交条件的订单');
		}
		$ids = array_column($porders, 'id');
		$param_ids = I('reapi_param_id/a');
		$num = I('num/a');
		if (!$param_ids || count($param_ids) == 0) {
			return $this->error('没有选择api');
		}
		$apiparam = array();
		foreach ($param_ids as $k => $v) {
			$apiparam[] = array('param_id' => $v, 'num' => $num[$k]);
		}
		$api_arr = M('reapi_param rp')->join('reapi r', 'r.id=rp.reapi_id')->where(array('rp.id' => array('in', $param_ids)))->field('rp.reapi_id,rp.id as param_id,1 as num')->orderRaw('find_in_set(param_id,"' . implode(',', $param_ids) . '")')->select();
		if (!$api_arr) {
			return $this->error('未找到对应的api');
		}
		M('porder')->where(array('id' => array('in', $ids)))->setField(array('tlocking' => 3));
		queue('app\\queue\\job\\Work@pordersSubApi', array('ids' => $ids, 'apiparam' => $apiparam, 'op' => $this->adminuser['nickname']));
		return $this->success('推送任务提交成功', 'back');
	}
	public function batch_apart()
	{
		$map = $this->create_map();
		$list = M('porder')->where($map)->field("id")->select();
		if (count($list) == 0) {
			return $this->error('没有筛选到订单!');
		}
		$ids = array_column($list, 'id');
		$list = M('porder')->where(array('id' => array('in', $ids), 'is_apart' => array('in', '0'), 'status' => array('in', '2,9,10')))->field("id")->select();
		if (count($list) == 0) {
			return $this->error('没有筛选到订单（系统会自动筛选待充值、部分充值、压单的订单,子订单和被拆过的单也不能再拆单）');
		}
		$ids = array_column($list, 'id');
		$this->assign('_total', count($ids));
		$this->assign('_ids', implode(',', $ids));
		$this->assign('reapi', M('reapi')->where(array('is_del' => 0))->order('id desc')->select());
		$miangroup = M('porder')->where(array('id' => array('in', $ids)))->group('product_name')->field('product_name')->select();
		$this->assign('is_same', count($miangroup) == 1 ? 1 : 0);
		foreach ($miangroup as &$v) {
			$v['mianzhi'] = floatval(preg_replace('/\\D/', '', $v['product_name']));
			$this->assign('mianzhi', $v['mianzhi']);
		}
		$this->assign('miangroup', $miangroup);
		$this->assign('cates', M('product_cate')->where(array('type' => M('porder')->where(array('id' => array('in', $ids)))->value('type')))->order('sort asc')->select());
		return view();
	}
	public function batch_apart_do()
	{
		$ids = I('ids');
		$pnum = M('porder')->where(array('id' => array('in', $ids)))->count();
		if ($ids == '' || $pnum == 0) {
			return $this->error('没有选择订单');
		}
		$product_ids = I('product_id/a');
		$num = I('num/a');
		if (!$product_ids || count($product_ids) == 0) {
			return $this->error('没有选择套餐');
		}
		$products = array();
		foreach ($product_ids as $k => $v) {
			$products[] = array('product_id' => $v, 'num' => $num[$k]);
		}
		$product_arr = M('product p')->where(array('p.id' => array('in', $product_ids), 'p.added' => 1, 'p.is_del' => 0))->field('p.id')->select();
		if (!$product_arr) {
			return $this->error('未找到对应的产品');
		}
		queue('app\\queue\\job\\Work@pordersBatchApart', array('ids' => explode(',', $ids), 'products' => $products, 'op' => $this->adminuser['nickname']));
		return $this->success('拆单任务提交成功', 'back');
	}
	public function log()
	{
		$list = M('porder_log')->where(array('porder_id' => I('id')))->order("id asc")->paginate(50);
		$this->assign('_list', $list);
		return view();
	}
	public function deletes()
	{
		if (M('porder')->where(array('id' => I('id')))->setField(array('is_del' => 1))) {
			Createlog::porderLog(I('id'), "删除成功|后台|" . $this->adminuser['nickname']);
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败');
		}
	}
	public function refund()
	{
		$ids = I('id/a');
		$porders = M('porder')->where(array('id' => array('in', $ids), 'status' => array('in', '5,12')))->select();
		if (!$porders) {
			return $this->error('未查询到订单');
		}
		$counts = 0;
		$errmsg = '';
		foreach ($porders as $porder) {
			if ($porder['refund_time']) {
				return rjson(1, '订单已经操作过退款！');
			}
			$ret = PorderModel::refund($porder['id'], "后台|" . session('user_auth')['nickname'], '管理员：' . session('user_auth')['nickname']);
			if ($ret['errno'] == 0) {
				$counts++;
			} else {
				$errmsg .= $porder['order_number'] . $ret['errmsg'] . ';';
			}
		}
		if ($counts == 0) {
			return $this->error('操作失败,' . $errmsg);
		}
		return $this->success("成功处理" . $counts . "条");
	}
	public function set_chenggong()
	{
		$ids = I('id/a');
		$porders = M('porder')->where(array('id' => array('in', $ids), 'status' => array('in', '2,3,8,9,10,11')))->select();
		if (!$porders) {
			return $this->error('未查询到订单');
		}
		$counts = 0;
		$errmsg = '';
		foreach ($porders as $porder) {
			$ret = PorderModel::rechargeSus($porder['order_number'], "充值成功|后台|" . $this->adminuser['nickname']);
			if ($ret['errno'] == 0) {
				$counts++;
			} else {
				$errmsg .= $ret['errmsg'] . ';';
			}
		}
		if ($counts == 0) {
			return $this->error('操作失败,' . $errmsg);
		}
		return $this->success("成功处理" . $counts . "条");
	}
	public function set_czing()
	{
		$ids = I('id/a');
		$porders = M('porder')->where(array('id' => array('in', $ids), 'status' => array('in', '2,8,9,10')))->select();
		if (!$porders) {
			return $this->error('未查询到订单');
		}
		$counts = 0;
		$errmsg = '';
		foreach ($porders as $porder) {
			Createlog::porderLog($porder['id'], "将订单设置为充值中|后台|" . $this->adminuser['nickname']);
			M('porder')->where(array('id' => $porder['id']))->setField(array('status' => 3));
			$counts++;
		}
		if ($counts == 0) {
			return $this->error('操作失败,' . $errmsg);
		}
		return $this->success("成功处理" . $counts . "条");
	}
	public function set_daicz()
	{
		$ids = I('id/a');
		$remark = I('prompt_remark');
		$porders = M('porder')->where(array('id' => array('in', $ids), 'status' => array('in', '1,3,4,8,10')))->select();
		if (!$porders) {
			return $this->error('未查询到订单');
		}
		$counts = 0;
		$errmsg = '';
		foreach ($porders as $porder) {
			Createlog::porderLog($porder['id'], "将订单设置为待充值|备注：" . $remark . "|后台:" . $this->adminuser['nickname']);
			M('porder')->where(array('id' => $porder['id']))->setField(array('status' => 2));
			$remark && M('porder')->where(array('id' => $porder['id']))->setField(array('remark' => $remark));
			$counts++;
		}
		if ($counts == 0) {
			return $this->error('操作失败,' . $errmsg);
		}
		return $this->success("成功处理" . $counts . "条");
	}
	public function set_shibai()
	{
		$ids = I('id/a');
		$porders = M('porder')->where(array('id' => array('in', $ids), 'status' => array('in', '2,3,8,9,10,11')))->select();
		if (!$porders) {
			return $this->error('未查询到订单');
		}
		$counts = 0;
		$errmsg = '';
		foreach ($porders as $porder) {
			$ret = PorderModel::rechargeFailDo($porder['order_number'], "充值失败|后台|" . $this->adminuser['nickname']);
			if ($ret['errno'] == 0) {
				$counts++;
			} else {
				$errmsg .= $ret['errmsg'] . ';';
			}
		}
		if ($counts == 0) {
			return $this->error('操作失败,' . $errmsg);
		}
		return $this->success("成功处理" . $counts . "条");
	}
	public function set_partsus()
	{
		$ids = I('id/a');
		$porders = M('porder')->where(array('id' => array('in', $ids), 'status' => array('in', '2,3,8,9,10,11')))->select();
		if (!$porders) {
			return $this->error('未查询到订单');
		}
		$counts = 0;
		$errmsg = '';
		foreach ($porders as $porder) {
			$allmian = floatval(preg_replace('/\\D/', '', $porder['product_name']));
			$charge_amount = floatval(I('prompt_remark'));
			if ($allmian <= $charge_amount) {
				$errmsg .= $porder['order_number'] . '部分充值面值不合法;';
				continue;
			}
			$ret = PorderModel::rechargePartDo($porder['order_number'], "部分充值成功|面值" . $charge_amount . "|后台|" . $this->adminuser['nickname'], $charge_amount, '部分充值：' . $charge_amount);
			if ($ret['errno'] == 0) {
				$counts++;
			} else {
				$errmsg .= $ret['errmsg'] . ';';
			}
		}
		if ($counts == 0) {
			return $this->error('操作失败,' . $errmsg);
		}
		return $this->success("成功处理" . $counts . "条");
	}
	public function set_aprefund()
	{
		$ids = I('id/a');
		return PorderModel::applyCancelOrder($ids, "后台-" . $this->adminuser['nickname']);
	}
	public function set_apbreak()
	{
		$ids = I('id/a');
		return PorderModel::applyBreakOrder($ids, "后台-" . $this->adminuser['nickname']);
	}
	public function batch_sms()
	{
		$map = $this->create_map();
		$list = M('porder')->where($map)->field("id")->select();
		if (count($list) == 0) {
			return $this->error('没有筛选到订单!');
		}
		$ids = array_column($list, 'id');
		$this->assign('_total', count($ids));
		$this->assign('_ids', implode(',', $ids));
		return view();
	}
	public function batch_sms_do()
	{
		$ids = I('ids');
		queue('app\\queue\\job\\Work@callFunc', array('class' => '\\app\\common\\library\\SmsNotice', 'func' => 'porderBatchSms', 'param' => json_encode(array('ids' => $ids, 'template' => I('template'), 'operator' => $this->adminuser['nickname']))));
		return $this->success("任务提交成功", 'back');
	}
	public function notification()
	{
		$ids = I('id/a');
		$porders = M('porder')->where(array('id' => array('in', $ids), 'status' => array('in', '3,4,5,6,7,12,13')))->field('id')->select();
		if (!$porders) {
			return $this->error('未查询到订单');
		}
		$sus_counts = 0;
		$fail_counts = 0;
		$errmsg = '';
		foreach ($porders as $porder) {
			$ret = PorderModel::notification($porder['id']);
			if ($ret['errno'] == 0) {
				$sus_counts++;
			} else {
				$fail_counts++;
				$errmsg .= $ret['errmsg'] . ';';
			}
		}
		if ($sus_counts == 0) {
			return $this->error('回调失败,' . $errmsg);
		}
		return $this->success("回调成功" . $sus_counts . "条/失败" . $fail_counts . "条");
	}
	public function untlock()
	{
		$ids = I('id/a');
		$porders = M('porder')->where(array('id' => array('in', $ids), 'tlocking' => array('gt', 0)))->field('id,tlocking')->select();
		if (!$porders) {
			return $this->error('未查询到订单');
		}
		$sus_counts = 0;
		foreach ($porders as $porder) {
			M('porder')->where(array('id' => $porder['id']))->setField(array('tlocking' => 0));
			Createlog::porderLog($porder['id'], '解除锁定|原锁定为' . $porder['tlocking'] . "|管理员：" . session('user_auth')['nickname']);
			$sus_counts++;
		}
		return $this->success("操作成功" . $sus_counts . "条");
	}
	private function create_map()
	{
		$map['is_del'] = 0;
		if ($key = trim(I('key'))) {
			$query_name = I('query_name');
			if ($query_name) {
				if (strpos($query_name, '.') !== false) {
					$qu_arr = explode('.', $query_name);
					$qu_rets = M($qu_arr[0])->where(array($qu_arr[1] => $key))->field('id')->select();
					$map[$qu_arr[2]] = array('in', array_column($qu_rets, 'id'));
				} else {
					$map[$query_name] = $key;
				}
			} else {
				$map['order_number|title|product_name|mobile|out_trade_num|guishu|api_order_number|remark|isp|apart_order_number|lable'] = array('like', '%' . $key . '%');
			}
		}
		if (I('status')) {
			$map['status'] = array('in', I('status'));
		}
		if (I('type')) {
			$map['type'] = I('type');
			$cates = M('product_cate')->where(array('type' => I('type')))->select();
			$this->assign('cates', $cates);
			if (I('cate')) {
				$product = M('product')->where(array('cate_id' => I('cate'), 'is_del' => 0))->select();
				$this->assign('products', $product);
				if (I('product_id')) {
					$map['product_id'] = I('product_id');
				}
			}
		}
		if (I('is_apart')) {
			$map['is_apart'] = intval(I('is_apart')) - 1;
		} else {
			$map['is_apart'] = array('in', array(0, 2));
		}
		if (I('apply_refund')) {
			$map['apply_refund'] = intval(I('apply_refund')) - 1;
		}
		if (I('isp')) {
			$map['isp'] = getISPText(I('isp'));
		}
		if (I('?apart_order_number')) {
			$map['apart_order_number'] = I('apart_order_number');
		}
		if (I('client')) {
			$map['client'] = I('client');
		}
		if (I('reapi_id')) {
			$map['api_cur_id'] = I('reapi_id');
		}
		if (I('pay_way')) {
			$map['pay_way'] = I('pay_way');
		}
		if (I('is_notification')) {
			$map['is_notification'] = intval(I('is_notification')) - 1;
		}
		if (I('customer_id')) {
			$map['customer_id'] = I('customer_id');
		}
		if (I('end_time') && I('begin_time')) {
			$time_style = I('time_style') ?: 'create_time';
			$map[$time_style] = array('between', array(strtotime(I('begin_time')), strtotime(I('end_time'))));
		}
		if (I('batch_mobile')) {
			$batch_order_number = str_replace(' ', '', str_replace(array("\r\n", "\r\n", "\r\n"), ",", I('batch_mobile')));
			$bt_mo = preg_grep('/\\S+/', explode(',', $batch_order_number));
			$bt_mo && ($map['mobile'] = array("in", $bt_mo));
		}
		if (I('batch_order_number')) {
			$batch_order_number = str_replace(' ', '', str_replace(array("\r\n", "\r\n", "\r\n"), ",", I('batch_order_number')));
			$bt_mo = preg_grep('/\\S+/', explode(',', $batch_order_number));
			$bt_mo && ($map['order_number'] = array("in", $bt_mo));
		}
		if (I('batch_api_order_number')) {
			$batch_order_number = str_replace(' ', '', str_replace(array("\r\n", "\r\n", "\r\n"), ",", I('batch_api_order_number')));
			$bt_mo = preg_grep('/\\S+/', explode(',', $batch_order_number));
			$bt_mo && ($map['api_order_number'] = array("in", $bt_mo));
		}
		return $map;
	}
	public function in_excel()
	{
		if (request()->isPost()) {
			set_time_limit(0);
			vendor("phpexcel.PHPExcel");
			$file = request()->file('excel');
			if (empty($file)) {
				return $this->error('请选择上传文件');
			}
			$info = $file->validate(array('size' => C('DOWNLOAD_UPLOAD.maxSize'), 'ext' => 'xlsx'))->move(C('DOWNLOAD_UPLOAD.movePath'));
			if ($info) {
				$exclePath = $info->getSaveName();
				$file_name = C('DOWNLOAD_UPLOAD.movePath') . DS . $exclePath;
				$objReader = \PHPExcel_IOFactory::createReader("Excel2007");
				$obj_PHPExcel = $objReader->load($file_name, $encode = 'utf-8');
				$excel_array = $obj_PHPExcel->getSheet(0)->toArray();
				array_shift($excel_array);
				$cus = M('customer')->where(array('id' => C('PORDER_EXCEL_CUSID'), 'is_del' => 0))->find();
				if (!$cus) {
					return $this->error('未找到正确的导入用户ID,点击导入设置配置用户ID');
				}
				$tirow = array();
				foreach ($excel_array as $k => $v) {
					$product_id = trim($v[0]);
					$mobile = trim($v[1]);
					$remark = $v[2];
					$out_trade_num = isset($v[3]) ? trim($v[3]) : '';
					$prov = isset($v[4]) ? trim($v[4]) : '';
					$id_card_no = isset($v[5]) ? trim($v[5]) : '';
					$ytype = isset($v[6]) ? trim($v[6]) : '';
					$city = isset($v[7]) ? trim($v[7]) : '';
					$hangstr = '  #第[' . ($k + 2) . '行]';
					$product_type = M('product')->where(array('id' => $product_id, 'is_del' => 0))->value('type');
					if (in_array($product_type, array(1, 2))) {
						$guishu = QCellCore($mobile);
						if ($guishu['errno'] != 0) {
							return $this->error('归属地未找到:' . $mobile . ',' . $guishu['errmsg'] . $hangstr, '', '', 20);
						}
						$map['p.isp'] = array('like', '%' . $guishu['data']['isp'] . '%');
						$tirow[$k]['isp'] = $guishu['data']['ispstr'];
						$tirow[$k]['guishu'] = $guishu['data']['prov'] . $guishu['data']['city'];
						$hangstr = $guishu['data']['ispstr'] . ',' . $hangstr;
						$prov = $guishu['data']['prov'];
						$city = $guishu['data']['city'];
					}
					$map['p.id'] = $product_id;
					$map['p.added'] = 1;
					$resdata = ProductModel::getProduct($map, $cus['id'], $prov, $city);
					if ($resdata['errno'] != 0) {
						return $this->error('未找到匹配的充值产品，' . $resdata['errmsg'] . '，套餐id:' . $product_id . '，手机：' . $mobile . '，' . $hangstr, '', '', 20);
					}
					$product = $resdata['data'];
					$tirow[$k]['product_id'] = $product_id;
					$tirow[$k]['mobile'] = $mobile;
					$tirow[$k]['remark'] = $remark;
					$tirow[$k]['area'] = $prov;
					$tirow[$k]['city'] = $city;
					$tirow[$k]['ytype'] = $ytype;
					$tirow[$k]['id_card_no'] = $id_card_no;
					$tirow[$k]['product_name'] = $product['name'];
					$tirow[$k]['total_price'] = $product['price'];
					$tirow[$k]['product_desc'] = $product['desc'];
					$tirow[$k]['api_open'] = $product['api_open'];
					$tirow[$k]['api_arr'] = $product['api_arr'];
					$tirow[$k]['api_cur_index'] = -1;
					$tirow[$k]['type'] = $product['type'];
					$tirow[$k]['status'] = 1;
					$tirow[$k]['hang'] = $k + 2;
					$tirow[$k]['out_trade_num'] = $out_trade_num;
					$tirow[$k]['create_time'] = time();
				}
				$sh = M('proder_excel')->insertAll($tirow);
				return $this->success('成功导入' . $sh . '条,即将刷新', U('porder_excel', array('status' => 1)));
			} else {
				return $this->error($file->getError());
			}
		} else {
			return $this->error('错误的请求方式');
		}
	}
	public function porder_excel()
	{
		$map = array();
		$status = I('?status') ? I('status') : 1;
		if ($status) {
			$map['status'] = $status;
		}
		$list = M('proder_excel')->where($map)->field("*,(select type_name from dyr_product_type where id=type) as type_name")->select();
		$alljy_pt = 0;
		$alljy_dr = 0;
		$total_price = 0;
		foreach ($list as &$item) {
			$item['ptjiaoyan'] = floatval(preg_replace('/\\D/', '', $item['product_name']));
			$item['drjiaoyan'] = floatval(preg_replace('/\\D/', '', $item['remark']));
			$item['jy_jg'] = $item['ptjiaoyan'] == $item['drjiaoyan'] ? 1 : 0;
			$alljy_dr += $item['drjiaoyan'];
			$alljy_pt += $item['ptjiaoyan'];
			$total_price += $item['total_price'];
		}
		$this->assign('alljy_pt', $alljy_pt);
		$this->assign('alljy_dr', $alljy_dr);
		$this->assign('alljy_jg', $alljy_pt == $alljy_dr ? 1 : 0);
		$this->assign('total_price', $total_price);
		$this->assign('reapi', M('reapi')->where(array('is_del' => 0))->select());
		$this->assign('_list', $list);
		$this->assign('status', $status);
		return view();
	}
	public function delete_porder_excel()
	{
		if (M('proder_excel')->where(array('id' => I('id')))->delete()) {
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败');
		}
	}
	public function empty_porder_excel()
	{
		$map['status'] = I('status');
		M('proder_excel')->where($map)->delete();
		return $this->success('清空成功');
	}
	public function push_excel()
	{
		set_time_limit(0);
		$cus = M('customer')->where(array('id' => C('PORDER_EXCEL_CUSID'), 'is_del' => 0))->find();
		if (!$cus) {
			return $this->error('未找到正确的导入用户ID,点击导入设置配置用户ID');
		}
		$list = M('proder_excel')->where(array('status' => 1))->field('id')->select();
		if (!$list) {
			return $this->error('没有可推送的数据');
		}
		M('proder_excel')->where(array('id' => array('in', array_column($list, 'id'))))->setField(array('status' => 2));
		queue('app\\queue\\job\\Work@adminPushExcel', $list);
		return $this->success('成功确认' . count($list) . '条！请刷新待推送列表查看', U('porder_excel', array('status' => 2)));
	}
	public function out_excel()
	{
		$map = $this->create_map();
		$ret = M('porder')->where($map)->field("*,(select type_name from dyr_product_type where id=type) as type_name")->order("create_time desc")->select();
		$field_arr = array(array('title' => '单号', 'field' => 'order_number'), array('title' => '商户单号', 'field' => 'out_trade_num'), array('title' => '类型', 'field' => 'type_name'), array('title' => '产品ID', 'field' => 'product_id'), array('title' => '产品', 'field' => 'product_name'), array('title' => '充值账号', 'field' => 'mobile'), array('title' => '客户ID', 'field' => 'customer_id'), array('title' => '客户端', 'field' => 'client'), array('title' => '归属地', 'field' => 'guishu'), array('title' => '运营商', 'field' => 'isp'), array('title' => '状态', 'field' => 'status'), array('title' => '总金额', 'field' => 'total_price'), array('title' => 'api成本', 'field' => 'cost'), array('title' => '当前api', 'field' => 'cur_apiinfo'), array('title' => '支付方式', 'field' => 'pay_way'), array('title' => '支付时间', 'field' => 'pay_time'), array('title' => '下单时间', 'field' => 'create_time'), array('title' => '备注', 'field' => 'remark'), array('title' => '标签', 'field' => 'lable'), array('title' => '回调地址', 'field' => 'notify_url'), array('title' => '回调时间', 'field' => 'notification_time'), array('title' => '扩展1', 'field' => 'param1'), array('title' => '扩展2', 'field' => 'param2'), array('title' => '扩展3', 'field' => 'param3'));
		foreach ($ret as $key => $vo) {
			$ret[$key]['status'] = C('PORDER_STATUS')[$vo['status']];
			$ret[$key]['pay_way'] = C('PAYWAY')[$vo['pay_way']];
			$ret[$key]['client'] = C('CLIENT_TYPE')[$vo['client']];
			$ret[$key]['pay_time'] = time_format($vo['pay_time']);
			$ret[$key]['create_time'] = time_format($vo['create_time']);
			$ret[$key]['notification_time'] = time_format($vo['notification_time']);
			$ret[$key]['cur_apiinfo'] = PorderModel::getCurApiInfos($vo['api_arr'], $vo['api_cur_index'], $vo['api_open']);
		}
		$this->exportToExcel('充值订单报表' . time(), $field_arr, $ret);
	}
	public function rihuizong()
	{
		if (I('end_time') && I('begin_time')) {
			$start_time = strtotime(date("Y-m-d", strtotime(I('begin_time'))));
			$end_time = strtotime(date("Y-m-d", strtotime(I('end_time')))) + 86400;
		} else {
			$start_time = strtotime(date("Y-m-d", strtotime("-6 day")));
			$end_time = strtotime(date("Y-m-d", time())) + 86400;
		}
		$data = array();
		while (1) {
			$arr['date'] = date("Y-m-d", $end_time - 1);
			$arr['date_end'] = date("Y-m-d", $end_time);
			$arr['all_price'] = M('porder')->where(array('status' => array('in', '2,3,4,5,6,8,9,10,11,12,13'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
			$arr['all_count'] = M('porder')->where(array('status' => array('in', '2,3,4,5,6,8,9,10,11,12,13'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
			$arr['all_amount'] = M('porder')->where(array('status' => array('in', '2,3,4,5,6,8,9,10,11,12,13'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum("CAST(product_name as DECIMAL(10,2))");
			$arr['wait_count'] = M('porder')->where(array('status' => array('in', '2,10'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
			$arr['wait_price'] = M('porder')->where(array('status' => array('in', '2,10'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
			$arr['wait_amount'] = M('porder')->where(array('status' => array('in', '2,10'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum("CAST(product_name as DECIMAL(10,2))");
			$arr['sus_count'] = M('porder')->where(array('status' => array('in', '4'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
			$arr['sus_price'] = M('porder')->where(array('status' => array('in', '4'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
			$arr['sus_amount'] = M('porder')->where(array('status' => array('in', '4'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum("CAST(product_name as DECIMAL(10,2))");
			$arr['yic_count'] = M('porder')->where(array('status' => array('in', '8'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
			$arr['yic_price'] = M('porder')->where(array('status' => array('in', '8'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
			$arr['yic_amount'] = M('porder')->where(array('status' => array('in', '8'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum("CAST(product_name as DECIMAL(10,2))");
			$arr['ing_count'] = M('porder')->where(array('status' => array('in', '3'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
			$arr['ing_price'] = M('porder')->where(array('status' => array('in', '3'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
			$arr['ing_amount'] = M('porder')->where(array('status' => array('in', '3'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum("CAST(product_name as DECIMAL(10,2))");
			$arr['fail_count'] = M('porder')->where(array('status' => array('in', '5,6'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
			$arr['fail_price'] = M('porder')->where(array('status' => array('in', '5,6'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
			$arr['fail_amount'] = M('porder')->where(array('status' => array('in', '5,6'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum("CAST(product_name as DECIMAL(10,2))");
			$arr['part_count'] = M('porder')->where(array('status' => array('in', '12,13'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
			$arr['part_price'] = M('porder')->where(array('status' => array('in', '12,13'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
			$arr['part_amount'] = M('porder')->where(array('status' => array('in', '12,13'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum("CAST(product_name as DECIMAL(10,2))");
			$arr['chai_count'] = M('porder')->where(array('status' => array('in', '11'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
			$arr['chai_price'] = M('porder')->where(array('status' => array('in', '11'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
			$arr['chai_amount'] = M('porder')->where(array('status' => array('in', '11'), 'create_time' => array('between', array($end_time - 86400, $end_time - 1)), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum("CAST(product_name as DECIMAL(10,2))");
			$arr['sus_ratio'] = $arr['all_count'] ? round($arr['sus_count'] / $arr['all_count'] * 100, 2) : 0;
			$arr['jiakuan'] = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 1, 'create_time' => array('between', array($end_time - 86400, $end_time - 1))))->sum('money');
			$arr['koukuan'] = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 2, 'create_time' => array('between', array($end_time - 86400, $end_time - 1))))->sum('money');
			$data[] = $arr;
			$end_time -= 86400;
			if ($start_time == $end_time) {
				break;
			}
		}
		$this->assign('_list', $data);
		return view();
	}
	public function complaint()
	{
		$map = array();
		if ($key = I('key')) {
			$map['c.name|c.mobile|c.issue|p.order_number|p.mobile'] = array('like', '%' . $key . '%');
		}
		if (I('status')) {
			$map['status'] = intval(I('status'));
		}
		$list = M('porder_complaint c')->join('porder p', 'p.id=c.porder_id')->where($map)->order("c.id desc")->field('c.*,p.mobile as pmobile,p.order_number')->paginate(C('LIST_ROWS'));
		$this->assign('_list', $list);
		return view();
	}
	public function deal_complaint()
	{
		$dafu = I('dafu');
		$com = M('porder_complaint')->where(array('id' => I('id')))->find();
		if (!$com) {
			return $this->error('未找到投诉信息');
		}
		if (!$dafu) {
			return $this->error('请输入答复内容');
		}
		M('porder_complaint')->where(array('id' => $com['id']))->setField(array('dafu' => $dafu, 'status' => 2));
		M('porder')->where(array('id' => $com['porder_id']))->setField(array('remark' => '投诉处理结果:' . $dafu));
		Createlog::porderLog($com['porder_id'], '投诉处理答复:' . $dafu . '|后台|' . $this->adminuser['nickname']);
		Templetmsg::yewuNoc($com['customer_id'], $com['name'] . '，您好，投诉内容：' . $com['issue'], '投诉管理部门', time_format(time()), '已办理', $dafu);
		return djson(0, '已通知客户');
	}
	public function lable()
	{
		if (request()->isPost()) {
			$ids = I('id/a');
			$porders = M('porder')->where(array('id' => array('in', $ids)))->field('id,lable')->select();
			if (!$porders) {
				return $this->error('订单未找到');
			}
			$lable = I('lable');
			$prompt_remark = I('prompt_remark');
			foreach ($porders as $porder) {
				if ($prompt_remark) {
					M('porder')->where(array('id' => $porder['id']))->setField(array('lable' => $porder['lable'] ? $porder['lable'] . ',' . $prompt_remark : $prompt_remark));
					Createlog::porderLog($porder['id'], '批量增加标签:' . $prompt_remark . '|后台|' . $this->adminuser['nickname']);
				} else {
					M('porder')->where(array('id' => $porder['id']))->setField(array('lable' => $lable));
					Createlog::porderLog($porder['id'], '设置标签:' . $lable . '|后台|' . $this->adminuser['nickname']);
				}
			}
			return $this->success('添加成功');
		} else {
			$info = M('porder')->where(array('id' => I('id')))->find();
			$this->assign('info', $info);
			$this->assign('lables', explode(',', C('PORDER_LABLES')));
			return view();
		}
	}
	public function remarks()
	{
		if (request()->isPost()) {
			$ids = I('id/a');
			$porders = M('porder')->where(array('id' => array('in', $ids)))->field('id,remark')->select();
			if (!$porders) {
				return $this->error('订单未找到');
			}
			$prompt_remark = I('prompt_remark');
			foreach ($porders as $porder) {
				M('porder')->where(array('id' => $porder['id']))->setField(array('remark' => $prompt_remark));
				Createlog::porderLog($porder['id'], '批量修改备注|修改后:' . $prompt_remark . '|修改前：' . $porder['remark'] . '|后台|' . $this->adminuser['nickname']);
			}
			return $this->success('修改成功');
		} else {
			return $this->error('请求方式错误');
		}
	}
	public function upstatus()
	{
		if (request()->isPost()) {
			$porder = M('porder')->where(array('id' => I('id')))->find();
			if (!$porder) {
				return $this->error('订单未找到');
			}
			$statusarr = C('PORDER_STATUS');
			$status = I('status');
			$remark = I('remark');
			if (!$remark) {
				return $this->error('备注必填');
			}
			if (!isset($statusarr[$status])) {
				return $this->error('修改状态错误');
			}
			$data = M('porder')->where(array('id' => $porder['id']))->setField(array('remark' => $remark, 'status' => $status));
			if ($data) {
				Createlog::porderLog($porder['id'], '修改状态|原状态：' . $statusarr[$porder['status']] . '，修改后状态：' . $statusarr[$status] . "|备注：" . $remark . '|后台|' . $this->adminuser['nickname']);
				return $this->success('修改成功');
			} else {
				return $this->error('修改失败');
			}
		} else {
			$info = M('porder')->where(array('id' => I('id')))->find();
			$this->assign('info', $info);
			return view();
		}
	}
	public function mobile_blacklist()
	{
		$map = array();
		if ($key = I('key')) {
			$map['mobile'] = array('like', '%' . $key . '%');
		}
		$list = M('mobile_blacklist')->where($map)->order("id desc")->paginate(C('LIST_ROWS'));
		$this->assign('_list', $list);
		return view();
	}
	public function edit_moible_blacklist()
	{
		if (request()->isPost()) {
			$arr['mobile'] = I('post.mobile');
			$arr['limit_time'] = intval(I('limit_day')) * 86400 + time();
			$aid = M('mobile_blacklist')->insertGetId($arr);
			if ($aid) {
				return $this->success('拉黑成功');
			} else {
				return $this->error('拉黑失败');
			}
		} else {
			$this->assign('heidays', array(30, 90, 365, 3650));
			return view();
		}
	}
	public function del_moible_blacklist()
	{
		if (M('mobile_blacklist')->where(array('id' => I('id')))->delete()) {
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败');
		}
	}
}