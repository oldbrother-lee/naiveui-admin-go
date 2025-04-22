<?php


namespace Recharge;

use app\common\model\Porder as PorderModel;
use app\api\controller\Notify;
use app\common\library\Createlog;
use app\common\library\Notification;
use app\common\library\PayWay;
use app\common\library\Rechargeapi;
use think\Exception;
use think\Model;
use Util\Ispzw;

/**
 *
 * 配置1：客户ID(需联系商务生成，纯数字ID)，配置2：秘钥，配置3：回调地址；配置4：提单地址(问渠道要)
 * 参数1：面值
 * http://xxx.com/api/addorder
 **/
class Dspay
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
        //http://47.96.187.2:10186/plat/api/old/queryorder
    /**
     * @param $order

    array(1) {
    [0] => array(10) {
    ["id"] => int(1)
    ["mobile"] => string(11) "13055909707"
    ["order_number"] => string(10) "CZH2210161"
    ["api_order_number"] => string(15) "CZH2210161A00N1"
    ["api_trade_num"] => NULL
    ["api_cur_param_id"] => int(1)
    ["api_cur_id"] => int(1)
    ["name"] => string(6) "顺赢"
    ["param1"] => string(5) "10023"
    ["param2"] => string(32) "cf97a21dcb274eed88ee2b48e6cbc234"
    }
    }
     */
    public function selfcheck($order){
        $data = [
            "szAgentId" => $order['param1'],
            "szOrderId" => $order['api_order_number'],
            "szFormat" =>'JSON',
        ];
        $sign_str = 'szAgentId='.$order['param1'].'&szOrderId='.$order['api_order_number'].'&szKey='. $order['param2'];
        $sign = md5(urldecode($sign_str));
        $data['szVerifyString'] =$sign;
        $res =  $this->http_post('http://47.96.187.2:10186/plat/api/old/queryorder',$data);
        if($res['errno']=='5011'){
            Createlog::porderLog($order['id'], "订单自检接口||返回:充值中-|".json_encode($res));
        }elseif($res['errno']=='5012'){
            Createlog::porderLog($order['id'], "订单自检接口||返回:充值成功-|".json_encode($res));

            PorderModel::rechargeSusApi('sh', $order['api_order_number'], $_POST,'充值成功',json_encode($res));

        }elseif($res['errno']=='5013'){

            Createlog::porderLog($order['id'], "订单自检接口||返回:充值失败-|".json_encode($res));
            PorderModel::rechargeFailApi('sh', $order['api_order_number'], json_encode($res));
        }else{

            Createlog::porderLog($order['id'], "订单自检接口||返回:未知-|".json_encode($res));
        }

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