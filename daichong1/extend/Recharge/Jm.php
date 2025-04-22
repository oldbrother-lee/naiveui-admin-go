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
            "appId" => $this->mchid,
            "uuid" => $mobile,
            "outOrderId" => $out_trade_num,
            "price" => $price,
            "paytype" => $teltype,
            "itemId" =>$param['param2'],//台商品编号(向平台索取)。 全国类商品需填写商品编号，分省商品时为空字符串，为空时根据号码自动判断运营商。充流量时填写对应流量商品编号。
            "itemFace" => $price,
            'callbackUrl' => $this->notify,
            "timestamp" => $time,
            'isp'=>$teltype,
            'timeout'=>10,
        ];

        $src = $this->getSortParams($data);

        $data['sign']=md5($src.$this->apikey);



      return $this->http_post($this->apiUrl,$data);






    }


 
  /**
   * base64 解码
   * @param base64Code 待解码的base64 string
   * @return 解码后的byte[]
   * @throws Exception
   */
        public static function decrypt($data='需要解密的数据', $key='平台给的密钥') {
          $encrypted = base64_decode($data);
          return openssl_decrypt($encrypted, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
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
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $param);
        curl_setopt($oCurl, CURLOPT_HEADER, 0);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, ["ContentType:application/x-www-form-urlencoded;charset=utf-8"]);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        $curl_err = curl_error($oCurl);
        curl_close($oCurl);



            

        if (intval($aStatus["http_code"]) == 200) {


            $result = json_decode($sContent, true);
            if (isset($result['code']) && $result['code'] == 00) {
                return rjson(0, $result['msg'], $result);
            } else {
                return rjson(1, $result['msg'], $result);
            }



        } else {
            return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"] . 'curl err:'.$curl_err);
        }
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


    public function notify()
    {
        $state = intval(I());

        Createlog::porderLog('112',json_encode($state));
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