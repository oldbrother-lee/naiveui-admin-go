<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

class Reapi extends Admin
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
		$list = M('reapi')->where(array('is_del' => 0))->select();
		$this->assign('_list', $list);
		return view();
	}
	public function edit()
	{
		if (request()->isPost()) {
			$arr = $_POST;
			if (I('id')) {
				$data = M('reapi')->where(array('id' => I('id')))->setField($arr);
				if ($data) {
					return $this->success('保存成功');
				} else {
					return $this->error('编辑失败');
				}
			} else {
				$aid = M('reapi')->insertGetId($arr);
				if ($aid) {
					return $this->success('新增成功');
				} else {
					return $this->error('新增失败');
				}
			}
		} else {
			$info = M('reapi')->where(array('id' => I('id')))->find();
			$this->assign('info', $info);
			return view();
		}
	}
	public function param()
	{
		$api = M('reapi')->where(array('id' => I('id')))->find();
		if (!$api) {
			return $this->error('参数错误');
		}
		$this->assign('api', $api);
		$list = M('reapi_param')->where(array('reapi_id' => I('id')))->select();
		$this->assign('_list', $list);
		return view();
	}
	public function param_edit()
	{
		if (request()->isPost()) {
			$arr = $_POST;
			if (I('id')) {
				$data = M('reapi_param')->where(array('id' => I('id')))->setField($arr);
				if ($data) {
					return $this->success('保存成功');
				} else {
					return $this->error('编辑失败');
				}
			} else {
				$data = M('reapi_param')->insertGetId($arr);
				if ($data) {
					return $this->success('添加成功');
				} else {
					return $this->error('添加失败');
				}
			}
		} else {
			$api = M('reapi')->where(array('id' => I('reapi_id')))->find();
			if (!$api) {
				return $this->error('参数错误');
			}
			$info = M('reapi_param')->where(array('id' => I('id')))->find();
			$this->assign('info', $info);
			return view();
		}
	}
	public function deletes()
	{
		$reapi = M('reapi')->where(array('id' => I('id')))->find();
		if (!$reapi) {
			return $this->error('未找到接口');
		}
		if (M('product_api')->where(array('reapi_id' => $reapi['id']))->find()) {
			return $this->error('该接口还有产品在使用中，请先取消接口绑定的所有产品');
		}
		if (M('reapi')->where(array('id' => $reapi['id']))->setField(array('is_del' => 1))) {
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败');
		}
	}
	public function deletes_param()
	{
		$param = M('reapi_param')->where(array('id' => I('id')))->find();
		if (!$param) {
			return $this->error('未找到套餐');
		}
		if (M('product_api')->where(array('param_id' => $param['id']))->find()) {
			return $this->error('该套餐还有产品在使用中，请先取消接口套餐绑定的所有产品');
		}
		if (M('reapi_param')->where(array('id' => $param['id']))->delete()) {
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败');
		}
	}
	public function get_reapi_param()
	{
		$map = array();
		if (I('reapi_id')) {
			$map['reapi_id'] = I('reapi_id');
		}
		$lists = M('reapi_param')->where($map)->order("desc asc")->select();
		return djson(0, 'ok', $lists);
	}
	public function fenxi()
	{
		$apmap['is_del'] = 0;
		if (I('reapi_id')) {
			$apmap['id'] = I('reapi_id');
		}
		$apis = M('reapi')->where($apmap)->select();
		if (!$apis) {
			return $this->error('参数错误');
		}
		if (I('end_time') && I('begin_time')) {
			$start_time = strtotime(date("Y-m-d", strtotime(I('begin_time'))));
			$end_time = strtotime(date("Y-m-d", strtotime(I('end_time')))) + 86400;
		} else {
			$start_time = strtotime(date("Y-m-01", time()));
			$end_time = strtotime(date("Y-m-d", time())) + 86400;
		}
		$datas = array();
		foreach ($apis as $api) {
			$arr['name'] = $api['name'];
			$arr['date_start'] = date("Y-m-d", $start_time);
			$arr['date_end'] = date("Y-m-d", $end_time);
			$arr['all_count'] = M('porder_apilog')->where(array('reapi_id' => $api['id'], 'create_time' => array('between', array($start_time, $end_time))))->count();
			$arr['ing_count'] = M('porder')->where(array('api_cur_id' => $api['id'], 'apireq_time' => array('between', array($start_time, $end_time))))->where(array('status' => 3))->count();
			$arr['sus_count'] = M('porder')->where(array('api_cur_id' => $api['id'], 'apireq_time' => array('between', array($start_time, $end_time))))->where(array('status' => array('in', array(4, 12, 13))))->count();
			$arr['sus_price'] = M('porder')->where(array('api_cur_id' => $api['id'], 'apireq_time' => array('between', array($start_time, $end_time))))->where(array('status' => array('in', array(4))))->sum("total_price");
			$arr['sus_cost'] = M('porder')->where(array('api_cur_id' => $api['id'], 'apireq_time' => array('between', array($start_time, $end_time))))->where(array('status' => array('in', array(4))))->sum("cost");
			$arr['pasus_price'] = M('porder')->where(array('api_cur_id' => $api['id'], 'apireq_time' => array('between', array($start_time, $end_time))))->where(array('status' => array('in', array(12, 13))))->sum("total_price-refund_price");
			$arr['pasus_cost'] = M('porder')->where(array('api_cur_id' => $api['id'], 'apireq_time' => array('between', array($start_time, $end_time))))->where(array('status' => array('in', array(12, 13))))->sum("cost*(1-refund_price/total_price)");
			$arr['sus_ratio'] = $arr['all_count'] ? round($arr['sus_count'] / $arr['all_count'] * 100, 2) : 0;
			$datas[] = $arr;
		}
		$this->assign('data', $datas);
		$this->assign('apis', M('reapi')->where(array('is_del' => 0))->select());
		return view();
	}
}