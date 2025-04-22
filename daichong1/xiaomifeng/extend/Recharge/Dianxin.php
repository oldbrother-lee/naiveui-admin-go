<?php


namespace Recharge;//命名空间

use app\common\model\Porder as PorderModel;//引用模块
use think\Log;//引用LOG
/**
 * 电费 http://ip/api/receiveOrder
 **/


class Dianxin
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

        $price = $param['param1'];//面值
        $key = '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCihNmIePYCxf82Feb0CIEzJOXqydekxK1fQfZp7XRZZBjf7ZZq
gpCjb3KumqfuqInGeOG5K4vFFgoXB0VrLnKMPtLXiKKiBkXBhCY9+DU6DPy3ts2y
dzJ6m92bRFKh8maBUXryCnIuHyEHMtcZ7hH58Mwi09l8AC+xqhFaQz0x6wIDAQAB
AoGAIOZJ7zGlg6w3XOiUJ2StWwAmNyCDMgzKmBUPYCQ8wHfd/T7oi0lBJITEL4qJ
Ymvl8DK6ZzTkh5JNmpnTOL5fW6gkftlfax6zCERmE7yHvyaTcUhbp/s/xxfMq7Rw
22kpumRl9zRF2+HSZdHqQNJ2wPNr2hM7Nvo6+7trnn5tnfkCQQDQ90UXjgfYGGTF
6FX7QUbq5iXvk7EteP1rHwuZbmGKu+m52Fp2OXICB1v6cYjf5bheJjuAJSwONEyb
UZsDWNydAkEAxxlH4nB2BHTQ5Y93wDgXDlswUWgJ6kWcJjuNCofxQJvM+16GA9kG
D+V65adyvy7pNo1UimwVVa9oaEaR4j8OJwJAJpJN5ZAo8IFoMIO3Qz6EWZ2LyRIo
9SzNEjXTzUlrpdETzmMaJ5Jo8ejr2GmWi0V6554FA51Y6XJL5auFgnOnnQJBAJag
BKdGJ7L1YXja0mEEzkSZLnPX/vBS23B9SxX1hMo5VJmziDXvAUwTc6e8x+3loqAX
yiay4G0juBxzjziYNa8CQAXDQ/JjySuW9oGQ6MJMYgbEKU1MH7S/cfzHEaIwUK0k
X7cHbUV9D5qjARaq7iOTGBEsFc9ZDNN3nOZtvmrWE4E=
-----END RSA PRIVATE KEY-----';
        $sign = $this->dian_sign($out_trade_num,$mobile,$price,$key);
        $data =[
            "token" =>$this->mchid,
            "out_trade_no" => $out_trade_num,
            "phone" => $mobile,
            "amount" => $price,
            'callback_url' => $this->notify,
            'sign' => $sign
        ];
        $url =$this->apiUrl. '?'.'token='.$data['token'].'&out_trade_no='.$data['out_trade_no'].'&phone='.$data['phone'].'&amount='.$data['amount'].'&callback_url='.$data['callback_url'].'&sign='.$data['sign'];
        Log::error("dianxin整合的参数".$url);
        $get = file_get_contents($url);
        //{"code":1,"msg":"成功","data":{"orderId":"a5deae18544042c78b44836a6f4ab9d6","out_trade_no":"1234","token":"90d64e771134b1cb12273b2d4791f76b","channel":"dxxe","phone":"15537288720","amount":50,"startDate":"2023-06-15 21:02:53","states":"待充值","callback_url":"baidu.com","lock_time":10}}
        //echo $url;

        $result = json_decode($get, true);
        if ($result['code'] == 1) {
            return rjson(0, $result['msg'], $result);
        } else {
            return rjson(1, $result['msg'], $result);
        }

//
//        $price = $param['param1'];//面值
//        $sign = $this->dian_sign($out_trade_num,$mobile,$price,$this->apikey);
//        $data =[
//            "token" => $this->mchid,
//            "out_trade_no" => $out_trade_num,
//            "phone" => $mobile,
//            "amount" => $price,
//            'callback_url' => $this->notify,
//            'sign' => $sign
//        ];
//
//
//        Log:: error ("Dianxin整合的参数".json_encode($data));
//        return $this->http_post($this->apiUrl, $data);
    }
    public  function dian_sign($out_trade_no, $phone, $amount,$key)
    {
        $data = $out_trade_no . $phone . $amount;

        Log:: error ("Dianxin整合的参数".json_encode($data));
        openssl_sign($data, $sign, $key, "SHA1");
        $sign =urlencode(base64_encode($sign));
        Log:: error ("Dianxin整合的参数".$sign);
        return $sign;
    }

//    public  function dian_sign($out_trade_no, $phone, $amount,$key)
//    {
//        $data = $out_trade_no . $phone . $amount;
//        Log:: error ("Dianxin整合的参数".json_encode($data));
//        openssl_sign($data, $sign, $key, "SHA1");
//        $sign = urlencode(base64_encode($sign));
//        return $sign;
//    }
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
        Log::error("Dianxin返回参数".$sContent);
        if (intval($aStatus["http_code"]) == 200) {
            $result = json_decode($sContent, true);
            if ($result['code'] == 1) {
                return rjson(0, $result['msg'], $result);
            } else {
                return rjson(1, $result['msg'], $result);
            }
        } else {

            Log::error("Dianxin整合的参数".json_encode($param));
            return rjson(500, '接口访问失败，http错误码测试AAA' . $aStatus["http_code"]);
        }
    }



    public function notify()
    {
        //{\"data\":{\"orderId\":\"ed8b0b2868b54cc6b8179c7cf8edb353\",\"out_trade_no\":\"CZH230615133991A00N1\",\"merchant_orderId\":null,\"tradeNO\":null,\"payStatus\":\"充值失败\",\"payTime\":null,\"mobile\":\"15537288720\",\"payAmount\":\"10.00\"},\"sign\":\"CJWaM5crIKa8BTLkti7grrLHQDYjHK0nlRDp9J7gvAFz9RNiSEEyf26ZADZFYa4mir5Ij70gmSPpvYVFWwj4908MGroPPiRElJ+j52zCbQ/qCM8F5n+UXwtNJXyTS8R+aQuXIbXoaCkUHdeeO5ODzjrpcC4FCLvT5Ys0ueBQfmo=\"}
        $str = file_get_contents("php://input");
        $data = json_decode($str, true);
        //回调
        Log::error("Dianxin接受参数".$str);

        if ($data['data']['payStatus'] == '充值成功') {
            PorderModel::rechargeSusApi('Dianxin', $data['data']['out_trade_no'], $data, '充值完成');
            echo "ok";

        } else {//失败回调
            PorderModel::rechargeFailApi('Dianxin', $data['data']['out_trade_no'], $data);
            echo "ok";
        }
        // }

    }
}