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
class Flz
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

        // Createlog::porderLog($out_trade_num,'开始请求订单:');
        // $teltype = $this->get_teltype($isp);
        $time = date("Y-m-d H:i:s",time());
        $price = $param['param1'];

        // $tel =  substr($mobile,0,7);

        //$pho= M('phone')->where(['phone'=>$tel])->find();


        // Createlog::porderLog('12',$I5pvPB4);
        $data = [
            //'amount'=>$price,
            'app_key'=>$this->mchid,
            'method'=>'fulu.order.direct.add',
            //  'mobile'=>$mobile,
            // 'times'=>$time,
            'version'=>'2.0',
            //'trade_id'=>$out_trade_num,
            'format'=>'json',
            'charset'=>'utf-8',
            'sign_type'=>'md5',
            'timestamp'=>$time,
            'app_auth_token'=>'',//授权码
        ];
        $biz_content = [
            'product_id'=>$param['param2'],
            'charge_account'=>$mobile,
            'buy_num'=>1,
            'customer_order_no'=>$out_trade_num
        ];
        $data['biz_content'] = json_encode($biz_content);

        //$sig = hash_hmac('sha256', $string, $secret)
        //  $string = $this->getSortParams($data);
        $data['sign'] =$this->getSign($data,$this->apikey,$param['logid']);
        //  Createlog::porderLog($param['logid'],'加密串:'.$string.'&key='.$this->apikey);
        Createlog::porderLog($param['logid'],'请求数据:'.json_encode($data));

        return $this->http_post($this->apiUrl,$data,$param['logid']);



    }

    /**
     * php签名方法
     */
    public function getSign($Parameters,$key,$logid=null)
    {
        //签名步骤一：把字典json序列化
        $json = json_encode( $Parameters, 320 );
        //签名步骤二：转化为数组
        $jsonArr = $this->mb_str_split( $json );
        //签名步骤三：排序
        sort( $jsonArr );
        //签名步骤四：转化为字符串
        $string = implode( '', $jsonArr );
        //签名步骤五：在string后加入secret
        $string = $string .$key;

        if($logid>1){
            Createlog::porderLog($logid,'加密串原版:'.$string);
        }

        //签名步骤六：MD5加密
        $result_ = strtolower( md5( $string ) );
        return $result_;
    }
    /**
     * 可将字符串中中文拆分成字符数组
     */
    public  function mb_str_split($str){
        return preg_split('/(?<!^)(?!$)/u', $str );
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
    //暂时不接
    public function selfcheck($order){
        $data = [
            "app_key" => $order['param1'],

            "method" =>'fulu.order.info.get',
            'timestamp'=>date('Y-m-d H:i:s',time()),
            'version'=>'2.0',
            'format'=>'json',
            'charset'=>'utf-8',
            'sign_type'=>'md5',
        ];

        $biz_content = [

            'customer_order_no'=>$order['api_order_number']
        ];
        $data['biz_content'] = json_encode($biz_content);

        //$sig = hash_hmac('sha256', $string, $secret)
        //  $string = $this->getSortParams($data);
        $data['sign'] =$this->getSign($data,$order['param2'],$order['id']);
        //  Createlog::porderLog($param['logid'],'加密串:'.$string.'&key='.$this->apikey);
        Createlog::porderLog($order['id'],'订单自检请求数据:'.json_encode($data));



        $ress =  $this->http_post('https://openapi.fulu.com/api/getway',$data,$order['id']);
        // $res = json_decode($ress, true);
        //result

        $res = $ress['data']['result'];

        $res = json_decode($res, true);

        /**
         * Array
        (
        [order_id] => 22113019210922951925
        [product_id] => 18221081
        [product_name] => 联通话费-全国-50元-直充-【回调5-10分钟，60以上成功率】
        [charge_account] => 18661218763
        [customer_order_no] => CZH22113028602A00N1
        [create_time] => 2022-11-30 19:25:53
        [buy_num] => 1
        [order_price] => 46.3
        [order_state] => success
        [finish_time] => 2022-11-30 20:09:00
        [area] =>
        [server] =>
        [type] =>
        [cards] =>
        [order_type] => 1
        [operator_serial_number] => asix110103308052211302008220757616
        )

         */

        // $res = json_decode($ress, true);







        if($res['order_state']=='processing'){
            Createlog::porderLog($order['id'], "订单自检接口||返回:充值中-|".json_encode($res));
        }elseif($res['order_state']=='success'){
            Createlog::porderLog($order['id'], "订单自检接口||返回:充值成功-|".json_encode($res));

            PorderModel::rechargeSusApi('Fl', $order['api_order_number'], $_POST,'充值成功',json_encode($res));

        }elseif($res['order_state']=='failed'){

            Createlog::porderLog($order['id'], "订单自检接口||返回:充值失败-|".json_encode($res));
            PorderModel::rechargeFailApi('Fl', $order['api_order_number'], json_encode($res));
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

            if($result['code']==0){
                if($result['message']=='接口调用成功'){
                    return rjson(0, 100, $result);
                }else{
                    //  return rjson(0, 100, $result['msg']);
                    return rjson(0, 100, $result);
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
        Createlog::porderLog('Fl',$I5pvPB4);
        $I5pvPB4 = json_decode($I5pvPB4,true);

//{"order_id":"22113039409742401508","charge_finish_time":"2022-11-30 15:29:30","customer_order_no":"CZH22113027827A00N1","order_status":"success","recharge_description":"充值成功","product_id":"17098075","price":"27.9000","buy_num":"1","operator_serial_number":"asix110103308072211301528590280381","sign":"e4ef188d8e078e16364c848489bb9160"}
        /**
         * {
        "order_id":"22113039409742401508",
        "charge_finish_time":"2022-11-30 15:29:30",
        "customer_order_no":"CZH22113027827A00N1",
        "order_status":"success",
        "recharge_description":"充值成功",
        "product_id":"17098075",
        "price":"27.9000",
        "buy_num":"1",
        "operator_serial_number":"asix110103308072211301528590280381",
        "sign":"e4ef188d8e078e16364c848489bb9160"
        }\
         * //{"order_id":"22113021285757992053","charge_finish_time":"2022-11-30 21:07:25","customer_order_no":"CZH22113028946A00N1","order_status":"success","recharge_description":"充值成功","product_id":"18221081","price":"46.3000","buy_num":"1","operator_serial_number":"asix110103308152211302103570299495","sign":"38f6f97f946b53d2ac20684bbcf75bbb"}
         */
        if($I5pvPB4){
            if($I5pvPB4['order_status']=='success'){


                //   $this->http_post('http://81.68.169.227/api.php/apinotify/taaa',$I5pvPB4);
                //充值成功,根据自身业务逻辑进行后续处理
                PorderModel::rechargeSusApi('Fl', $I5pvPB4['customer_order_no'], $I5pvPB4,'充值成功',$I5pvPB4['operator_serial_number']);
                echo "success";


            }elseif($I5pvPB4['order_status']=='failed'){
                //  dump($I5pvPB4);
                echo "success";
                PorderModel::rechargeFailApi('Fl', $I5pvPB4['customer_order_no'], $I5pvPB4);
//            echo "ok";
            }else{

                //   PorderModel::rechargeFailApi('Fl', $I5pvPB4['customer_order_no'], $I5pvPB4);
            }
//{"order_id":"22113024595794711936","charge_finish_time":"2022-11-30 20:14:15","customer_order_no":"CZH22113028634A00N1","order_status":"success","recharge_description":"充值成功","product_id":"18221081","price":"46.3000","buy_num":"1","operator_serial_number":"asix110103308202211302013270830661","sign":"90f1941940b0be9b245886d634d2ce58"}

        }else{


            Createlog::porderLog('Fl_c',$I5pvPB4);
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