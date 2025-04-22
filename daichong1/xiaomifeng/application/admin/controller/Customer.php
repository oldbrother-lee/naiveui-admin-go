<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use app\common\library\AgentGrade;
use app\common\library\Createlog;
use app\common\model\Balance;
use app\common\model\Client;
use app\common\model\CustomerHezuoPrice;
use app\common\model\Product as ProductModel;
use Util\GoogleAuth;
use Util\Random;
class Customer extends Admin
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
		$list = $this->getList($map);
		$this->assign('_list', $list);
		$this->assign('_count', $list->total());
		return view();
	}
	private function create_map()
	{
		$map['c.is_del'] = 0;
		if (I('key')) {
			if (I('query_name')) {
				$map[I('query_name')] = trim(I('key'));
			} else {
				$map['c.username|c.mobile'] = array('like', '%' . trim(I('key')) . '%');
			}
		}
		if (I('grade_id')) {
			$map['c.grade_id'] = I('grade_id');
		}
		if (I('is_subscribe')) {
			$map['c.is_subscribe'] = intval(I('is_subscribe')) - 1;
		}
		if (I('status')) {
			$map['c.status'] = I('status') - 1;
		}
		if (I('id')) {
			$map['c.id'] = I('id');
		}
		if (I('f_id')) {
			$map['c.f_id'] = I('f_id');
		}
		if (I('client')) {
			$map['c.client'] = I('client');
		}
		if (I('type')) {
			$map['c.type'] = I('type');
			$this->assign("grades", M('customer_grade')->where(array('grade_type' => I('type')))->order("sort asc,grade_type asc")->select());
		} else {
			$this->assign("grades", array());
		}
		return $map;
	}
	private function getList($map, $page = true)
	{
		if (I('sort')) {
			$sort = I('sort');
		} else {
			$sort = "id desc";
		}
		if ($page) {
			$list = M('Customer c')->where($map)->field('c.*,(select username from dyr_customer where id=c.f_id) as usernames,(select grade_name from dyr_customer_grade where id=c.grade_id) as grade_name,(select is_zdy_price from dyr_customer_grade where id=c.grade_id) as is_zdy_price,(select sum(total_price) from dyr_porder where customer_id=c.id and status in (2,3,4)) as total_price,(select count(*) from dyr_porder where customer_id=c.id and status not in (1,7)) as porder_num,(select count(*) from dyr_customer where f_id=c.id and is_del=0) as child_num')->order($sort)->paginate(C('LIST_ROWS'));
		} else {
			$list = M('Customer c')->where($map)->field('c.*,(select username from dyr_customer where id=c.f_id) as usernames,(select grade_name from dyr_customer_grade where id=c.grade_id) as grade_name,(select is_zdy_price from dyr_customer_grade where id=c.grade_id) as is_zdy_price,(select sum(total_price) from dyr_porder where customer_id=c.id and status in (2,3,4)) as total_price,(select count(*) from dyr_porder where customer_id=c.id and status status not in (1,7)) as porder_num,(select count(*) from dyr_customer where f_id=c.id and is_del=0) as child_num')->order($sort)->select();
		}
		return $list;
	}
	public function customer_excel()
	{
		$map = $this->create_map();
		$ret = $this->getList($map, false);
		$field_arr = array(array('title' => '昵称', 'field' => 'username'), array('title' => '手机', 'field' => 'mobile'), array('title' => '注册时间', 'field' => 'create_time'), array('title' => '会员类型', 'field' => 'type'), array('title' => '等级', 'field' => 'grade_name'), array('title' => 'appid', 'field' => 'weixin_appid'), array('title' => 'openid', 'field' => 'wx_openid'), array('title' => '余额', 'field' => 'balance'), array('title' => '订单金额', 'field' => 'total_price'), array('title' => '订单数', 'field' => 'porder_num'));
		foreach ($ret as $key => $vo) {
			$ret[$key]['type'] = C('CUS_TYPE')[$vo['type']];
			$ret[$key]['create_time'] = time_format($vo['create_time']);
		}
		$this->exportToExcel('会员列表' . time(), $field_arr, $ret);
	}
	public function edit()
	{
		if (request()->isPost()) {
			$arr = I('post.');
			if (I('id')) {
				$data = M('Customer')->where(array('id' => I('id')))->setField($arr);
				if ($data) {
					Createlog::customerLog(I('id'), '修改信息：' . json_encode($arr), '管理员：' . $this->adminuser['nickname']);
					return $this->success('保存成功');
				} else {
					return $this->error('编辑失败');
				}
			} else {
				$username = I('username');
				$mobile = I('mobile');
				if ($username == '' || $mobile == '') {
					return $this->error('用户名和联系方式必须');
				}
				if (M('Customer')->where(array('username' => I('username')))->find()) {
					return $this->error('已有相同用户名的用户');
				}
				$arr['password'] = dyr_encrypt(I('password'));
				$arr['type'] = 2;
				$arr['client'] = Client::CLIENT_ADM;
				$arr['create_time'] = time();
				$arr['apikey'] = Random::alnum(32);
				$arr['headimg'] = $arr['headimg'] ?: C('DEFAULT_HEADIMG');
				$inid = M('Customer')->insertGetId($arr);
				if ($inid) {
					Createlog::customerLog($inid, '后台注册成功', $this->adminuser['nickname']);
					return $this->success('新增成功');
				} else {
					return $this->error('新增失败');
				}
			}
		} else {
			$this->assign("grades", M('customer_grade')->select());
			$info = M('Customer')->where(array('id' => I('id')))->find();
			$this->assign('info', $info);
			return view();
		}
	}
	public function edita()
	{
		$info = M('Customer')->where(array('id' => I('id')))->find();
		$this->assign('info', $info);
		return view();
	}
	public function get_grades()
	{
		$map = array();
		if (I('grade_type')) {
			$map['grade_type'] = I('grade_type');
		}
		$grades = M('customer_grade')->where($map)->order("sort asc,grade_type asc")->select();
		return djson(0, 'ok', $grades);
	}
	public function deletes()
	{
		if (M('Customer')->where(array('id' => I('id')))->setField(array('is_del' => 1))) {
			Createlog::customerLog(I('id'), '删除账户', '管理员：' . $this->adminuser['nickname']);
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败');
		}
	}
	public function del_poster()
	{
		$map['is_del'] = 0;
		if (I('id')) {
			$map['id'] = I('id');
		}
		M('Customer')->where($map)->setField(array('qr_value' => '', 'qrurl' => '', 'share_img_time' => '0', 'mp_qrurl' => '', 'h5_qrurl' => ''));
		return $this->success('清空成功');
	}
	public function qi_jin()
	{
		if (I('status') == 0) {
			if (M('Customer')->where(array('id' => I('id')))->setField(array('status' => 0))) {
				Createlog::customerLog(I('id'), '禁用账户', '管理员：' . $this->adminuser['nickname']);
				return $this->success('禁用成功');
			} else {
				return $this->error('禁用失败');
			}
		} else {
			if (M('Customer')->where(array('id' => I('id')))->setField(array('status' => 1))) {
				Createlog::customerLog(I('id'), '启用账户', '管理员：' . $this->adminuser['nickname']);
				return $this->success('启用成功');
			} else {
				return $this->error('启用失败');
			}
		}
	}
	public function balance_log()
	{
		$vava = 0;
		$map = $this->balance_create_map();
		$list = M('balance_log b')->join('customer c', 'c.id=b.customer_id')->where($map)->field('b.*,c.username,c.headimg')->order("b.id desc")->paginate(50)->each(function ($item, $key) use(&$vava, $map) {
			if (isset($map['b.customer_id']) && !(isset($map['b.style']) || isset($map['b.remark']))) {
				$item['check'] = round(floatval($vava), 2) == round(floatval($item['balance']), 2) || $key == 0 ? 1 : 0;
			} else {
				$item['check'] = 2;
			}
			$vava = $item['balance'] + ($item['type'] == 1 ? -1 * $item['money'] : +1 * $item['money']);
			return $item;
		});
		$this->assign('_list', $list);
		return view();
	}
	private function balance_create_map()
	{
		$map = array();
		if (I('style')) {
			$map['b.style'] = I('style');
		}
		if (I('id')) {
			$map['b.customer_id'] = I('id');
		}
		if (I('key')) {
			$map['b.remark'] = array('like', '%' . I('key') . '%');
		}
		if (I('end_time') && I('begin_time')) {
			$map['b.create_time'] = array('between', array(strtotime(I('begin_time')), strtotime(I('end_time'))));
		}
		return $map;
	}
	public function balance_out_excel()
	{
		$map = $this->balance_create_map();
		$ret = M('balance_log b')->join('customer c', 'c.id=b.customer_id')->where($map)->field('b.*,c.username,c.headimg')->order("b.id desc")->select();
		$field_arr = array(array('title' => '账号', 'field' => 'username'), array('title' => '账号ID', 'field' => 'customer_id'), array('title' => '交易时间', 'field' => 'create_time'), array('title' => '交易方式', 'field' => 'type'), array('title' => '交易金额', 'field' => 'money'), array('title' => '交易明细', 'field' => 'remark'), array('title' => '交易类型', 'field' => 'style'), array('title' => '余额', 'field' => 'balance'));
		foreach ($ret as $key => $vo) {
			$ret[$key]['type'] = $vo['type'] == 1 ? '收入' : '支出';
			$ret[$key]['money'] = $vo['type'] == 1 ? $vo['money'] : -1 * $vo['money'];
			$ret[$key]['style'] = C('BALANCE_STYLE')[$vo['style']];
			$ret[$key]['create_time'] = time_format($vo['create_time']);
		}
		$this->exportToExcel('余额明细' . time(), $field_arr, $ret);
	}
	public function integral_log()
	{
		$map = array();
		if (I('style')) {
			$map['style'] = I('style');
		}
		if (I('id')) {
			$map['customer_id'] = I('id');
		}
		if (I('key')) {
			$map['remark'] = array('like', '%' . I('key') . '%');
		}
		if (I('end_time') && I('begin_time')) {
			$map['create_time'] = array('between', array(strtotime(I('begin_time')), strtotime(I('end_time'))));
		}
		$list = M('integral_log')->where($map)->order("id desc")->paginate(30);
		$this->assign('_list', $list);
		return view();
	}
	public function customer_log()
	{
		$map = array();
		if (I('id')) {
			$map['customer_id'] = I('id');
		}
		if (I('key')) {
			$map['log'] = array('like', '%' . I('key') . '%');
		}
		$list = M('customer_log')->where($map)->order("create_time desc")->paginate(30);
		$this->assign('_list', $list);
		return view();
	}
	public function grade()
	{
		$list = M('customer_grade')->order('sort asc,id asc')->select();
		$this->assign('_list', $list);
		return view();
	}
	public function grade_edit()
	{
		if (request()->isPost()) {
			$arr = I('post.');
			if (I('id')) {
				$data = M('customer_grade')->where(array('id' => I('id')))->setField($arr);
				if ($data) {
					return $this->success('保存成功');
				} else {
					return $this->error('编辑失败');
				}
			} else {
				$aid = M('customer_grade')->insertGetId($arr);
				if ($aid) {
					AgentGrade::initPrice();
					return $this->success('新增成功');
				} else {
					return $this->error('新增失败');
				}
			}
		} else {
			$info = M('customer_grade')->where(array('id' => I('id')))->find();
			$this->assign('info', $info);
			return view();
		}
	}
	public function grade_deletes()
	{
		$id = I('id');
		if (M('customer')->where(array('grade_id' => $id, 'is_del' => 0))->find()) {
			return $this->error('等级下还有会员，请先移除会员');
		}
		if (M('customer_grade')->where(array('id' => $id))->delete()) {
			M('customer_grade_price')->where(array('grade_id' => $id))->delete();
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败');
		}
	}
	public function grade_copy()
	{
		$id = I('id');
		$grade = M('customer_grade')->where(array('id' => $id))->find();
		if (!$grade) {
			return $this->error('不存在的等级');
		}
		unset($grade['id']);
		$grade['grade_name'] .= '_复制';
		$newid = M('customer_grade')->insertGetId($grade);
		if (!$newid) {
			return $this->error('复制错误');
		}
		$prices = M('customer_grade_price')->where(array('grade_id' => $id))->field($newid . ' as grade_id,product_id,ranges')->select();
		M('customer_grade_price')->insertAll($prices);
		return $this->success('复制成功');
	}
	public function price()
	{
		AgentGrade::initPrice();
		if (I('grade_id')) {
			$map['gp.grade_id'] = I('grade_id');
		}
		if (I('product_id')) {
			$map['gp.product_id'] = I('product_id');
		}
		$map['p.is_del'] = 0;
		if (I('key')) {
			$map['p.name|p.desc'] = array('like', '%' . I('key') . '%');
		}
		$list = M('customer_grade_price gp')->join("product p", 'p.id=gp.product_id')->join("customer_grade g", 'g.id=gp.grade_id')->where($map)->field("gp.*,p.name,p.isp,p.desc,p.price,p.added,p.remark,g.grade_name")->order("p.type asc,p.sort asc")->select();
		$this->assign('_list', $list);
		return view();
	}
	public function price_edit()
	{
		if (request()->isPost()) {
			$arr = I('post.');
			$data = M('customer_grade_price')->where(array('id' => I('id')))->setField($arr);
			if ($data) {
				return $this->success('保存成功');
			} else {
				return $this->error('编辑失败');
			}
		} else {
			$info = M('customer_grade_price')->where(array('id' => I('id')))->find();
			$this->assign('info', $info);
			return view();
		}
	}
    //批量设置代理等级浮动利润
    public function price_edits() {
        $dor = I('product_id');

        if(empty($dor)) {
            return $this->error('未选择产品，无法设置');
        }

        $bili = I('ranges');

        if(!is_numeric($bili)) {
            return $this->error('数值输入有误，无法设置');
        }

        $bili /= 100;

        if(empty($bili)) {
            return $this->error('未输入数值，无法设置');
        }

        $prices = M('customer_grade_price')->where('id', 'in', $dor)->select();

        foreach($prices as $k => $price) {
            $product = M('product')->where(['id' => $price['product_id']])->find();
            preg_match('/\d+/', $product['name'], $number);

            if($number[0] > 0) {
                $money = number_format($number[0] * $bili, 2, ".", "");
                M('customer_grade_price')->where(['id' => $price['id']])->update(['ranges' => $money]);
            }
        }

        return $this->success('保存成功');
    }
	public function up_password()
	{
		if (!I('id') || !I('password')) {
			return $this->error("请输入密码！");
		}
		M('customer')->where(array('id' => I('id')))->setField(array('password' => dyr_encrypt(I('password'))));
		Createlog::customerLog(I('id'), '修改密码', '管理员：' . $this->adminuser['nickname']);
		return $this->success('重置成功!');
	}
	public function reset_google_auth_secret()
	{
		$goret = GoogleAuth::verifyCode($this->adminuser['google_auth_secret'], I('verifycode'), 1);
		if (!$goret) {
			return $this->error("谷歌身份验证码错误！");
		}
		M('customer')->where(array('id' => I('id')))->setField(array('google_auth_secret' => ''));
		Createlog::customerLog(I('id'), '重置了用户谷歌验证码', '管理员：' . $this->adminuser['nickname']);
		return $this->success('重置成功');
	}
	public function balance_add()
	{
		$money = floatval(I('money'));
		if ($money == 0 || abs($money) > 9999999) {
			return $this->error("金额错误！");
		}
		$remark = I('remark');
		if ($remark == "") {
			return $this->error("备注必填！");
		}
		if ($money > 0) {
			$ret = Balance::revenue(I('id'), $money, $remark, Balance::STYLE_RECHARGE, '管理员：' . $this->adminuser['nickname']);
		} else {
			$ret = Balance::expend(I('id'), abs($money), $remark, Balance::STYLE_RECHARGE, '管理员：' . $this->adminuser['nickname']);
		}
		if ($ret['errno'] != 0) {
			return $this->error($ret['errmsg']);
		}
		return $this->success($ret['errmsg']);
	}
	public function hz_price()
	{
		$customer = M('customer')->where(array('id' => I('customer_id')))->find();
		if (!$customer) {
			return $this->error('未找到用户');
		}
		$map['p.is_del'] = 0;
		$key = trim(I('key'));
		if ($key) {
			if (I('query_name')) {
				$map[I('query_name')] = $key;
			} else {
				$map['p.name|p.desc'] = array('like', '%' . $key . '%');
			}
		}
		if (I('product_id')) {
			$map['p.id'] = I('product_id');
		}
		$resdata = ProductModel::getProducts($map, $customer['id']);
		$cates = $resdata['data'];
		foreach ($cates as &$cate) {
			foreach ($cate['products'] as &$item) {
				$hzprice = M('customer_hezuo_price')->where(array('customer_id' => $customer['id'], 'product_id' => $item['id']))->field('id as rangesid,ranges,ys_tag')->find();
				if (!$hzprice) {
					$item['ys_tag'] = '';
					$item['rangesid'] = 0;
					$item['ranges'] = 0;
				} else {
					$item['ys_tag'] = $hzprice['ys_tag'];
					$item['rangesid'] = $hzprice['rangesid'];
					$item['ranges'] = $hzprice['ranges'];
				}
			}
		}
		$this->assign('_list', $cates);
		return view();
	}
	public function hz_price_edit()
	{
		$pr_id = I('id');
		$product_id = I('product_id');
		$customer_id = I('customer_id');
		$map['p.is_del'] = 0;
		$map['p.id'] = $product_id;
		$customer = M('customer')->where(array('id' => $customer_id))->find();
		$resdata = ProductModel::getProduct($map, $customer['id']);
		if ($resdata['errno'] != 0) {
			return $this->error($resdata['errmsg']);
		}
		$product = $resdata['data'];
		$ranges = floatval(I('ranges'));
		if ($ranges < 0) {
			return $this->error('浮动金额不能小于0');
		}
		if (floatval($product['max_price']) > 0 && $product['price'] + $ranges > $product['max_price']) {
			return $this->error('不能设置高于封顶价格');
		}
		$res = CustomerHezuoPrice::saveValues($pr_id, $customer_id, $product_id, array('ranges' => $ranges));
		if ($res['errno'] == 0) {
			return $this->success('保存成功');
		} else {
			return $this->error('编辑失败');
		}
	}
	public function hz_price_ystag_edit()
	{
		$res = CustomerHezuoPrice::saveValues(I('id'), I('customer_id'), I('product_id'), array('ys_tag' => I('ys_tag')));
		if ($res['errno'] == 0) {
			return $this->success('保存成功');
		} else {
			return $this->error('编辑失败');
		}
	}
	public function balance_check()
	{
		$id = I('id');
		$bsr = M('balance_log')->where(array('type' => 1, 'customer_id' => $id))->sum('money');
		$bzc = M('balance_log')->where(array('type' => 2, 'customer_id' => $id))->sum('money');
		$balance = M('Customer')->where(array('id' => $id))->value('balance');
		$chva = round(floatval($bsr - $bzc - $balance), 2);
		if ($chva == 0) {
			return $this->success('校验通过');
		} else {
			return $this->error("检验不通过，异常值建议：" . $chva);
		}
	}
	public function reset_apikey()
	{
		$apikey = Random::alnum(32);
		M('Customer')->where(array('id' => I('id')))->setField(array('apikey' => $apikey));
		return $this->success('重置成功');
	}
	public function month_bill()
	{
		$customer_id = I('id');
		$data = array();
		$cusmonth = strtotime(date("Y-m-01", time()));
		for ($i = 0; $i < 12; $i++) {
			$start_time = strtotime('-' . $i . ' month', $cusmonth);
			$end_time = strtotime('+1 month', $start_time) - 1;
			$arr['date'] = date("Y-m", $start_time);
			$arr['qichu'] = M('balance_log')->where(array('create_time' => array('egt', $start_time), 'customer_id' => $customer_id))->order('create_time asc,id asc')->value('balance_pr');
			$arr['qimo'] = M('balance_log')->where(array('create_time' => array('elt', $end_time), 'customer_id' => $customer_id))->order('create_time desc,id desc')->value('balance');
			$arr['jiakuan'] = M('balance_log')->where(array('customer_id' => $customer_id, 'create_time' => array('between', array($start_time, $end_time)), 'type' => 1, 'style' => 4))->sum('money');
			$data[] = $arr;
		}
		$this->assign('_list', $data);
		return view();
	}
}