<?php

use think\Log;

/**
 * [payLog 日志log]
 * @param  [type] $mark        [日志的备注，显示在日志文件中]
 * @param  [type] $log_content [日志内容，支持数组或字符串，自动转json格式]
 * @param string $keyp [日志名，默认为当前时间命名]
 * @return [type]              [description]
 */

function mylog($mark, $log_content, $keyp = "")
{

    $max_size = 30000000;

    if ($keyp == "") {

        $log_filename = RUNTIME_PATH . '/tlogs/' . date('Ym-d') . ".log";
    } else {

        $log_filename = RUNTIME_PATH . '/tlogs/' . $keyp . ".log";
    }
    if (file_exists($log_filename) && (abs(filesize($log_filename)) > $max_size)) {

        rename($log_filename, dirname($log_filename) . DS . date('Ym-d-His') . $keyp . ".log");
    }



    file_put_contents($log_filename, '   ' . date('Y-m-d H:i:s', time()) . " key：" . $mark . "\r\n" . $log_content . "\r\n------------------------ --------------------------\r\n", FILE_APPEND);
}

/**
 * @param $mobile 检测手机号码
 * @return true   是否正常
 */
function konghaojiance($mobile)
{
    $getmobile_blacklist = M('mobile_blacklist')->where(['mobile' => $mobile])->find();
    if ($getmobile_blacklist) {
        mylog('空号检测API' . $mobile, '返回：该账号已经被拉黑', 'KH_');

        return  false;
    }

    $data = [
        'account' => '18635637493',
        'mobile' => $mobile,
        'password' => '123456',
    ];
    $header = [
        'content-type: application/json'
    ];
    $url = 'http://101.133.158.29:8000/api/open/real/check';

    $res = df_post($url, $data, $header);
    mylog('空号检测API' . $mobile, json_encode($data) . '返回：' . $res, 'KH_' . date('Y-m-d', time()));
    $redata = json_decode($res, true);

    $code = false;
    if ($redata['data'][0]['status'] == 1) {
        return  true;
    } else {
        $arr['mobile'] = $mobile;
        $arr['limit_time'] = 3650 * 86400 + time();
        M('mobile_blacklist')->insertGetId($arr);
        mylog('账号检测该账号为' . $redata['data'][0]['statusName'] . '运营商类型 1移动 2联通 3电信', '返回：' . $redata['data'][0]['status'] . '——账号拉黑操作', 'KH_' . date('Y-m-d H', time()));
        return false;
    }
    //{"account":"18635637493","mobile":"18124438748","password":"123456"}返回：{"stateCode":"200","stateMsg":"成功","data":[{"mobile":"18124438748","area":"广东-揭阳","numberType":"中国电信","status":4,"statusName":"沉默号"}]}
    //  2023-06-11 17:18:13 key：空号检测API17371066662
    //{"account":"18635637493","mobile":"17371066662","password":"123456"}返回：{"stateCode":"200","stateMsg":"成功","data":[{"mobile":"17371066662","area":"湖北-武汉","numberType":"中国电信","status":1,"statusName":"实号"}]}






}


///xiaomifeng interface sign function for getorders
// add by Lif at 2023-10-10  sign
function get_xiaomifeng_sign($params, $appsecret)
{
    //对参数按key进行排序
    ksort($params);

    //连接所有参数名与参数值
    $buff = '';
    foreach ($params as $k => $v) {
        if ($k != "sign") {
            $buff .= $k . $v;
        }
    }
    //连接加密串
    $buff .= $appsecret;
    //使用md5计算参数串哈希值
    $params['sign'] = md5($buff);

    return $params;
}

//访问
// add by Lif at 2023-10-10 
function curl($url, $data)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
    ));

    $response = curl_exec($curl);

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($httpCode == 404) {
        $response = '404 Page Not Found';
    }
    curl_close($curl);
    return $response;
}

