<?php

//decode by http://chiran.taobao.com/
namespace app\agent\controller;

use app\common\model\Product;
use app\common\model\Product as ProductModel;
use think\Request;
use Util\GoogleAuth;
class User extends Admin
{
	public function infos()
	{
		if (Request::instance()->isPost()) {
			M('customer')->where(array('id' => $this->user['id']))->setField(array('headimg' => I('headimg'), 'ip_white_list' => I('ip_white_list')));
			return $this->success("保存成功！");
		} else {
			$types = M('product_type')->where(array('status' => 1))->field('id,type_name,typec_id')->order('sort asc,id asc')->select();
			foreach ($types as $key => &$type) {
				$typec = ProductModel::getTypec($type['typec_id']);
				$type['typec'] = $typec;
			}
			$info = M('customer')->find($this->user['id']);
			$this->assign('info', $info);
			$this->assign('types', $types);
			return view();
		}
	}
	public function uppwd()
	{
		if (I('npwd') == I('ypwd')) {
			return $this->error("新密码不能与原密码相同");
		}
		if (I('npwd') != I('npwd2')) {
			return $this->error("两次输入的新密码不相同");
		}
		if (!M('customer')->where(array('id' => $this->user['id'], 'password' => dyr_encrypt(I('ypwd'))))->find()) {
			return $this->error("密码错误");
		}
		M('customer')->where(array('id' => $this->user['id']))->setField(array('password' => dyr_encrypt(I('npwd'))));
		session('user_auth_agent', null);
		return $this->success("修改成功！");
	}
	public function balance()
	{
		$map['customer_id'] = $this->user['id'];
		if (I('style')) {
			$map['style'] = I('style');
		}
		if (I('key')) {
			$map['remark'] = array('like', '%' . I('key') . '%');
		}
		if (I('end_time') && I('begin_time')) {
			$map['create_time'] = array('between', array(strtotime(I('begin_time')), strtotime(I('end_time'))));
		}
		$list = M('balance_log')->where($map)->order("id desc")->paginate(30);
		$this->assign('_list', $list);
		return view();
	}
}