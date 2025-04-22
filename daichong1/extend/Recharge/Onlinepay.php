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
class Onlinepay
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
        $time = date("YmdHis",time());
        $price = $param['param1'];


        $data = [
            "userid" => $this->mchid,
            "mobile" => $mobile,
            "sporderid" => $out_trade_num,
            "price" => $price,
            "paytype" => $teltype,
            "productid" =>$param['param2'],//台商品编号(向平台索取)。 全国类商品需填写商品编号，分省商品时为空字符串，为空时根据号码自动判断运营商。充流量时填写对应流量商品编号。
            "num" => 1,
            'back_url' => $this->notify,
            "spordertime" => $time,
        ];



        $data['sign']=MD5('userid='.$data['userid'].'&productid='.$data['productid'].'&price='.$data['price'].'&num='.$data['num'].'&mobile='.$data['mobile'].'&spordertime='.$data['spordertime'].'&sporderid='.$data['sporderid'].'&key='.$this->apikey);



      return $this->http_post($this->apiUrl,$data,$param['logid']);






    }



    private function get_teltype($str)
    {
        //运营商类型，联通:lt 移动:yd 电信:dx。
        switch ($str) {
            case '移动':
                return 'yd';
            case '联通':
                return 'lt';
            case '电信':
                return 'dx';
            default:
                return -1;
        }
    }
    public function selfcheck($order){
        $data = [
            "userid" => $order['param1'],
            "sporderid" => $order['api_order_number'],
        ];


        $data['sign']=MD5('userid='.$data['userid'].'&sporderid='.$data['sporderid'].'&key='.$order['param2']);



        $res =  $this->http_post('http://47.106.64.208:9086/searchpay.do',$data);
        /**
         * object(SimpleXMLElement)#5 (11) {
        ["orderid"] => string(14) "XS090428000003"
        ["productid"] => string(4) "3312"
        ["num"] => string(1) "1"
        ["ordercash"] => string(4) "98.5"
        ["productname"] => string(25) " 广东移动100元直充"
        ["sporderid"] => string(13) "2009042800001"
        ["mobile"] => string(11) "13590101510"
        ["merchantsubmittime"] => object(SimpleXMLElement)#6 (0) {
        }
        ["resultno"] => string(1) "0"
        ["remark1"] => string(9) "凭证号"
        ["fundbalance"] => string(6) "100000"
        }
         */



        if($res->resultno ==1){
            Createlog::porderLog($order['id'], "订单自检接口||返回:充值成功-|".json_encode($res));

            PorderModel::rechargeSusApi('Onlinepay', $order['api_order_number'], $_POST,'充值成功',json_encode($res));



        }else{

            Createlog::porderLog($order['id'], "订单自检接口||返回:充值失败-|".json_encode($res));
            PorderModel::rechargeFailApi('Onlinepay', $order['api_order_number'], json_encode($res));
        }

    }

    /**
     * get请求
     */
    private function http_post($url, $param,$logid=null)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1);
        }
//        if (is_string($param)) {
//            $strPOST = $param;
//        } else {
//            $strPOST = http_build_query($param);
//        }
        $param = http_build_query($param);
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 90);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $param);
        curl_setopt($oCurl, CURLOPT_HEADER, 0);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, ["ContentType:application/x-www-form-urlencoded;charset=utf-8"]);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        $curl_err = curl_error($oCurl);
        curl_close($oCurl);



                //load the xml string using simplexml
                $xml = simplexml_load_string($sContent);

        if($logid>1){
            Createlog::porderLog($logid,'返回:'.json_encode($xml));
        }
        dump($xml);die;
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
        $state = $_POST;
        //"orderid=WHT22120118070084396&sporderid=CZH22120131949A00N1&userid=10002943&merchantsubmittime=20221201181745&resultno=1&parvalue=100&remark1=22120118072889719637&payno=22120118072889719637&fundbalance=-189&sign=E9067B867CBF408EFCD7443FA83F068F"
        //{"orderid":"WHT22120118070084396","sporderid":"CZH22120131949A00N1","userid":"10002943","merchantsubmittime":"20221201181745","resultno":"1","parvalue":"100","remark1":"22120118072889719637","payno":"22120118072889719637","fundbalance":"-189","sign":"E9067B867CBF408EFCD7443FA83F068F"}


        Createlog::porderLog('Onlinepay',json_encode(file_get_contents('php://input')));
        Createlog::porderLog('Onlinepay',json_encode($state));
        $os = json_decode($state,true);
        if($os){

            if ($os['resultno'] == 1) {
                //充值成功,根据自身业务逻辑进行后续处理
                PorderModel::rechargeSusApi('Onlinepay', I('sporderid'), $_POST,'充值成功',$_POST);
                echo "ok";
            } else if ($os == 3) {
                //充值失败,根据自身业务逻辑进行后续处理
                PorderModel::rechargeFailApi('Onlinepay', I('sporderid'), $_POST);
                echo "ok";
            }
        }else{

            Createlog::porderLog('Onlinepay',json_encode($os));
        }


    }
}