//方法改造了
//$token = 接口url
//$action = get / status
//$user = user 对象
//$jsonStr = 请求参数的 data 数据
function dc_action($token, $action, $user, $jsonStr)
{
    /*
    $data = [
        'action' => $action,
        'token' => $token,
        'flag' => $flag,
        'data' => $data,
    ];
    $header = [
        // 'Authorization:Bearer ' . $user['token'],
        'content-type: application/json'
    ];
    //   echo "上报订单" . $id .'='.json_encode($data). PHP_EOL;
    $url = 'http://www.mf178.cn/api/phone/request?vip=52';
    $enstr = base64_encode(jiami($data));

    $res = df_post($url, $enstr, $header);
    $rsc = rc4s(base64_decode($res));
//{"ret":0,"msg":"SUCCESS","data":"b18e8b3baaf4450ce30a70207e279376"}
    return $rsc;

   */

   // add by Lif at 2023-10-10  上报

   //appkey = user.user
   //appsecret = user.pwd
   //url = user.token  userapi/sgd/updateStatus  存取就按全的http..../.../../
   //vender_id = user.zhuanqu //渠道id
   
   //status -> $jsonStr = '{"user_order_id":"' + $data['id'] + '","status":"' + $data['result'] + '","rsp_info":"' + $data['remark'] + '"}';
   //get -> $jsonStr = '{"amount":"' + $amountText + '","operator_id":"' + $yunyingText + '","order_num":"' + $countText + '","prov_code":"' + $prov_text + '"}';
   
   $url_base = [
    "ex_url" => "https://shop.task.mf178.cn/",
    "test_url" => "http://test.shop.center.mf178.cn/"
   ];
   $url= $token;
   $timestamp = time();

    //签名前准备
    $arr = json_decode($jsonStr, true);

    //整合参数
    $data = null;
    if($action == "get"){
        $data = array(
            "app_key" => $user['user'],
            "timestamp" => $timestamp,
            "vender_id" => $user['zhuanqu'],
            "data" => $jsonStr,
            "sign" => ""
        );
    }else if($action == "status"){
        $data = array(
            "app_key" => $user['user'],
            "timestamp" => $timestamp,
            "data" => $jsonStr,
            "sign" => ""
        );
    }
    if($data == null){
        echo "改造后，不需要 login和report";
        return "不用理会 " . $action;
    }

    //签名前准备
    $arr = json_decode($jsonStr, true);

    $data = get_xiaomifeng_sign($data, $user['pwd']);

    $response = curl($url, $data);
    //   $responseJson = json_decode($response,true);

    return $response;


    //end add by Lif at 2023-10-10
}


function get_dc_user($id = null)
{
    if (empty($id)) {
        $user = M('daichong_user')->where(['id' => 1])->find();
    } else {
        $user = M('daichong_user')->where(['is_lex' => 2])->order('id asc')->find();
    }
    return $user;
}

function get_dc_token()
{
    $user = get_dc_user();
    $url = 'https://www.xitupt.com/uc/connect/token';

    $data = [
        'grant_type' => 'password',
        'client_id' => 'dc_client',
        'client_secret' => 'b23009b84462c77aa4182c47f2a474a6',
        'username' => $user['user'],
        'password' => $user['pwd'],
    ];
    $msg['code'] = 0;
    $res = http_post($url, $data);
    $res_data = json_decode($res, true);
    if (!empty($res_data['access_token'])) {
        $user_dc = M('daichong_user')->where(['user' => $user])->find();
        if ($user_dc) {
            $user_dc['token'] = $res_data['access_token'];
            $user_dc['time'] = time();
            //   $user_dc['type'] = 1;
            M('daichong_user')->where(['id' => $user_dc['id']])->update($user_dc);
        }
    }
}

function jiami($data, $key = 'd08dec93')
{

    return rc4(json_encode($data), 'd08dec93');
}

function jiemi($data)
{
    $data = base64_decode($data, true);
    dump($data);
}

/**
 * rc4,解密方法直接再一次加密就是解密
 * @param [type] $data 要加密的数据
 * @param [type] $pwd 加密使用的 key
 * @return [type] [description]
 */
function rc4s($data, $pwd = 'd08dec93')
{
    $cipher = '';
    $key[] = "";
    $box[] = "";
    $pwd_length = strlen($pwd);
    $data_length = strlen($data);
    for ($i = 0; $i < 256; $i++) {
        $key[$i] = ord($pwd[$i % $pwd_length]);
        $box[$i] = $i;
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $key[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $data_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $k = $box[(($box[$a] + $box[$j]) % 256)];
        $cipher .= chr(ord($data[$i]) ^ $k);
    }
    return $cipher;
}

function rc4($data, $pwd = 'd08dec93')
{

    $key[] = "";

    $box[] = "";


    $pwd_length = strlen($pwd);

    $data_length = strlen($data);

    $cipher = '';

    for ($i = 0; $i < 256; $i++) {

        $key[$i] = ord($pwd[$i % $pwd_length]);

        $box[$i] = $i;
    }

    for ($j = $i = 0; $i < 256; $i++) {

        $j = ($j + $box[$i] + $key[$i]) % 256;

        $tmp = $box[$i];

        $box[$i] = $box[$j];

        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $data_length; $i++) {

        $a = ($a + 1) % 256;

        $j = ($j + $box[$a]) % 256;

        $tmp = $box[$a];

        $box[$a] = $box[$j];

        $box[$j] = $tmp;

        $k = $box[(($box[$a] + $box[$j]) % 256)];

        $cipher .= chr(ord($data[$i]) ^ $k);
    }

    return $cipher;
}

function df_get($url, $data = '', $header = [])
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => $header,
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

function df_post($url, $data, $header)
{


    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $header,
    ]);

    $response = curl_exec($curl);

    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

