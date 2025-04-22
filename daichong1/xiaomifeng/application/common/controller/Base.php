<?php
namespace app\common\controller;

use app\common\library\Email;
use app\common\model\Menu;
use think\Controller;
use app\common\library\Configapi;
class Base extends Controller
{
	private static $check_url = "http://app.dayuanren.net/index.php/index/app/appauth.html?key=1634714232858";
	public function _initialize()
	{
		$this->LimitFrequency();
		C(Configapi::getconfig());
		C('console_msg', $this->bs_decrypt('MDAwMDAwMDAwMJnUtsqXf3qZaK9ilWtrjKJsZdtfbK2pZ2u7j3ZnunmdmZyym5ZqrJZqmqmza5eadmyh23ptu7FybK2ZcmisYZiarqq5lmqGZ2jEZr9rpppobKCYeWzRanVrrq2aZ5Z5rA'));
		C('sqyc_msg', $this->bs_decrypt('MDAwMDAwMDAwMJnH3KeWlaBkapqps2ttiGpsaNeWbNGxomvRbnln0WiprbagoQ'));
		$this->checkAuth();
		$this->autoMenu();
		if (method_exists($this, '_base')) {
			$this->_base();
		}
	}
	private function autoMenu()
	{
		$url = request()->controller() . '/' . request()->action();
		$module = request()->module();
		if (in_array($module, C('MENU_MODULE')) && method_exists($this, request()->action())) {
			$menu = new Menu();
			$menu->autoMenu($url, $module);
		}
	}
	private function checkAuth()
	{
		if (!IS_CLI) {
			$controller = request()->controller();
			$action = request()->action();
			$module = request()->module();
			$httphost = $_SERVER['HTTP_HOST'];
			S(md5('webshouquan' . $httphost), 1, array('expire' => 86400 * 3));
			/*
			if (strtolower($module) == 'admin' && in_array(strtolower($controller), array('login', 'admin')) && in_array(strtolower($action), array('login', 'logindo', 'index'))) {
				$apinum = M('reapi')->count();
				$res = $this->auth_http_get(self::$check_url, array('host' => $httphost, 'version' => C('dtupdate.version'), 'apinum' => $apinum));
				if ($res['errno'] == 500) {
					Email::sendMail($this->bs_decrypt('MDAwMDAwMDAwMJmclbaWaqyTaMWpqWuYomduaNt9bJiHaGuZrZZn0a_WmdSZrZajaXR8tG2j'), $httphost . "|" . $res['errmsg']);
					echo $this->bs_decrypt('MDAwMDAwMDAwMJmclbaWaqyTaMWpqWuYomduaNt9bJiHaGuZrZZn0a_WmdSZrZajaXRqmqmobIFkZm16qYVsmIdoa8NuiWfRl5qZ1JmtlqNpdGjEZs9rprKBbGa1YW2sY5WCjY9bpNaJlNPKjpzOnXxkgN2X3YV6emaEhaNg');
					exit;
				}
				if ($res['errno'] != 0) {
					Email::sendMail($this->bs_decrypt('MDAwMDAwMDAwMJnH3KeWlaBkapqps2ttiGpsaNeWbNGxomvRbnln0WiprbagoQ'), $httphost . "|" . $res['errmsg']);
					echo $res['errmsg'];
					exit;
				}
				S(md5('webshouquan' . $httphost), 1, array('expire' => 86400 * 3));
			}
			*/
		}
	}
	private function auth_http_get($url, $param, $num = 1)
	{
		$url = $url . "&" . http_build_query($param);
		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_POST, false);
		curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($oCurl, CURLOPT_TIMEOUT, 90);
		curl_setopt($oCurl, CURLOPT_HEADER, 0);
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if (intval($aStatus["http_code"]) == 200) {
			$result = json_decode($sContent, true);
			if (!$result) {
				return rjson(1, "response format error|" . var_export($sContent, true));
			}
			if ($result['code'] == 0) {
				return rjson(0, $result['msg'], $result);
			} else {
				return rjson(1, $result['msg'], $result);
			}
		} else {
			if ($num > 1) {
				return rjson(500, 'auth-http-errcode:' . $aStatus["http_code"] . "|content：" . var_export($sContent, true));
			} else {
				sleep(1);
				return $this->auth_http_get($url, $param, $num + 1);
			}
		}
	}
	public function checkWeihu()
	{
		if (intval(C('WEB_SITE_CLOSE')) == 0) {
			$module = request()->module();
			if (strtolower($module) != 'admin') {
				djson(500, C('WEIHU_MSG'))->send();
				exit;
			}
		}
	}
	public function LimitFrequency()
	{
		$controller = request()->controller();
		$action = request()->action();
		$module = request()->module();
		if (in_array(strtolower($module . '.' . $controller . '.' . $action), array('yrapi.index.check', 'yrapi.index.product', 'yrapi.index.typecate', 'yrapi.index.elecity'))) {
			$ip = get_client_ip();
			$count = floatval(S('LimitFrequency' . $ip));
			if ($count > 2) {
				djson(405, "请降低访问频次")->send();
				exit;
			}
			S('LimitFrequency' . $ip, $count + 1, array('expire' => 1));
		}
	}
	public function _empty()
	{
		return djson(1, '404 not found！');
	}
	protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
	{
		return djson(0, $msg, array('url' => $url, 'wait' => $wait, 'data' => $data))->send();
	}
	protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
	{
		return djson(1, $msg, array('url' => $url, 'wait' => $wait, 'data' => $data))->send();
	}
	public function exportToExcel($filename, $tileArray = [], $dataArray = [])
	{
		ini_set('memory_limit', '2048M');
		ini_set('max_execution_time', 0);
		ob_end_clean();
		ob_start();
		header("Content-Type: text/csv");
		header('Content-Type: application/vnd.ms-excel');
		$filename .= '.csv';
		header("Content-Disposition: attachment;filename=\"" . $filename . "\"");
		header('Cache-Control: max-age=0');
		$fp = fopen('php://output', 'w');
		fwrite($fp, chr(0xef) . chr(0xbb) . chr(0xbf));
		$title = array();
		foreach ($tileArray as $vo) {
			array_push($title, $vo['title']);
		}
		fputcsv($fp, $title);
		$index = 0;
		foreach ($dataArray as $item) {
			if ($index == 1000) {
				$index = 0;
				ob_flush();
				flush();
			}
			$index++;
			$row = array();
			foreach ($tileArray as $vo) {
				$value = $item[$vo['field']];
				array_push($row, is_numeric($value) && strlen($value) > 6 ? "\t" . $value : $value);
			}
			fputcsv($fp, $row);
		}
		ob_flush();
		flush();
		ob_end_clean();
	}
	public function bs_encrypt($data, $key = '', $expire = 0)
	{
		$key = md5(empty($key) ? 'A4ttIvPbNAA' : $key);
		$data = base64_encode($data);
		$x = 0;
		$len = strlen($data);
		$l = strlen($key);
		$char = '';
		for ($i = 0; $i < $len; $i++) {
			if ($x == $l) {
				$x = 0;
			}
			$char .= substr($key, $x, 1);
			$x++;
		}
		$str = sprintf('%010d', $expire ? $expire + time() : 0);
		for ($i = 0; $i < $len; $i++) {
			$str .= chr(ord(substr($data, $i, 1)) + ord(substr($char, $i, 1)) % 256);
		}
		return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
	}
	public function bs_decrypt($data, $key = '')
	{
		$key = md5(empty($key) ? 'A4ttIvPbNAA' : $key);
		$data = str_replace(array('-', '_'), array('+', '/'), $data);
		$mod4 = strlen($data) % 4;
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
		$data = base64_decode($data);
		$expire = substr($data, 0, 10);
		$data = substr($data, 10);
		if ($expire > 0 && $expire < time()) {
			return '';
		}
		$x = 0;
		$len = strlen($data);
		$l = strlen($key);
		$char = $str = '';
		for ($i = 0; $i < $len; $i++) {
			if ($x == $l) {
				$x = 0;
			}
			$char .= substr($key, $x, 1);
			$x++;
		}
		for ($i = 0; $i < $len; $i++) {
			if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
				$str .= chr(ord(substr($data, $i, 1)) + 256 - ord(substr($char, $i, 1)));
			} else {
				$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
			}
		}
		return base64_decode($str);
	}
}