<?php


namespace Recharge;

use think\Log;
use think\response\Json;

/**
 * 捷立讯接口
 * https://www.showdoc.com.cn/aql666666/7837530452757903
 **/
class Abin
{
    private $userid;
    private $apikey;
    private $notify;
    private $apiurl;//充值接口

    public function __construct()
    {
        $this->userid = 10002723;
        $this->apikey = 'KC6mkt75piS7kjZ8ZX2BxTQXXJ5SP6Fm';
        $this->notify = '';
        $this->apiurl = 'http://8.217.103.177:9086/';
    }

    /**
     * 下单地址
     */
    public function recharge($out_trade_num, $price,$mobile, $isp = '')
    {
        switch ($isp){
            case '联通':
                $isp = 'lt';
                break;
            case '移动':
                $isp = 'yd';
                break;
            case '电信':
                $isp = 'dx';
        }
        $data = [
            'userid' => $this->userid,
            'productid' => $out_trade_num,
            'price' => $price,
            'num' => 1,
            'mobile' => $mobile,
            'spordertime' => date('YmdHis'),
            'sporderid' => 'abin'.$out_trade_num.rand(1000, 9999)
        ];
        $data['sign'] = $this->sign($data);
        $data['back_url'] = 'http://115.126.57.143/api.php/Abin/notify';
        $data['paytype'] = $isp;
        return $this->http_get($this->apiurl . 'onlinepay.do', $data);
    }

    /**
     *查询订单接口
     * @param $out_trade_nums 平台订单号
     * @return array|Json
     */
    public function check($out_trade_nums)
    {
        $data = [
            "userid" => $this->userid,
            "sporderid" => $out_trade_nums
        ];
        $data['sign'] = $this->sign($data);

        return $this->http_get($this->apiurl . 'searchpay.do', $data);
    }

    //余额查询
    public function balance()
    {
        $data = [
            'userid' => $this->userid
        ];
        $data['sign'] = $this->sign($data);
        return $this->http_get($this->apiurl.'searchbalance.do', $data);
    }
    //签名
    public function sign($data)
    {
        ksort($data);
        $sign_str = http_build_query($data) . '&key=' . $this->apikey;
        return strtoupper(md5($sign_str));
    }

    /**
     * get请求
     */
    private function http_get($url, $param)
    {
        $oCurl = curl_init();
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $strPOST = http_build_query($param);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 90);
        curl_setopt($oCurl, CURLOPT_HEADER, 0);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, ["ContentType:application/x-www-form-urlencoded;charset=utf-8"]);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            $xml = simplexml_load_string($sContent);
            $json = json_encode($xml);
            $result = json_decode($json, true);
            if ($result['resultno'] == 1) {
                return rjson(0, '成功', $result);
            } else {
                return rjson(1, '失败', $result);
            }
        } else {
            return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"]);
        }
    }

}