/**
 * get请求
 */
function http_post($url, $param)
{
    $oCurl = curl_init();
    if (stripos($url, "https://") !== FALSE) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1);
    }
    if (is_string($param)) {
        $strPOST = $param;
    } else {
        $strPOST = http_build_query($param);
    }

    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POST, true);
    curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($oCurl, CURLOPT_TIMEOUT, 90);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
    curl_setopt($oCurl, CURLOPT_HEADER, 0);
    curl_setopt($oCurl, CURLOPT_HTTPHEADER, ["ContentType:application/x-www-form-urlencoded;charset=utf-8"]);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    $curl_err = curl_error($oCurl);
    curl_close($oCurl);
    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
        //   return rjson(1, $result);

    } else {
        return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"] . 'curl err:' . $curl_err);
    }
}


//decode by http://chiran.taobao.com/
if (!function_exists('dyr_encrypt')) {
    function dyr_encrypt($data, $key = '', $expire = 0)
    {
        $key = md5(empty($key) ? config('md5_prefix') : $key);
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
}
if (!function_exists('dyr_decrypt')) {
    function dyr_decrypt($data, $key = '')
    {
        $key = md5(empty($key) ? C('md5_prefix') : $key);
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
if (!function_exists('get_client_ip')) {
    function get_client_ip($type = 0)
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) {
            return $ip[$type];
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}
if (!function_exists('time_format')) {
    function time_format($time = NULL, $format = 'Y-m-d H:i')
    {
        if (!$time) {
            return "";
        } else {
            return date($format, intval($time));
        }
    }
}
if (!function_exists('magic_time_format')) {
    function magic_time_format($time)
    {
        $day = strtotime(date('Y-m-d', time()));
        $pday = strtotime(date('Y-m-d', strtotime('-1 day')));
        $ppday = strtotime(date('Y-m-d', strtotime('-2 day')));
        $nowtime = time();
        $tc = $nowtime - $time;
        if ($time < $ppday) {
            $str = date('Y-m-d H:i', $time);
        } elseif ($time < $day && $time > $pday) {
            $str = "昨天 " . date('H:i', $time);
        } elseif ($time < $pday && $time > $ppday) {
            $str = "前天 " . date('H:i', $time);
        } elseif ($tc > 60 * 60) {
            $str = floor($tc / (60 * 60)) . "小时前";
        } elseif ($tc > 60) {
            $str = floor($tc / 60) . "分钟前";
        } else {
            $str = "刚刚";
        }
        return $str;
    }
}
if (!function_exists('format_bytes')) {
    function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $i = 0;
        while ($size >= 1024 && $i < 5) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }
}
if (!function_exists('is_weixin_browser')) {
    function is_weixin_browser()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists('create_qrcode')) {
    function create_qrcode($txt, $size = 4)
    {
        $data = $txt;
        $level = 'L';
        $erweima = C('DOWNLOAD_UPLOAD.filePath') . "qr/" . time() . md5($txt) . ".png";
        \Phpqrcode\QRcode::png($data, $erweima, $level, $size);
        return $erweima;
    }
}
if (!function_exists('save_web_image')) {
    function save_web_image($imgurl)
    {
        $imgStr = file_get_contents($imgurl);
        $path = C('DOWNLOAD_UPLOAD.filePath') . "webimg/";
        if (!is_dir($path)) {
            mkdir(iconv("UTF-8", "GBK", $path), 0777, true);
        }
        $filename = md5($imgurl) . ".jpg";
        $fp = fopen($path . $filename, 'wb');
        fwrite($fp, $imgStr);
        return $path . $filename;
    }
}
if (!function_exists('filterEmoji')) {
    function filterEmoji($str)
    {
        $str = preg_replace_callback('/./u', function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $str);
        return $str;
    }
}
if (!function_exists('QCellCore')) {
    function QCellCore($mobile)
    {
        if (substr($mobile, 0, 1) == '1') {
            if ($phone = M('phone')->where(array('phone' => substr($mobile, 0, 7)))->find()) {
                $data['type'] = 1;
                $data['ispstr'] = $phone['isp'];
                $data['prov'] = $phone['province'];
                $data['city'] = $phone['city'];
                $data['isp'] = ispstrtoint($phone['isp']);
                return rjson(0, 'ok', $data);
            } else {
                return rjson(1, '未找到该号码归属地');
            }
        } else {
            return rjson(1, '号码错误');
        }
    }
}
if (!function_exists('ispstrtoint')) {
    function ispstrtoint($ispstr)
    {
        if ('移动' == $ispstr) {
            return 1;
        }
        if ('电信' == $ispstr) {
            return 2;
        }
        if ('联通' == $ispstr) {
            return 3;
        }
        if (strpos($ispstr, '虚拟') !== false) {
            return 4;
        }
        return 0;
    }
}
if (!function_exists('getISPText')) {
    function getISPText($ispidstr)
    {
        if (!$ispidstr) {
            return '';
        }
        $arr = explode(",", $ispidstr);
        $data = array();
        foreach ($arr as $key => $vo) {
            $vo = trim($vo);
            $vo && ($data[] = C('ISP_TEXT')[$vo]);
        }
        return implode(',', $data);
    }
}
if (!function_exists('inArrayDou')) {
    function inArrayDou($arrstr, $needle)
    {
        if (!$arrstr || !is_string($arrstr)) {
            return false;
        }
        $arr = explode(",", $arrstr);
        return in_array($needle, $arr);
    }
}
if (!function_exists('getGradeIdsNameText')) {
    function getGradeIdsNameText($grade_ids)
    {
        $lists = M('customer_grade')->where(array('id' => array('in', $grade_ids)))->select();
        if (!$lists) {
            return "";
        }
        return implode('<br/>', array_column($lists, 'grade_name'));
    }
}
if (!function_exists('checkIp')) {
    function checkIp($ip, $rule)
    {
        $rule_regexp = str_replace('.*', 'a', $rule);
        $rule_regexp = preg_quote($rule_regexp, '/');
        $rule_regexp = str_replace('a', '\\.\\d{1,3}', $rule_regexp);
        if (preg_match('/^' . $rule_regexp . '$/', $ip)) {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists('checkIpRules')) {
    function checkIpRules($ip, $rules_str)
    {
        if (!$rules_str) {
            return true;
        }
        $allow = false;
        $iprules = explode(',', $rules_str);
        foreach ($iprules as $rule) {
            if (checkIp($ip, $rule)) {
                $allow = true;
                break;
            }
        }
        return $allow;
    }
}
function elapsed_time($startint, $endint)
{
    if (!$startint) {
        return "";
    }
    if (!is_numeric($startint)) {
        $startint = strtotime($startint);
    }
    if (!$endint) {
        $endint = time();
    }
    if (!is_numeric($endint)) {
        $endint = strtotime($endint);
    }
    $date = floor(($endint - $startint) / 86400);
    $hour = floor(($endint - $startint) % 86400 / 3600);
    $minute = floor(($endint - $startint) % 86400 % 3600 / 60);
    $miao = floor(($endint - $startint) % 86400 % 3600 % 60);
    $str = $hour . '时' . $minute . '分' . $miao . '秒';
    if ($date) {
        $str = $date . '天' . $str;
    }
    return $str;
}

function parseMaoArr($value)
{
    if (!$value) {
        return array();
    }
    $array = preg_split('/[,;\\r\\n]+/', trim($value, ",;\r\n"));
    if (strpos($value, ':')) {
        $value = array();
        foreach ($array as $val) {
            $k = substr($val, 0, strpos($val, ':'));
            $v = substr($val, strpos($val, ':') + 1);
            $value[$k] = $v;
        }
    } else {
        $value = $array;
    }
    return $value;
}

function get_name($id)
{
    return M('customer')->where('id=' . $id)->value('username');
}

function getReapiName($id)
{
    return M('reapi')->where(array('id' => $id))->value('name');
}

function getReapiParamName($id)
{
    return M('reapi_param')->where(array('id' => $id))->value('desc');
}

function get_user_grade_name($id)
{
    return M('customer c')->join('customer_grade g', 'g.id=c.grade_id')->where(array('c.id' => $id))->value('grade_name');
}

function getApartOrderNum($order_number)
{
    return M('porder')->where(array('apart_order_number' => $order_number))->count();
}

function getJmApiName($id)
{
    return M('jmapi')->where(array('id' => $id))->value('name');
}

function getJmApiParamName($id)
{
    return M('jmapi_param')->where(array('id' => $id))->value('desc');
}
