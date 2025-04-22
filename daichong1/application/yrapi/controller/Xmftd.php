<?php

namespace app\yrapi\controller;

use think\Log;
use app\admin\Traits\DaichongsTrait;
class Xmftd
{
    public function index(){
        $jsonData = file_get_contents("php://input");
        $logFile = 'td.log';
        file_put_contents($logFile, $jsonData . PHP_EOL, FILE_APPEND);
        $data = json_decode($jsonData,true);
        $ins = array();
        $appkey = $data['app_key'];
        //查询本平台对应的账号信息
        $user = M('daichong_user')->where('user',$appkey)->find();
        if($user){
            $ins['user'] = $user['child'];
        }else{
            $ins['user'] = '';
        }
        $ins['order_id'] = $data['user_order_id'];
        $ins['account'] = $data['target'];
        $ins['denom'] = $data['user_payment'];
        $ins['settlePrice'] = $data['user_payment'];
        $ins['createTime'] = time();
        $ins['status'] = 5;
        $ins['settleStatus'] = 0;
        $ins['yr_order_id'] = $this->getordersn();
        $shuju = $data['datas'];
        $ins['yunying'] = $shuju['operator_id'];
        $ins['way'] = 2;
        $num = M('daichong_orders')->where('order_id',$data['user_order_id'])->count();
        if($num ==1){
            echo  json_encode(array('code'=>'SUCCESS'));
        }else{
             $res = M('daichong_orders')->insertGetId($ins);
             if($res){
                echo  json_encode(array('code'=>'SUCCESS'));
                $this->paifa($res);
             }else{
                 echo json_encode(array('code'=>'FAIL','message'=>'商品未上架'));
             }
        }
       
    }
    public function paifa($res)
    {
        $list = M('daichong_orders')->where('status',5)->where('id',$res)->select();
        foreach ($list as $key =>$value){
            $account = $value['account'];
            $order_sn = $value['yr_order_id'];
            $denom  = $value['denom'];
            // header('Content-Type: application/json; charset=utf-8');
            if($value['yunying'] == '移动'){
                $isp =1;
            }elseif($value['yunying'] == '联通'){
                $isp =3;
            }elseif($value['yunying'] == '电信'){
                $isp =2;
            }
            $this->post_porder($account,$order_sn,$denom,$isp);
            // var_dump($res);exit();
            // $result = json_decode($res,true);
            // if($result['errno'] == 1){
            //     DaichongsTrait::setordertype($value, 8, '失败');
            // }
        }

    }
    public function post_porder($mobile, $out_trade_num, $denom,$isp)
    {
        // echo($isp);exit();
        $product = M('product')->where(['price' => $denom, 'isp'=>$isp,'added' => 1, 'is_del' => 0])->find();
        // var_dump($product);exit();
        $data = [
            "userid" => 3,
            "mobile" => $mobile,
            "out_trade_num" => $out_trade_num,
            "product_id" => $product['id'],
            "notify_url" => 'http://m.bnai.com',
        ];
        $data['sign'] = $this->sign($data);
        return $this->http_get('http://124.222.223.198/yrapi.php/index/recharge', $data);
    }
    public function sign($data)
    {
        ksort($data);
        $sign_str = http_build_query($data) . '&apikey=EojFDKLbOPJty1B8IX9iUQnrCTSml0ZR';
        $sign_str = urldecode($sign_str);
        return strtoupper(md5($sign_str));
    }
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

        echo  $sContent . PHP_EOL;

        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            $result = json_decode($sContent, true);
            if ($result['errno'] == 0) {
                // return rjson(0, $result['errmsg'], $result['data']);
            } else {
                // return rjson(1, $result['errmsg'], $result['data']);
            }
        } else {
            // return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"]);
        }
    }
    public function getordersn(){
        $order_sn = date('YmdHms').rand('000000','999999');
        $num = M('daichong_orders')->where('yr_order_id',$order_sn)->count();
        if($num == 1){
            $this->getordersn();
        }else{
            return $order_sn;
        }
    }
    public function getyunying($val)
    {
        if($val == '移动'){
            return 1;
        }else if($val == '电信'){
            return 3;
        }else if($val == '联通'){
            return 2;
        }
    }
     public function cha()
    {
        $jsonData = file_get_contents("php://input");
        $logFile = 'cd.log';
        file_put_contents($logFile, $jsonData . PHP_EOL, FILE_APPEND);
        $data = json_decode($jsonData,true);
        $user_order_id = $data['user_order_id'];
        $order = M('daichong_orders')->where('order_id',$user_order_id)->find();
        $order_number = $order['yr_order_id'];
        $proder = M('porder')->where('out_trade_num',$order_number)->find();
        if(!$proder){
            $out =array(
                'code'=>1,
                'message'=>'订单不存在',
                'data'=>array()
            );
            echo json_encode($out);exit();
        }
        if($proder['status'] == 4){ //充值成功
            $out = array(
                'code'=>0,
                'message'=>'',
                'data'=>array(
                    'status'=>2,
                    'rsp_info'=>'充值成功',
                    'rsp_time'=>time()
                )
            );
        }elseif ($proder['status'] == 6 || $proder['status'] == 7 || $proder['status'] == 8){
            $out = array(
                'code'=>0,
                'message'=>'',
                'data'=>array(
                    'status'=>3,
                    'rsp_info'=>'充值失败',
                    'rsp_time'=>time()
                )
            );
        }
        echo json_encode($out);exit();
    }
}