<?php

//decode by http://chiran.taobao.com/
namespace app\api\controller;

use Qiniu\QiniuStorage;
use Util\Random;
class Qiniu extends Base
{
	public function _apibase()
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
}