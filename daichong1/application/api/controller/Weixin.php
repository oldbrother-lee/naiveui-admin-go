<?php

//decode by http://chiran.taobao.com/
namespace app\api\controller;

use app\common\library\Userlogin;
use app\common\model\Client;
class Weixin extends Base
{
	public function _apibase()
	{
		$this->weixin = new \Util\Wechat($this->wxconfig);
	}
	public function getOauthRedirect()
	{
		$view_url = HTTP_TYPE . $_SERVER['HTTP_HOST'] . "/#/pages/login/oauth";
		$url = $this->weixin->getOauthRedirect($view_url, 'STATE', 'snsapi_userinfo');
		return djson(0, '', $url);
	}
	public function getOauthAccessToken()
	{
		$res = $this->weixin->getOauthAccessToken();
		if ($res['errno'] != 0) {
			return djson($res['errno'], $res['errmsg'], $res['data']);
		}
		$oauthdata = $res['data'];
		$res = $this->weixin->getOauthUserinfo($oauthdata['access_token'], $oauthdata['openid']);
		if ($res['errno'] != 0) {
			return djson($res['errno'], $res['errmsg'], $res['data']);
		}
		$userinfo = $res['data'];
		if ($customer = M('customer')->where(array("wx_openid" => $userinfo['openid'], 'is_del' => 0))->field("id")->find()) {
			Userlogin::wx_up_userinfo($customer['id'], $userinfo);
			$data = Userlogin::create_login_data($customer['id']);
			return djson($data['errno'], $data['errmsg'], $data['data']);
		} else {
			$regret = Userlogin::wxh5_user_reg($userinfo, I('vi'), $this->wxconfig['appid'], Client::CLIENT_WX);
			if ($regret['errno'] != 0) {
				return djson($regret['errno'], $regret['errmsg']);
			}
			$data = Userlogin::create_login_data($regret['data']['id']);
			return djson($data['errno'], $data['errmsg'], $data['data']);
		}
	}
	function create_js_config()
	{
		$res = $this->weixin->get_jspai_tiket();

		//dump($res);die;
		if ($res['errno'] != 0) {
			return djson($res['errno'], $res['errmsg'], $res['data']);
		}
		$tiket = $res['data'];
		$url = I('url');
		$data['appid'] = $this->wxconfig['appid'];
		$data['timestamp'] = time();
		$data['noncestr'] = "eryg5293ytAF5";
		$data['signature'] = sha1("jsapi_ticket=" . $tiket . "&noncestr=" . $data['noncestr'] . "&timestamp=" . $data['timestamp'] . "&url=" . $url);
		$data['tiket'] = $tiket;
		return djson(0, '请求成功', array('config' => $data, 'share' => $this->getShareData(I('shareurl'))));
	}
	private function getsharedata($url)
	{
		return array('title' => $this->wxconfig['share_title'], 'desc' => $this->wxconfig['share_desc'], 'link' => $url, 'imgUrl' => $this->wxconfig['share_icon']);
	}
}