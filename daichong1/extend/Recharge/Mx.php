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
class Mx
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

        Createlog::porderLog($out_trade_num,'开始请求订单:');
        $teltype = $this->get_teltype($isp);
        $time = date("Y-m-d H:i:s",time());
        $price = $param['param1'];

        $tel =  substr($mobile,0,7);

        $pho= M('phone')->where(['phone'=>$tel])->find();


       // Createlog::porderLog('12',$I5pvPB4);
        $data = [
            'amount'=>$price,
            'mch_id'=>$this->mchid,
            'mobile'=>$mobile,
            'times'=>time(),
            'trade_id'=>$out_trade_num,
            'type'=>1,
            'official'=>$teltype,
            'area'=>$pho['province'],//归属地 不要求准确
            
            
        ];
        //$sig = hash_hmac('sha256', $string, $secret)
        $string = $this->getSortParams($data);
        $data['sign'] =md5($string.'&key='.$this->apikey);
        Createlog::porderLog($param['logid'],'加密串:'.$string.'&key='.$this->apikey);
        Createlog::porderLog($param['logid'],'请求数据:'.json_encode($data));

        return $this->http_post($this->apiUrl,$data,$param['logid']);



    }

    /**
    2      * 参数排序
    3      *
    4      * @param array $param
    5      * @return string
    6      */
    public static function getSortParams($param = [])
    {
        unset($param['sign']);
        ksort($param);
        $signstr = '';
        if (is_array($param)) {
            foreach ($param as $key => $value) {
                if ($value == '') {
                    continue;
                }
                $signstr .= $key . '=' . $value . '&';
            }
            $signstr = rtrim($signstr, '&');
        }
        return $signstr;
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
            "mch_id" => $order['param1'],
            "trade_id" => $order['api_order_number'],
            "times" =>time(),
        ];

        $string = $this->getSortParams($data);
        $data['sign'] =md5($string.'&key='.$order['param2']);


        $ress =  $this->http_post('http://119.23.219.141:7111/api/Submit/orderQuery',$data,$order['id']);
        $res = json_decode($ress, true);



        if($res['status']=='66'){
            Createlog::porderLog($order['id'], "订单自检接口||返回:充值中-|".json_encode($res));
        }elseif($res['status']=='88'){
            Createlog::porderLog($order['id'], "订单自检接口||返回:充值成功-|".json_encode($res));

            PorderModel::rechargeSusApi('sh', $order['api_order_number'], $_POST,'充值成功',json_encode($res));

        }elseif($res['status']=='22'){

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



        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 90);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($param));
        curl_setopt($oCurl, CURLOPT_HEADER, 0);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, ["Content-type: application/json;charset=utf-8"]);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        $curl_err = curl_error($oCurl);
        curl_close($oCurl);
       // Createlog::porderLog($param['logid'],'请求数据:'.json_encode($data));
        if($logid>1){
            Createlog::porderLog($logid,'返回:'.$sContent);
        }

        if (intval($aStatus["http_code"]) == 200) {
            $result = json_decode($sContent, true);


          //  $I5pvPB4 = 'curl_content:'.$param['no'];
           // Createlog::porderLog($I5pvPB4,json_encode($sContent));


        //</pre>{"code":"200","res":{"result":"error","date":"1666772832633","string":"请激活商户"}}

            if($result['code']==1){
                if($result['msg']=='提交成功'){
                    return rjson(0, 100, $result['data']);
                }else{
                  //  return rjson(0, 100, $result['msg']);
                    return rjson(0, 100, $result['data']);
                }

            }else{
                return rjson(0, 100, $result);
            }




//            if (isset($result['nRtn']) && $result['nRtn'] == 0) {
//                return rjson(0, $result['szRtnCode'], $result);
//            } else {
//                return rjson(1, $result['szRtnCode'], $result);
//            }
        } else {
            return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"] . 'curl err:'.$curl_err);
        }
    }




    public function notify()
    {
        $I5pvPB4 = file_get_contents('php://input');
  Createlog::porderLog('Mx',$I5pvPB4);
        $I5pvPB4 = json_decode($I5pvPB4,true);

//【参数：{"amount":"200.00","msg":"success","orderid":"1536618426647027","proof":"0e92f2310f3fe47edcd1479369faa879","rechargeno":15665391000,"status":88,"times":1669090536,"trade_id":"CZH22112214881A00N1","sign":"b5f1dec3a0409bcad08c80a80201c1b4","voucher":"110103308082211221217010830494"}】

       if(!empty($I5pvPB4)){
           if($I5pvPB4['status']=='88'){


               //   $this->http_post('http://81.68.169.227/api.php/apinotify/taaa',$I5pvPB4);
               //充值成功,根据自身业务逻辑进行后续处理
               PorderModel::rechargeSusApi('Mx', $I5pvPB4['trade_id'], $_POST,'充值成功',$I5pvPB4['orderid']);
               echo "success";


           }else{
               //  dump($I5pvPB4);
               echo "success";
               PorderModel::rechargeFailApi('Mx', $I5pvPB4['trade_id'], $_POST);
//            echo "ok";
           }


       }








//        if ($state == 2) {
//            //充值成功,根据自身业务逻辑进行后续处理
//            PorderModel::rechargeSusApi('sh', I('szOrderId'), $_POST,'充值成功',I('szRtnMsg'));
//            echo "ok";
//        } else if ($state == 3) {
//            //充值失败,根据自身业务逻辑进行后续处理
//            PorderModel::rechargeFailApi('sh', I('szOrderId'), $_POST);
//            echo "ok";
//        }
    }
}