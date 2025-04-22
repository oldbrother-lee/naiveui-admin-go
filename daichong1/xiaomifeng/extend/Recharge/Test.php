<?php


namespace Recharge;

use app\common\model\Porder as PorderModel;

/**
 *
 * 配置1：客户ID(需联系商务生成，纯数字ID)，配置2：秘钥，配置3：回调地址；配置4：提单地址(问渠道要)
 * 参数1：面值
 * http://xxx.com/api/addorder
 **/
use think\Log;

/**
 * 猿人接口
 * yrapi.php/index/recharge
 **/
class Yuanren
{
    private $mchid;//商户编号
    private $apikey;
    private $notify;
    private $apiUrl;//话费充值接口

    public function __construct($option)
    {
        $this->mchid = isset($option['param1']) ? $option['param1'] : '';
        $this->apikey = isset($option['param2']) ? $option['param2'] : '';
        $this->notify = isset($option['param3']) ? $option['param3'] : '';
        $this->apiUrl = isset($option['param4']) ? $option['param4'] : '';
    }

    /**
     * 提交充值号码充值
     */
    public function recharge($out_trade_num, $mobile, $param, $isp = '')
    {


        return rjson(500, '接口访问失败，http错误码---1111');



        $teltype = $this->get_teltype($isp);
        $time = date("Y-m-d H:i:s",time());
        $price = $param['param1'];

        $data = [
            "szAgentId" => $this->mchid,
            "szPhoneNum" => $mobile,
            "szOrderId" => $out_trade_num,
            "nMoney" => $price,
            "nSortType" => $teltype,
            "nProductClass" => 1,
            "nProductType" => 1,
            "szProductId" => $param['param2'],
            'szNotifyUrl' => $this->notify,
            "szTimeStamp" => $time,
            "szFormat" =>'JSON',
        ];
        $sign_str = 'szAgentId='.$this->mchid.'&szOrderId='.$out_trade_num.'&szPhoneNum='.$mobile.'&nMoney='.$price.'&nSortType='.$teltype.'&nProductClass=1&nProductType=1&szTimeStamp='.$time.'&szKey='. $this->apikey;
        $sign = md5(urldecode($sign_str));
        $data['szVerifyString'] =$sign;
        return $this->http_post($this->apiUrl,$data);
    }


    private function get_teltype($str)
    {
        switch ($str) {
            case '移动':
                return 1;
            case '联通':
                return 2;
            case '电信':
                return 3;
            default:
                return -1;
        }
    }

    /**
     * get请求
     */
    private function http_post($url, $param)
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
            $result = json_decode($sContent, true);
            if (isset($result['nRtn']) && $result['nRtn'] == 0) {
                return rjson(0, $result['szRtnCode'], $result);
            } else {
                return rjson(1, $result['szRtnCode'], $result);
            }
        } else {
            return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"] . 'curl err:'.$curl_err);
        }
    }




    public function notify()
    {
        $state = intval(I('nFlag'));
        if ($state == 2) {
            //充值成功,根据自身业务逻辑进行后续处理
            PorderModel::rechargeSusApi('sh', I('szOrderId'), $_POST,'充值成功',I('szRtnMsg'));
            echo "ok";
        } else if ($state == 3) {
            //充值失败,根据自身业务逻辑进行后续处理
            PorderModel::rechargeFailApi('sh', I('szOrderId'), $_POST);
            echo "ok";
        }
    }
}