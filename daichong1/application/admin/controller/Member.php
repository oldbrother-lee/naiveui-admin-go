<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use Util\GoogleAuth;
class Member extends Admin
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
			$map['id|nickname'] = array('like', '%' . I('key') . '%');
		}
		if (intval(I('status')) > 0) {
			$map['status'] = intval(I('status')) - 1;
		}
		$map['id'] = array('neq', 1);
		$data = D('member')->where($map)->field('id,nickname,sex,login,reg_ip,reg_time,last_login_ip,last_login_time,status,headimg')->order('id asc')->paginate(C('LIST_ROWS'));
		$this->assign('list', $data);
		return view();
	}
	public function forbidden()
	{
		if (I('id')) {
			$member = M('member')->where('id=' . I('id'))->find();
			if ($member['status'] == 1) {
				$arr['status'] = 0;
				$ret = M('member')->where('status=1 and id=' . I('id'))->setField($arr);
				if ($ret) {
					return $this->success('禁用成功!');
				}
			} elseif ($member['status'] == 0) {
				$arr['status'] = 1;
				$ret = D('member')->where('status=0 and id=' . I('id'))->setField($arr);
				if ($ret) {
					return $this->success('启用成功!');
				}
			} else {
				return $this->error('操作失败!');
			}
		}
	}
	public function edit()
	{
		if (request()->isPost()) {
			if (!I('nickname')) {
				return $this->error('登录名不能为空！');
			}
			if (I('id')) {
				$map['nickname'] = I('nickname');
				$map['headimg'] = I('headimg');
				$map['sex'] = I('sex');
				$map['status'] = I('status');
				$data = M('Member')->where(array('id' => I('id')))->setField($map);
				if ($data) {
					return $this->success('保存成功', U('index'));
				} else {
					return $this->error('保存失败,可能您未修改数据！');
				}
			} else {
				if (M('Member')->where('nickname', I('nickname'))->find()) {
					return $this->error('该用户已存在！');
				}
				$data['nickname'] = I('nickname');
				$data['headimg'] = I('headimg');
				$data['sex'] = I('sex');
				$data['status'] = I('status');
				$data['password'] = dyr_encrypt('123456');
				$data['reg_time'] = time();
				$ret = M('Member')->insertGetId($data);
				if ($ret) {
					return $this->success('保存成功', U('index'));
				} else {
					return $this->error('保存失败');
				}
			}
		} else {
			$info = M('Member')->where(array('id' => I('id')))->find();
			$this->assign('info', $info);
			return view();
		}
	}
	public function deletes()
	{
		if (I('id') && I('id') != 1) {
			$result = D('member')->where(array("id" => I('id')))->setField(array('is_del' => 1));
			if ($result) {
				return $this->success('删除成功!');
			} else {
				return $this->error('删除失败！');
			}
		} else {
			return $this->error('无法删除');
		}
	}
	public function uppassword()
	{
		if (!I('id') || !I('password')) {
			return $this->error("请输入密码！");
		}
		$ret = D('member')->reset_pwd(I('id'), I('password'));
		if ($ret) {
			return $this->success('保存成功!');
		} else {
			return $this->error("重置失败，重置密码可能与原密码相同！");
		}
	}
	public function log()
	{
		$map = array();
		if (I('key')) {
			$map['title|name|url|ip|param'] = array('like', '%' . I('key') . '%');
		}
		if (I('member_id')) {
			$map['member_id'] = I('member_id');
		}
		if (I('method')) {
			$map['method'] = I('method');
		}
		$data = M('member_log')->where($map)->order('create_time desc')->paginate(100);
		$this->assign('list', $data);
		$this->assign('member', D('member')->where(array('is_del' => 0))->select());
		$this->assign('methods', M('member_log')->Distinct(true)->field('method')->select());
		return view();
	}
	public function reset_google_auth_secret()
	{
		$goret = GoogleAuth::verifyCode($this->adminuser['google_auth_secret'], I('verifycode'), 1);
		if (!$goret) {
			return $this->error("谷歌身份验证码错误！");
		}
		M('member')->where(array('id' => I('id')))->setField(array('google_auth_secret' => ''));
		return $this->success('重置成功');
	}
}