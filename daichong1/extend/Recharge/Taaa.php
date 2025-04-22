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
class Taaa
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

        $tel =  substr($mobile,0,7);

        $pho= M('phone')->where(['phone'=>$tel])->find();

        $I5pvPB4 = $mobile.'|'.$out_trade_num.'|'.$pho['province'];
        Createlog::porderLog('12',$I5pvPB4);
        $data = [
            'money'=>$price,
            'id'=>$this->mchid,
            'phone_number'=>$mobile,
            'address'=>$pho['province'].$isp,
            'no'=>$out_trade_num,
        ];
        //$sig = hash_hmac('sha256', $string, $secret)
        $string = $this->getSortParams($data);
        $data['sign'] =strtoupper(hash_hmac('sha256',$string,$this->apikey));


        return $this->http_post($this->apiUrl,$data);



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
        if (intval($aStatus["http_code"]) == 200) {
            $result = json_decode($sContent, true);


            $I5pvPB4 = 'curl_content:'.$param['no'];
            Createlog::porderLog($I5pvPB4,json_encode($sContent));


        //</pre>{"code":"200","res":{"result":"error","date":"1666772832633","string":"请激活商户"}}

            if($result['code']==200){
                if($result['result']=='success'){
                    return rjson(1, 100, $result['string']);
                }else{
                    return rjson(0, 100, $result['string']);

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


        $I5pvPB4 = I();
        Createlog::porderLog('12',json_encode($I5pvPB4));

        if($I5pvPB4['code']=='200'){
            if($I5pvPB4['res']['result']='success'){

             //   $this->http_post('http://81.68.169.227/api.php/apinotify/taaa',$I5pvPB4);
                //充值成功,根据自身业务逻辑进行后续处理
            PorderModel::rechargeSusApi('sh', I('szOrderId'), $_POST,'充值成功',I('szRtnMsg'));
            echo "ok";
            }else{
                PorderModel::rechargeFailApi('sh', I('szOrderId'), $_POST);
//            echo "ok";
            }

        }else{
          //  dump($I5pvPB4);
            PorderModel::rechargeFailApi('sh', I('szOrderId'), $_POST);
//            echo "ok";
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