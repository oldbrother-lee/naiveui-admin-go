<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use app\common\library\AgentGrade;
class Product extends Admin
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
		$map['is_del'] = 0;
		if (I('key')) {
			$map['name|desc'] = array('like', '%' . I('key') . '%');
		}
		if (I('added')) {
			$map['added'] = intval(I('added')) - 1;
		}
		$type = M('product_type')->where(array('status' => 1))->order('sort asc,id asc')->value('id');
		if (I('type')) {
			$type = I('type');
		}
		$map['type'] = $type;
		if (I('isp')) {
			$map['isp'] = I('isp');
		}
		if (I('id')) {
			$map['id'] = I('id');
		}
		if (I('cate_id')) {
			$map['cate_id'] = I('cate_id');
		}
		if (I('show_style')) {
			$map['show_style'] = I('show_style');
		}
		$lists = M('product')->where($map)->field("*,(select type_name from dyr_product_type where id=type) as type_name")->order("type,sort")->select();
		$grade = M('customer_grade')->order('grade_type asc,sort asc')->select();
		foreach ($lists as $key => $vo) {
			$gr = array();
			foreach ($grade as $k => $v) {
				$price = M('customer_grade_price')->where(array('grade_id' => $v['id'], 'product_id' => $vo['id']))->find();
				array_push($gr, array('id' => $v['id'], 'grade_name' => $v['grade_name'], 'price' => $vo['price'] + $price['ranges']));
			}
			$lists[$key]['grade'] = $gr;
			$lists[$key]['api_list'] = M('product_api')->where(array('product_id' => $vo['id']))->order('sort asc')->select();
			$lists[$key]['cate_name'] = M('product_cate')->where(array('id' => $vo['cate_id']))->value('cate');
		}
		$this->assign('_list', $lists);
		$this->assign('cates', M('product_cate')->where(array('type' => $type))->order('sort asc')->select());
		$this->assign('types', M('product_type')->where(array('status' => 1))->order('sort asc,id asc')->select());
		$this->assign('typeid', $type);
		return view();
	}
	public function edit()
	{
		if (request()->isPost()) {
			$isparr = I('isp/a');
			$gradesids = I('grade_ids/a');
			$arr = I('post.');
			if (!isset($arr['cate_id']) || !$arr['cate_id']) {
				return $this->error('分类必选');
			}
			if ($gradesids) {
				$arr['grade_ids'] = implode(',', $gradesids);
			} else {
				$arr['grade_ids'] = "";
			}
			if ($isparr) {
				$arr['isp'] = implode(',', $isparr);
			} else {
				$arr['isp'] = '';
			}
			if (I('is_jiema') == 1 && (I('jmapi_id') == 0 || I('jmapi_param_id') == 0)) {
				return $this->error('请选择接码api');
			}
			if (in_array($arr['type'], array(1, 2, 3))) {
				preg_match_all('/\\d+/', $arr['name'], $numarr);
				if (count($numarr[0]) != 1) {
					return $this->error('套餐名称不符合规范');
				}
			}
			unset($arr['id']);
			if (I('id')) {
				$data = M('product')->where(array('id' => I('id')))->setField($arr);
				if ($data) {
					return $this->success('保存成功');
				} else {
					return $this->error('修改保存失败，可能未做任何变动');
				}
			} else {
				$aid = M('product')->insertGetId($arr);
				if ($aid) {
					AgentGrade::initPrice();
					return $this->success('新增成功');
				} else {
					return $this->error('新增失败');
				}
			}
		} else {
			$info = M('product')->where(array('id' => I('id')))->find();
			$this->assign('info', $info);
			if ($info) {
				$type = $info['type'];
			} else {
				$type = I('type');
			}
			$this->assign('cates', M('product_cate')->where(array('type' => $type))->order('sort asc')->select());
			$this->assign('types', M('product_type')->where(array('status' => 1))->order('sort asc,id asc')->select());
			$this->assign('grades', M('customer_grade')->select());
			$this->assign('jiemaapi', M('jmapi')->where(array('is_del' => 0))->select());
			$this->assign('jmapiparams', M('jmapi_param')->where(array('jmapi_id' => $info['jmapi_id']))->select());
			return view();
		}
	}
	public function deletes()
	{
		$id = I('id');
		if (M('product')->where(array('id' => I('id')))->setField(array('is_del' => 1))) {
			M('product_api')->where(array('product_id' => $id))->delete();
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败');
		}
	}
	public function added()
	{
		$ids = I('id/a');
		$added = I('added') == 0 ? 0 : 1;
		$products = M('product')->where(array('id' => array('in', $ids), 'added' => $added == 0 ? 1 : 0))->select();
		if (!$products) {
			return $this->error('未查询到产品');
		}
		$counts = 0;
		$errmsg = '';
		foreach ($products as $product) {
			$state = M('product')->where(array('id' => $product['id']))->setField(array('added' => $added));
			if ($state) {
				$counts++;
			} else {
				$errmsg .= $product['name'] . '失败;';
			}
		}
		if ($counts == 0) {
			return $this->error('操作失败,' . $errmsg);
		}
		return $this->success("成功处理" . $counts . "条");
	}
	public function apiopen()
	{
		$ids = I('id/a');
		$api_open = I('api_open') == 0 ? 0 : 1;
		$products = M('product')->where(array('id' => array('in', $ids), 'api_open' => $api_open == 0 ? 1 : 0))->select();
		if (!$products) {
			return $this->error('未查询到产品');
		}
		$counts = 0;
		$errmsg = '';
		foreach ($products as $product) {
			$state = M('product')->where(array('id' => $product['id']))->setField(array('api_open' => $api_open));
			if ($state) {
				$counts++;
			} else {
				$errmsg .= $product['name'] . '失败;';
			}
		}
		if ($counts == 0) {
			return $this->error('操作失败,' . $errmsg);
		}
		return $this->success("成功处理" . $counts . "条");
	}
	public function cates()
	{
		$map = array();
		if (I('type')) {
			$map['type'] = I('type');
		}
		$this->assign('_list', M('product_cate')->where($map)->order('sort asc')->select());
		return view();
	}
	public function cate_edit()
	{
		$info = M('product_cate')->where(array('id' => I('id')))->find();
		$this->assign('info', $info);
		return view();
	}
	public function cate_edit_save()
	{
		$arr = I('post.');
		unset($arr['id']);
		if (I('id')) {
			$data = M('product_cate')->where(array('id' => I('id')))->setField($arr);
			if ($data) {
				return $this->success('保存成功');
			} else {
				return $this->error('编辑失败');
			}
		} else {
			$aid = M('product_cate')->insertGetId($arr);
			if ($aid) {
				return $this->success('新增成功');
			} else {
				return $this->error('新增失败');
			}
		}
	}
	public function cate_del()
	{
		if (M('product')->where(array('cate_id' => I('id'), 'is_del' => 0))->find()) {
			return $this->error('分类下还有产品，请先移除产品或者删除产品');
		}
		if (M('product_cate')->where(array('id' => I('id')))->delete()) {
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败');
		}
	}
	public function reapi()
	{
		return rjson(0, 'ok', M('reapi')->where(array('is_del' => 0))->select());
	}
	public function reapi_param()
	{
		return rjson(0, 'ok', M('reapi_param')->where(array('reapi_id' => I('reapi_id')))->select());
	}
	public function get_product()
	{
		return rjson(0, 'ok', M('product')->where(array('cate_id' => I('cate_id'), 'added' => 1, 'is_del' => 0))->select());
	}
	public function api()
	{
		$lists = M('product_api pa')->join('reapi_param rp', 'rp.id=pa.param_id')->join('reapi r', 'r.id=pa.reapi_id')->where(array('pa.product_id' => I('id')))->order('pa.status desc,pa.sort asc,pa.id asc')->field('pa.*,r.name,rp.desc,rp.allow_pro,rp.allow_city,rp.forbid_pro,rp.forbid_city')->select();
		$this->assign('_list', $lists);
		return view();
	}
	public function edit_api()
	{
		if (request()->isPost()) {
			$arr = I('post.');
			if (intval($arr['num']) < 1) {
				return rjson(1, '重试次数至少填1');
			}
			if (!($arr['reapi_id'] && $arr['param_id'])) {
				return rjson(1, '渠道、套餐必选；如果没有可选套餐，请先到接口管理->套餐配置中去设置');
			}
			if (I('id')) {
				$data = M('product_api')->where(array('id' => I('id')))->setField($arr);
			} else {
				$data = M('product_api')->insertGetId($arr);
			}
			if ($data) {
				return rjson(0, '设置成功');
			} else {
				return rjson(1, '修改失败，您可能未做任何修改');
			}
		} else {
			$info = M('product_api')->where(array('id' => I('id')))->find();
			$this->assign('info', $info ?: array('id' => 0, 'status' => 1, 'sort' => 0, 'reapi_id' => 0, 'param_id' => 0, 'num' => 1));
			$this->assign('isps', C('ISP_TEXT'));
			return view();
		}
	}
	public function api_status_cg()
	{
		$data = M('product_api')->where(array('id' => I('id')))->setField(array('status' => I('status')));
		if (!$data) {
			return $this->error('操作失败');
		}
		return $this->success("操作成功");
	}
	public function api_del()
	{
		if (M('product_api')->where(array('id' => I('id')))->delete()) {
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败');
		}
	}
	public function phone()
	{
		$map = array();
		if (I('key')) {
			$map['prefix|phone|province|city|isp'] = I('key');
		}
		$list = M('phone')->where($map)->order('prefix asc')->paginate(C('LIST_ROWS'));
		$this->assign('_list', $list);
		return view();
	}
	public function phone_edit()
	{
		if (request()->isPost()) {
			$arr = I('post.');
			if (M('phone')->where(array('phone' => I('phone')))->find()) {
				unset($arr['phone']);
				$data = M('phone')->where(array('phone' => I('phone')))->update($arr);
				if ($data) {
					return $this->success('更新成功');
				} else {
					return $this->error('更新失败');
				}
			} else {
				$data = M('phone')->insert($arr);
				if ($data) {
					return $this->success('新增成功');
				} else {
					return $this->error('新增失败');
				}
			}
		} else {
			if (I('phone')) {
				$info = M('phone')->where(array('phone' => I('phone')))->find();
				$this->assign("info", $info);
			}
		}
		return view();
	}
	public function phone_del()
	{
		if (M('phone')->where(array('phone' => I('phone')))->delete()) {
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败');
		}
	}
	public function elecity()
	{
		$list = M('electricity_city e')->where(array('e.pid' => 0))->order('e.initial asc,e.sort asc')->field('e.*,(select count(*) from dyr_electricity_city where e.id=pid and is_del=0) as city_num')->select();
		$this->assign('_list', $list);
		return view();
	}
	public function elecity_edit()
	{
		if (request()->isPost()) {
			$arr = I('post.');
			unset($arr['id']);
			$arr['initial'] = strtoupper($arr['initial']);
			if (I('id')) {
				$data = M('electricity_city')->where(array('id' => I('id')))->update($arr);
				if ($data) {
					return $this->success('更新成功');
				} else {
					return $this->error('更新失败');
				}
			} else {
				$data = M('electricity_city')->insert($arr);
				if ($data) {
					return $this->success('新增成功');
				} else {
					return $this->error('新增失败');
				}
			}
		} else {
			$info = M('electricity_city')->where(array('id' => I('id')))->find();
			$this->assign("info", $info);
			$this->assign("pcitys", M('electricity_city')->where(array('pid' => 0, 'is_del' => 0))->select());
		}
		return view();
	}
	public function elecityc()
	{
		$list = M('electricity_city')->where(array('pid' => I('pid')))->order('initial asc,sort asc')->select();
		$this->assign('_list', $list);
		return view();
	}
	public function elecity_del()
	{
		if (M('electricity_city')->where(array('id' => I('id')))->setField(array('is_del' => I('is_del')))) {
			return $this->success('操作成功');
		} else {
			return $this->error('操作失败');
		}
	}
	public function elecity_need_city()
	{
		if (M('electricity_city')->where(array('id' => I('id')))->setField(array('need_city' => I('need_city')))) {
			return $this->success('操作成功');
		} else {
			return $this->error('操作失败');
		}
	}
	public function elecity_need_ytype()
	{
		if (M('electricity_city')->where(array('id' => I('id')))->setField(array('need_ytype' => I('need_ytype')))) {
			return $this->success('操作成功');
		} else {
			return $this->error('操作失败');
		}
	}
	public function elecity_force_ytype()
	{
		if (M('electricity_city')->where(array('id' => I('id')))->setField(array('force_ytype' => I('force_ytype')))) {
			return $this->success('操作成功');
		} else {
			return $this->error('操作失败');
		}
	}
	public function elecity_force_city()
	{
		if (M('electricity_city')->where(array('id' => I('id')))->setField(array('force_city' => I('force_city')))) {
			return $this->success('操作成功');
		} else {
			return $this->error('操作失败');
		}
	}
	public function type()
	{
		$list = M('product_type')->field("*,(select cname from dyr_product_typec where id=typec_id) as cname")->order('sort asc,id asc')->select();
		$this->assign('_list', $list);
		return view();
	}
	public function type_edit()
	{
		if (request()->isPost()) {
			$arr = I('post.');
			$arr['tishidoc'] = isset($_POST['tishidoc']) ? $_POST['tishidoc'] : '';
			unset($arr['id']);
			if (I('id')) {
				$data = M('product_type')->where(array('id' => I('id')))->setField($arr);
				if ($data) {
					return $this->success('更新成功');
				} else {
					return $this->error('更新失败');
				}
			} else {
				$data = M('product_type')->insert($arr);
				if ($data) {
					return $this->success('新增成功');
				} else {
					return $this->error('新增失败');
				}
			}
		} else {
			$this->assign("typecs", M('product_typec')->select());
			$info = M('product_type')->where(array('id' => I('id')))->find();
			$this->assign("info", $info);
		}
		return view();
	}
	public function typec()
	{
		$list = M('product_typec')->select();
		$this->assign('_list', $list);
		return view();
	}
	public function typec_edit()
	{
		if (request()->isPost()) {
			$arr = I('post.');
			unset($arr['id']);
			if (I('id')) {
				$data = M('product_typec')->where(array('id' => I('id')))->setField($arr);
				if ($data) {
					return $this->success('更新成功');
				} else {
					return $this->error('更新失败');
				}
			} else {
				$data = M('product_typec')->insert($arr);
				if ($data) {
					return $this->success('新增成功');
				} else {
					return $this->error('新增失败');
				}
			}
		} else {
			$info = M('product_typec')->where(array('id' => I('id')))->find();
			$this->assign("info", $info);
		}
		return view();
	}
	public function typec_ziduan()
	{
		$list = M('product_typec_ziduan')->where(array('typec_id' => I('typec_id')))->order('sort asc,id asc')->select();
		$this->assign('_list', $list);
		return view();
	}
	public function typec_ziduan_edit()
	{
		if (request()->isPost()) {
			$arr = I('post.');
			unset($arr['id']);
			if (I('id')) {
				$data = M('product_typec_ziduan')->where(array('id' => I('id')))->setField($arr);
				if ($data) {
					return $this->success('更新成功');
				} else {
					return $this->error('更新失败');
				}
			} else {
				$data = M('product_typec_ziduan')->insert($arr);
				if ($data) {
					return $this->success('新增成功');
				} else {
					return $this->error('新增失败');
				}
			}
		} else {
			$zds = array('param1', 'param2', 'param3');
			$ziduan = M('product_typec_ziduan')->where(array('typec_id' => I('typec_id')))->field('zi_duan')->select();
			$ziduan && ($zds = array_diff($zds, array_column($ziduan, 'zi_duan')));
			$info = M('product_typec_ziduan')->where(array('id' => I('id')))->find();
			$this->assign("info", $info);
			if ($info) {
				array_push($zds, $info['zi_duan']);
			}
			$this->assign('_zds', $zds);
		}
		return view();
	}
	public function typec_ziduan_del()
	{
		if (M('product_typec_ziduan')->where(array('id' => I('id')))->delete()) {
			return $this->success('操作成功');
		} else {
			return $this->error('操作失败');
		}
	}
}