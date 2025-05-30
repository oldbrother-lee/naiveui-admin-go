<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use Qiniu\QiniuStorage;
use Util\Random;
class Qiniu extends Admin
{
	public function _init()
	{
		$this->qiniuconfig = C('QINIU');
		$this->qiniu = new QiNiuStorage($this->qiniuconfig);
	}
	public function get_token()
	{
		$token = $this->qiniu->getToken();
		$filename = I('filename') ?: "";
		if ($filename) {
			$filename = time() . Random::alnum(6) . substr($filename, strrpos($filename, '.'));
		}
		return djson(0, 'ok', array('token' => $token, 'domain' => $this->qiniuconfig['domain'], 'prefix' => $this->qiniuconfig['prefix'], 'filename' => $filename));
	}
	public function uploadOne()
	{
		$style = intval(C('UPLOAD_STYLE'));
		switch ($style) {
			case 1:
				return $this->uploadLocal();
			case 2:
				return $this->uploadQiniu();
			default:
				return djson(1, '暂时不支持的上传方式');
		}
	}
	public function uploadLocal()
	{
		$file = request()->file('file');
		if (empty($file)) {
			return djson(1, '请选择上传文件');
		}
		$file_info = $file->getInfo();
		$info = $file->validate(array('size' => C('DOWNLOAD_UPLOAD.maxSize'), 'ext' => C('DOWNLOAD_UPLOAD.exts')))->move(C('DOWNLOAD_UPLOAD.movePath'));
		if ($info) {
			$result['domain'] = $_SERVER['HTTP_HOST'];
			$result['url'] = HTTP_TYPE . $_SERVER['HTTP_HOST'] . C('DOWNLOAD_UPLOAD.rootPath') . $info->getSaveName();
			$result['size'] = round($file_info['size'] / 1024 / 1024, 3);
			return djson(0, '上传成功', $result);
		} else {
			return djson(1, $file->getError());
		}
	}
	public function uploadQiniu()
	{
		ini_set('memory_limit', '3072M');
		set_time_limit(0);
		$file = $_FILES['file'];
		$upfile = array('name' => 'file', 'fileName' => time() . Random::alnum(6) . substr($file['name'], strrpos($file['name'], '.')), 'fileBody' => file_get_contents($file['tmp_name']));
		$config = array('Expires' => 3600, 'saveName' => '', 'custom_fields' => array());
		$result = $this->qiniu->upload($config, $upfile);
		if ($result) {
			$result['domain'] = C('QINIU')['domain'];
			$result['url'] = C('QINIU')['prefix'] . '://' . C('QINIU')['domain'] . DS . $result['key'];
			$result['size'] = round($file['size'] / 1024 / 1024, 3);
			return djson(0, '上传成功', $result);
		} else {
			return djson(1, '上传失败', array('error' => $this->qiniu->error, 'errorStr' => $this->qiniu->errorStr));
		}
	}
}