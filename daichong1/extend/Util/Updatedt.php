<?php

//decode by http://chiran.taobao.com/
namespace Util;

class Updatedt
{
	protected static $instance;
	//private static $check_url = "http://app.dayuanren.net/index.php/index/app/appapi.html?key=1634714232858";
    private static $check_url = "https://baidu.com";

    private function getFile($url, $path = '', $filename = '', $type = 0)
	{
		if ($url == '') {
			return false;
		}
		if ($type === 0) {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			$img = curl_exec($ch);
			curl_close($ch);
		}
		if ($type === 1) {
			ob_start();
			readfile($url);
			$img = ob_get_contents();
			ob_end_clean();
		}
		if ($type === 2) {
			$img = file_get_contents($url);
		}
		if (empty($img)) {
			return rjson(1, "下载错误,无法下载更新文件！");
		}
		if ($path === '') {
			$path = "./";
		}
		if ($filename === "") {
			$filename = md5($img);
		}
		$ext = substr($url, strrpos($url, '.'));
		if ($ext && strlen($ext) < 5) {
			$filename .= $ext;
		}
		$path = rtrim($path, "/") . "/";
		$fp2 = @fopen($path . $filename, 'a');
		fwrite($fp2, $img);
		fclose($fp2);
		return rjson(0, '下载完成', $filename);
	}
	public function unzip($version, $name)
	{
		$dir = $_SERVER['DOCUMENT_ROOT'] . '/../';
		if (class_exists('ZipArchive')) {
			$zip = new \ZipArchive();
			if ($zip->open($name) !== TRUE) {
				return rjson(1, '无法打开更新文件');
			}
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$filename = $zip->getNameIndex($i);
				$_filename = str_replace('framework-' . $version, '', $filename);
				@mkdir(dirname($dir . $_filename), 0777, true);
				@copy("zip://" . $name . "#" . $filename, $dir . $_filename);
			}
			$zip->close();
			return rjson(0, '解压完成', $dir);
		}
		return rjson(1, '服务器环境异常，无法解压更新文件，请确保ZipArchive安装正确');
	}
	public function checkThinkPHPVersion()
	{
		return C('dtupdate.version');
	}
	public function download($download_url, $version, $type = 0, $checkv = true)
	{
		$old_version = $this->checkThinkPHPVersion();
		if ($checkv && $old_version >= $version) {
			return rjson(1, "当前版本不需要更新！" . $old_version . $version);
		}
		$filename = $version . '-' . time();
		return $this->getFile($download_url, '', $filename, $type);
	}
	public function start($version, $download_url, $type = 2, $checkv = true)
	{
		$dowres = $this->download($download_url, $version, $type, $checkv);
		if ($dowres['errno'] != 0) {
			return $dowres;
		}
		$filename = $dowres['data'];
		$res = $this->unzip($version, $filename);
		unlink($filename);
		return $res;
	}
	public function check()
	{
	    $res['data'] = array(
	        'content' => '已是最新版'
	        );
	    $res['errno'] = 0;
	    /*$res['data']['wgt'] = array(
	        'version' => '2.5.2',
	        'desc'    => '已是最新版本'
	        );
	   */
		return rjson(2, '当前版本不需要更新！', $res['data']);
		die;
		//$json = Http::get(self::$check_url, array('host' => $_SERVER['HTTP_HOST']));
		//$res = json_decode($json, true);

        $res['data']['wgt']['version']=1;
		if (!$res) {
			return rjson(1, '异常，无法检查版本号,请联系官方客服人员，电话/微信:15537288720');
		}
		if ($res['code'] == 0 && $res['data']['wgt']) {
			$localv = floatval(C('dtupdate.version'));
			$onlinev = floatval($res['data']['wgt']['version']);
			if ($localv >= $onlinev) {
				return rjson(2, '当前版本不需要更新！', $res['data']);
			}
			return rjson(0, '系统可更新', $res['data']);
		} else {
			return rjson(1, '异常，无法检查版本号，提示：' . $res['msg']);
		}
	}
	public function executesql()
	{
		$sqlfile = $_SERVER['DOCUMENT_ROOT'] . '/../update.sql';
		if (!file_exists($sqlfile)) {
			return rjson(1, '不存在sql更新文件');
		}
		$sql = file_get_contents($sqlfile);
		$sqlqrr = explode("\r\n", $sql);
		$data = array();
		foreach ($sqlqrr as $k => $hang) {
			try {
				M()->strict(false)->query($hang);
			} catch (\Exception $exception) {
				$data[] = '[' . $k . '行]' . $exception->getMessage();
			}
		}
		unlink($sqlfile);
		return rjson(0, 'sql更新成功', $data);
	}
	public function log($version, $sql_ret, $zip_url)
	{
		M('sysupdate_log')->insertGetId(array('version' => $version, 'sql_ret' => $sql_ret, 'zip_url' => $zip_url, 'create_time' => time()));
	}
}