<?php

//decode by http://chiran.taobao.com/
namespace app\agent\controller;

use app\api\controller\Notify;
use app\common\library\Createlog;
use app\common\model\Balance;
use app\common\model\Client;
use app\common\model\Porder as PorderModel;
use app\common\model\Product;
use app\common\model\Product as ProductModel;
class Porder extends Admin
{
	public function index()
	{

		$map = $this->create_map();
		if (I('sort')) {
			$sort = I('sort');
		} else {
			$sort = "id desc";
		}
		$list = D('porder')->where($map)->field("*,(select type_name from dyr_product_type where id=type) as type_name")->order($sort)->paginate(C('LIST_ROWS'));
		$this->assign('total_price', M('porder')->where($map)->sum("total_price"));
		$this->assign('_list', $list);
		$this->assign('_total', $list->total());
		$this->assign('_types', M('product_type')->where(array('status' => 1))->order('sort asc,id asc')->select());
		$this->assign('agent_cancel_sw', C('AGENT_CANCEL_SW'));

		return view();
	}
	private function create_map()
	{
		$map['is_del'] = 0;
		$map['is_apart'] = array('in', array(0, 2));
		$map['customer_id'] = $this->user['id'];
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
				$map['order_number|title|product_name|mobile|out_trade_num|api_order_number|guishu|remark|isp'] = array('like', '%' . $key . '%');
			}
		}
		if (I('status')) {
			$map['status'] = array('in', I('status'));
		} elseif (I('status2')) {
			$map['status'] = array('in', I('status2'));
		}
		if (I('is_notification')) {
			$map['is_notification'] = intval(I('is_notification')) - 1;
		}
		if (I('apply_refund')) {
			$map['apply_refund'] = intval(I('apply_refund')) - 1;
		}
		if (I('type')) {
			$map['type'] = I('type');
		}
		if (I('isp')) {
			$map['isp'] = getISPText(I('isp'));
		}
		if (I('end_time') && I('begin_time')) {
			$map['create_time'] = array('between', array(strtotime(I('begin_time')), strtotime(I('end_time'))));
		}
		if (I('excel_id')) {
			$porderes = M('agent_proder_excel')->where(array('customer_id' => $this->user['id'], 'excel_id' => I('excel_id')))->field('order_number')->select();
			$map['order_number'] = array('in', array_column($porderes, 'order_number'));
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
		if (I('batch_out_trade_num')) {
			$batch_order_number = str_replace(' ', '', str_replace(array("\r\n", "\r\n", "\r\n"), ",", I('batch_out_trade_num')));
			$bt_mo = preg_grep('/\\S+/', explode(',', $batch_order_number));
			$bt_mo && ($map['out_trade_num'] = array("in", $bt_mo));
		}
		return $map;
	}
	public function out_excel()
	{
		$map = $this->create_map();
		$ret = M('porder')->where($map)->field('*,(select type_name from dyr_product_type where id=type) as type_name')->order("create_time desc")->select();
		$field_arr = array(array('title' => '系统单号', 'field' => 'order_number'), array('title' => '商户单号', 'field' => 'out_trade_num'), array('title' => '类型', 'field' => 'type_name'), array('title' => '产品ID', 'field' => 'product_id'), array('title' => '产品', 'field' => 'product_name'), array('title' => '手机', 'field' => 'mobile'), array('title' => '归属地', 'field' => 'guishu'), array('title' => '运营商', 'field' => 'isp'), array('title' => '状态', 'field' => 'status'), array('title' => '总金额', 'field' => 'total_price'), array('title' => '支付时间', 'field' => 'pay_time'), array('title' => '下单时间', 'field' => 'create_time'), array('title' => '完成时间', 'field' => 'finish_time'), array('title' => '备注', 'field' => 'remark'), array('title' => '用户标记', 'field' => 'user_lable'), array('title' => '回调地址', 'field' => 'notify_url'), array('title' => '回调时间', 'field' => 'notification_time'), array('title' => '凭证', 'field' => 'voucher'));
		foreach ($ret as $key => $vo) {
			$ret[$key]['status'] = C('PORDER_STATUS')[$vo['status']];
			$ret[$key]['pay_time'] = time_format($vo['pay_time']);
			$ret[$key]['create_time'] = time_format($vo['create_time']);
			$ret[$key]['finish_time'] = time_format($vo['finish_time']);
			$ret[$key]['notification_time'] = time_format($vo['notification_time']);
			$ret[$key]['voucher'] = $vo['status'] == 4 ? C('WEB_URL') . "yrapi.php/open/voucher/id/" . $vo['id'] . '.html' : '';
		}
		$this->exportToExcel('充值订单报表' . time(), $field_arr, $ret);
	}
	public function orders()
	{
		$areas = M('electricity_city')->where(array('is_del' => 0, 'pid' => 0))->order('initial asc,sort asc')->select();
		$this->assign('areas', $areas);
		$this->assign('ytypes', C('ELE_YTYPE'));
		return view();
	}
	public function get_city()
	{
		$areas = M('electricity_city')->where(array('is_del' => 0, 'pid' => I('pid')))->order('initial asc,sort asc')->select();
		return rjson(0, 'ok', $areas);
	}
    public function set_aprefunds()
    {
        $ids = I('id/a');
        return PorderModel::applyCancelOrder($ids, "后台-" . $this->user['id']);
    }

    public function get_product()
	{
		$data = array();
		$types = M('product_type')->where(array('status' => 1))->order('sort asc,id asc')->select();
		foreach ($types as $key => $type) {
			$map = array('p.is_del' => 0, 'p.added' => 1);
			$map['p.type'] = $type['id'];
			$map['p.show_style'] = array('in', '1,3');
			$prov = '';
			$city = '';
			if (in_array($type['id'], array(1, 2)) && ($mobile = I('mobile'))) {
				$guishu = QCellCore($mobile);
				if ($guishu['errno'] == 0) {
					$guishu = Product::Ispzhan($mobile, $this->user['id'], $guishu);
					$map['p.isp'] = array('like', '%' . $guishu['data']['isp'] . '%');
					$prov = $guishu['data']['prov'];
					$city = $guishu['data']['city'];
				}
			}
			$typec = ProductModel::getTypec($type['typec_id']);
			$resdata = ProductModel::getProducts($map, $this->user['id'], $prov, $city);
			$lists = $resdata['data'];
			$data[] = array('type' => $type['id'], 'name' => $type['type_name'], 'typec' => $typec, 'lists' => $lists);
		}
		return djson(0, 'ok', array('product' => $data));
	}
	public function get_guishu()
	{
		$mobile = I('mobile');
		$guishu = QCellCore($mobile);
		if ($guishu['errno'] != 0) {
			return djson($guishu['errno'], $guishu['errmsg']);
		}
		$guishu = Product::Ispzhan($mobile, $this->user['id'], $guishu);
		return djson(0, 'ok', $guishu['data']);
	}
	public function create_order()
	{
		$mobile = trim(I('mobile'));
		$product_id = I('product_id');
		$area = trim(I('area'));
		$id_card_no = trim(I('id_card_no'));
		$city = trim(I('city'));
		$ytype = I('ytype') ? trim(I('ytype')) : 1;
		$param1 = trim(I('param1'));
		$param2 = trim(I('param2'));
		$param3 = trim(I('param3'));
		$res = PorderModel::createOrder($mobile, $product_id, array('prov' => $area, 'city' => $city, 'ytype' => $ytype, 'id_card_no' => $id_card_no, 'param1' => $param1, 'param2' => $param2, 'param3' => $param3), $this->user['id'], Client::CLIENT_AGA, '');
		if ($res['errno'] != 0) {
			return djson($res['errno'], $res['errmsg'], $res['data']);
		}
		$aid = $res['data'];
		PorderModel::compute_rebate($aid);
		Createlog::porderLog($aid, "代理商后台下单成功");
		$porder = M('porder')->where(array('id' => $aid))->field("id,order_number,mobile,product_id,total_price,create_time,guishu,title,out_trade_num")->find();
		$ret = Balance::expend($this->user['id'], $porder['total_price'], "[支付]代理商后台为账号：" . $porder['mobile'] . ",充值产品：" . $porder['title'] . "，单号" . $porder['order_number'], Balance::STYLE_ORDERS, '代理商_手工');
		if ($ret['errno'] != 0) {
			PorderModel::payFailCancelOrder($aid, "代理商后台下单时支付失败，取消订单，原因：" . $ret['errmsg']);
			return djson($ret['errno'], $ret['errmsg']);
		}
		Createlog::porderLog($aid, "余额支付成功");
		$noticy = new Notify();
		$noticy->balance($porder['order_number']);
		return djson(0, "下单成功");
	}
	public function order_batch()
	{
		return view();
	}
	public function excels()
	{
		$map['customer_id'] = $this->user['id'];
		if (I('key')) {
			$map['name'] = array('like', '%' . I('key') . '%');
		}
		if (I('type')) {
			$map['type'] = I('type');
		}
		if (I('end_time') && I('begin_time')) {
			$map['create_time'] = array('between', array(strtotime(I('begin_time')), strtotime(I('end_time'))));
		}
		$list = M('agent_excel')->where($map)->order('id desc')->paginate(C('LIST_ROWS'))->each(function ($item, $key) {
			$es = M('agent_proder_excel')->where(array('excel_id' => $item['id']))->field('order_number')->select();
			$item['all_count'] = M('agent_proder_excel')->where(array('excel_id' => $item['id']))->count();
			$item['weidao_count'] = M('agent_proder_excel')->where(array('excel_id' => $item['id'], 'status' => 1))->count();
			$item['daoruing_count'] = M('agent_proder_excel')->where(array('excel_id' => $item['id'], 'status' => array('in', array(2, 3))))->count();
			$item['daorus_sus_count'] = M('agent_proder_excel')->where(array('excel_id' => $item['id'], 'status' => array('in', 4)))->count();
			$item['daorus_fail_count'] = M('agent_proder_excel')->where(array('excel_id' => $item['id'], 'status' => array('in', 5)))->count();
			$porders = M('porder')->where(array('customer_id' => $this->user['id'], 'order_number' => array('in', array_column($es, 'order_number'))))->select();
			$item['daoru_count'] = count($porders);
			$item['ing_count'] = M('porder')->where(array('id' => array('in', array_column($porders, 'id')), 'status' => array('in', '2,3,8,9,10,11')))->count();
			$item['sus_count'] = M('porder')->where(array('id' => array('in', array_column($porders, 'id')), 'status' => array('in', '4,12,13')))->count();
			$item['fail_count'] = M('porder')->where(array('id' => array('in', array_column($porders, 'id')), 'status' => array('in', '5,6,7')))->count();
			$item['total_price'] = M('porder')->where(array('id' => array('in', array_column($porders, 'id')), 'status' => array('in', '2,3,4,5,6,8,9,10,11,12,13')))->sum('total_price');
			$item['refund_price'] = M('porder')->where(array('id' => array('in', array_column($porders, 'id')), 'status' => array('in', '6,13')))->sum('refund_price');
			return $item;
		});
		$this->assign('_list', $list);
		return view();
	}
	public function excels_out()
	{
		$exc = M('agent_excel')->where(array('id' => I('id'), 'customer_id' => $this->user['id']))->find();
		if (!$exc) {
			return $this->error('没有该导入文件');
		}
		$porderes = M('agent_proder_excel')->where(array('excel_id' => $exc['id']))->field('order_number')->select();
		$porders = M('porder')->where(array('customer_id' => $this->user['id'], 'order_number' => array('in', array_column($porderes, 'order_number'))))->field('*,(select type_name from dyr_product_type where id=type) as type_name')->select();
		$field_arr = array(array('title' => '平台单号', 'field' => 'order_number'), array('title' => '商户单号', 'field' => 'out_trade_num'), array('title' => '类型', 'field' => 'type_name'), array('title' => '产品ID', 'field' => 'product_id'), array('title' => '产品', 'field' => 'product_name'), array('title' => '手机', 'field' => 'mobile'), array('title' => '归属地', 'field' => 'guishu'), array('title' => '运营商', 'field' => 'isp'), array('title' => '状态', 'field' => 'status'), array('title' => '总金额', 'field' => 'total_price'), array('title' => '支付时间', 'field' => 'pay_time'), array('title' => '下单时间', 'field' => 'create_time'), array('title' => '完成时间', 'field' => 'finish_time'), array('title' => '扩展1', 'field' => 'param1'), array('title' => '扩展2', 'field' => 'param2'), array('title' => '扩展3', 'field' => 'param3'));
		foreach ($porders as &$item) {
			$item['status'] = C('PORDER_STATUS')[$item['status']];
			$item['pay_time'] = time_format($item['pay_time']);
			$item['create_time'] = time_format($item['create_time']);
			$item['finish_time'] = time_format($item['finish_time']);
		}
		$this->exportToExcel($exc['name'] . '-' . time_format($exc['create_time']), $field_arr, $porders);
	}
	public function in_excel()
	{
		if (request()->isPost()) {
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
				$excel_id = M('agent_excel')->insertGetId(array('type' => 2, 'customer_id' => $this->user['id'], 'name' => $file->getInfo()['name'], 'create_time' => time()));
				$tirow = array();
				foreach ($excel_array as $k => $v) {
					$product_id = trim($v[0]);
					$mobile = trim($v[1]);
					$remark = $v[2];
					$out_trade_num = isset($v[3]) ? trim($v[3]) : '';
					$prov = isset($v[4]) ? trim($v[4]) : '';
					$id_card_no = isset($v[5]) ? trim($v[5]) : '';
					$ytype = isset($v[6]) ? trim($v[6]) : 1;
					$city = isset($v[7]) ? trim($v[7]) : '';
					$hangstr = '  #第[' . ($k + 2) . '行]';
					$product_type = M('product')->where(array('id' => $product_id, 'is_del' => 0))->value('type');
					if (in_array($product_type, array(1, 2))) {
						$guishu = QCellCore($mobile);
						if ($guishu['errno'] != 0) {
							return $this->error('归属地未找到:' . $mobile . ',' . $guishu['errmsg'] . $hangstr, '', '', 20);
						}
						$guishu = Product::Ispzhan($mobile, $this->user['id'], $guishu);
						$map['p.isp'] = array('like', '%' . $guishu['data']['isp'] . '%');
						$tirow[$k]['isp'] = $guishu['data']['ispstr'];
						$tirow[$k]['guishu'] = $guishu['data']['prov'] . $guishu['data']['city'];
						$hangstr = $guishu['data']['ispstr'] . ',' . $hangstr;
						$prov = $guishu['data']['prov'];
						$city = $guishu['data']['city'];
					}
					$map['p.id'] = $product_id;
					$map['p.added'] = 1;
					$resdata = ProductModel::getProduct($map, $this->user['id'], $prov, $city);
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
					$tirow[$k]['customer_id'] = $this->user['id'];
					$tirow[$k]['excel_id'] = $excel_id;
					$tirow[$k]['create_time'] = time();
					$tirow[$k]['out_trade_num'] = $out_trade_num;
				}
				$sh = M('agent_proder_excel')->insertAll($tirow);
				return $this->success('成功导入' . $sh . '条数据,即将进入推送页面', U('porder_excel', array('status' => 1, 'excel_id' => $excel_id)));
			} else {
				return $this->error($file->getError());
			}
		} else {
			return view();
		}
	}
	public function batch_in()
	{
		$mobile_str = $_POST['mobiles'];
		$mobiles = json_decode($mobile_str, true);
		$product_id = I('product_id');
		$excel_id = M('agent_excel')->insertGetId(array('type' => 1, 'customer_id' => $this->user['id'], 'name' => "批量下单" . date('Y-m-d H:i', time()), 'create_time' => time()));
		$tirow = array();
		foreach ($mobiles as $k => $hang) {
			$mobile = trim($hang[0]);
			$prov = isset($hang[1]) ? trim($hang[1]) : '';
			$id_card_no = isset($hang[2]) ? trim($hang[2]) : '';
			$ytype = isset($hang[3]) ? trim($hang[3]) : 1;
			$city = isset($hang[4]) ? trim($hang[4]) : '';
			$hangstr = '  #第[' . ($k + 1) . '行]';
			$product_type = M('product')->where(array('id' => $product_id, 'is_del' => 0))->value('type');
			if (in_array($product_type, array(1, 2))) {
				if (!is_numeric($mobile) || mb_strlen($mobile) != 11) {
					return $this->error('手机号格式不正确');
				}
				$guishu = QCellCore($mobile);
				if ($guishu['errno'] != 0) {
					return $this->error('归属地未找到:' . $mobile . ',' . $guishu['errmsg'] . $hangstr, '', '', 20);
				}
				$guishu = Product::Ispzhan($mobile, $this->user['id'], $guishu);
				$map['p.isp'] = array('like', '%' . $guishu['data']['isp'] . '%');
				$tirow[$k]['isp'] = $guishu['data']['ispstr'];
				$tirow[$k]['guishu'] = $guishu['data']['prov'] . $guishu['data']['city'];
				$hangstr = $guishu['data']['ispstr'] . ',' . $hangstr;
				$prov = $guishu['data']['prov'];
				$city = $guishu['data']['city'];
			}
			$map['p.id'] = $product_id;
			$map['p.added'] = 1;
			$resdata = ProductModel::getProduct($map, $this->user['id'], $prov, $city);
			if ($resdata['errno'] != 0) {
				return $this->error('未找到匹配的充值产品，' . $resdata['errmsg'] . '，套餐id:' . $product_id . '，手机：' . $mobile . '，' . $hangstr, '', '', 20);
			}
			$product = $resdata['data'];
			$tirow[$k]['product_id'] = $product_id;
			$tirow[$k]['mobile'] = $mobile;
			$tirow[$k]['remark'] = $product['name'];
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
			$tirow[$k]['customer_id'] = $this->user['id'];
			$tirow[$k]['excel_id'] = $excel_id;
			$tirow[$k]['create_time'] = time();
		}
		$sh = M('agent_proder_excel')->insertAll($tirow);
		return $this->success('成功生成' . $sh . '条数据,即将进入推送页面', U('porder_excel', array('status' => 1, 'excel_id' => $excel_id)));
	}
	public function up_agent_excel()
	{
		$arr = I('post.');
		unset($arr['id']);
		M('agent_excel')->where(array('id' => I('id'), 'customer_id' => $this->user['id']))->setField($arr);
		return $this->success('修改成功');
	}
	public function porder_excel()
	{
		$map = $this->porder_excel_map();
		$list = M('agent_proder_excel p')->join('agent_excel e', 'e.id=p.excel_id')->where($map)->field('p.*,e.name as excel_name,(select type_name from dyr_product_type where id=p.type) as type_name')->order('p.id desc')->select();
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
		return view();
	}
	public function porder_excel_out()
	{
		$map = $this->porder_excel_map();
		$list = M('agent_proder_excel p')->join('agent_excel e', 'e.id=p.excel_id')->where($map)->field('p.*,e.name as excel_name,(select type_name from dyr_product_type where id=p.type) as type_name')->order('p.id desc')->select();
		$field_arr = array(array('title' => '表格名称', 'field' => 'excel_name'), array('title' => '行', 'field' => 'hang'), array('title' => '商户单号', 'field' => 'out_trade_num'), array('title' => '类型', 'field' => 'type_name'), array('title' => '产品ID', 'field' => 'product_id'), array('title' => '产品', 'field' => 'product_name'), array('title' => '手机', 'field' => 'mobile'), array('title' => '归属地', 'field' => 'guishu'), array('title' => '运营商', 'field' => 'isp'), array('title' => '状态', 'field' => 'status'), array('title' => '系统单号', 'field' => 'order_number'), array('title' => '总金额', 'field' => 'total_price'), array('title' => '生成时间', 'field' => 'create_time'), array('title' => '提单结果', 'field' => 'resmsg'));
		foreach ($list as &$item) {
			$item['status'] = C('PORDER_EXCEL_STATUS')[$item['status']];
			$item['create_time'] = time_format($item['create_time']);
		}
		$this->exportToExcel('导出表格-' . time_format(time()), $field_arr, $list);
	}
	public function zuofei_porder_excel()
	{
		if (M('agent_proder_excel')->where(array('id' => I('id'), 'customer_id' => $this->user['id']))->setField(array('status' => 5))) {
			return $this->success('作废成功');
		} else {
			return $this->error('作废失败');
		}
	}
	public function zuofei_all_porder_excel()
	{
		$map['customer_id'] = $this->user['id'];
		$map['status'] = 1;
		M('agent_proder_excel')->where($map)->setField(array('status' => 5));
		return $this->success('作废成功');
	}
	public function push_excel()
	{
		set_time_limit(0);
		$excel_id = I('excel_id');
		$map['p.status'] = 1;
		$map['p.excel_id'] = $excel_id;
		$list = M('agent_proder_excel p')->join('agent_excel e', 'e.id=p.excel_id')->where($map)->field('p.id')->select();
		if (!$list) {
			return $this->error('没有可推送的数据');
		}
		M('agent_proder_excel')->where(array('id' => array('in', array_column($list, 'id'))))->setField(array('status' => 2));
		queue('app\\queue\\job\\Work@agentPushExcel', $list);
		return $this->success('成功确认' . count($list) . '条！请刷新待推送列表查看', U('porder_excel', array('excel_id' => $excel_id)));
	}
	private function porder_excel_map()
	{
		$map['p.customer_id'] = $this->user['id'];
		if (I('status2')) {
			$map['p.status'] = array('in', I('status2'));
		}
		if (I('status')) {
			$map['p.status'] = I('status');
		}
		if (I('excel_id')) {
			$map['p.excel_id'] = I('excel_id');
		}
		return $map;
	}
	public function notification()
	{
		$ret = PorderModel::notification(I('id'));
		if ($ret['errno'] != 0) {
			return $this->error($ret['errmsg']);
		}
		return $this->success($ret['errmsg']);
	}
	public function complaint()
	{
		$porder = M('porder')->where(array('customer_id' => $this->user['id'], 'is_del' => 0, 'id' => I('porder_id')))->find();
		if (!$porder) {
			return $this->error("订单不存在");
		}
		$this->assign('info', $porder);
		return view();
	}
	public function complaint_sub()
	{
		$porder = M('porder')->where(array('customer_id' => $this->user['id'], 'is_del' => 0, 'id' => I('id')))->find();
		if (!$porder) {
			return djson(1, '订单未找到');
		}
		M('porder_complaint')->insertGetId(array('customer_id' => $this->user['id'], 'porder_id' => $porder['id'], 'name' => I('name'), 'mobile' => I('mobile'), 'issue' => I('issue'), 'create_time' => time(), 'status' => 1));
		return djson(0, '投诉提交成功，等待平台处理');
	}
	public function apply_cancel_order()
	{
		if (C('AGENT_CANCEL_SW') != 1) {
			return djson(1, '功能关闭');
		}
		$porder = M('porder')->where(array('customer_id' => $this->user['id'], 'is_del' => 0, 'id' => I('id')))->find();
		if (!$porder) {
			return djson(1, '订单未找到');
		}
		return PorderModel::applyCancelOrder(array($porder['id']), "代理-[" . $this->user['id'] . ']' . $this->user['username']);
	}
	public function lable()
	{
		if (request()->isPost()) {
			$id = I('id');
			$porder = M('porder')->where(array('customer_id' => $this->user['id'], 'id' => $id))->find();
			if (!$porder) {
				return $this->error('订单未找到');
			}
			$lable = I('lable');
			$data = M('porder')->where(array('id' => $id))->setField(array('user_lable' => $lable));
			if ($data) {
				Createlog::porderLog($porder['id'], '修改标签:' . $lable . '|代理端|' . "代理-[" . $this->user['id'] . ']' . $this->user['username']);
				return $this->success('保存成功');
			} else {
				return $this->error('保存失败');
			}
		} else {
			$info = M('porder')->where(array('id' => I('id')))->find();
			$this->assign('info', $info);
			$this->assign('lables', explode(',', C('PORDER_LABLES')));
			return view();
		}
	}
}