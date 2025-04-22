<?php


namespace Recharge;//命名空间

use app\common\model\Porder as PorderModel;//引用模块
use think\Log;//引用LOG
/**
 * 电费 http://ip/api/receiveOrder
 **/


class lanhfb
{
    private $mchid;//商户编号
    private $apikey;//私钥
    private $notify;//回调接口
    private $apiUrl;//提交接口

    public function __construct($option)//配置参数
    {
        $this->mchid = isset($option['param1']) ? $option['param1'] : '';
        $this->apikey = isset($option['param2']) ? $option['param2'] : '';
        $this->notify = isset($option['param3']) ? $option['param3'] : '';
        $this->apiUrl = isset($option['param4']) ? $option['param4'] : '';
    }

    /**
     * 提交充值号码充值
     */
    //Log::error("bcbsj整合的参数".$this->apiUrl);

    public function recharge($out_trade_num, $mobile, $param, $isp = '')//PARAM为套餐参数
    {



        if($isp == '移动'){
            $teltype = 1;
        }else if($isp == '联通'){
            $teltype = 3;
        }else if($isp == '电信'){
            $teltype = 2;
        }
        $charge_type=0;//0.慢充 1.快充

        $price = $param['param1'];//面值
        $area = '四川';
        $sign_str = $this->mchid . $out_trade_num . $mobile . $price . $teltype . $param['guishu_pro'].$charge_type.$this->notify . $this->apikey;
        $sign = md5(urldecode($sign_str));
        $data =[
            "partner_id" => $this->mchid,
            "partner_order_no" => $out_trade_num,
            "account" => $mobile,
            "amount" => $price,
            "type" => $param['param2'],
            "area"=> $param['guishu_pro'],
            "charge_type"=> $charge_type,
            'notify_url' => $this->notify,
            'sign' => $sign
        ];


        Log:: error ("lanhfb整合的参数".json_encode($data).$isp);
        return $this->http_post($this->apiUrl, $data);
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
        curl_setopt($oCurl, CURLOPT_URL, $url);//POST地址
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 90);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);//POST字符串
        curl_setopt($oCurl, CURLOPT_HEADER, 0);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            $result = json_decode($sContent, true);
            if ($result['code'] == 1) {
                return rjson(0, $result['msg'], $result);
            } else {
                return rjson(1, $result['msg'], $result);
            }
        } else {

            Log::error("lanhfb整合的参数".json_encode($param));
            return rjson(500, '接口访问失败，http错误码测试AAA' . $aStatus["http_code"]);
        }
    }



    public function notify()
    {
        //回调
        $charge_amount = intval(I('charge_amount'));
        $state = intval(I('charge_status'));
        if ($state == 2) {
            PorderModel::rechargeSusApi('lanhfb', I('partner_order_no'), $_POST, '充值完成');
            echo "success";
        }else if($state == 1){
            if ($charge_amount == 0 || $charge_amount == -1) {
                PorderModel::rechargeFailApi('lanhfb', I('partner_order_no'), $_POST);
            } else {
                PorderModel::rechargePartApi('lanhfb', I('partner_order_no'), $_POST, "部分充值：" . I('charge_amount'));
            }

            echo "success";
        } else {//失败回调
            PorderModel::rechargeFailApi('lanhfb', I('partner_order_no'), $_POST);
            echo "success";
        }
        // }

    }
}