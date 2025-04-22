<?php

namespace app\common\command;

use app\common\library\Configapi;
use think\Cache;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Xzx extends Command
{
    protected function configure()
    {
        $this->setName('Xzx')->setDescription('5秒获取闲赚侠订单');
    }
    protected function execute(Input $input, Output $output)
    {
        while (1) {
            C(Configapi::getconfig());
             sleep(5);
            if(Cache::get('is_xzx') == 1){
                echo('开启了'.date('Y-m-d H:i:s'));
                $this->getplan();
            }else{
                echo('关闭了'.date('Y-m-d H:i:s'));
            }
           
        }
    }
   public function getplan()
    {
        //先查询代充任务
        $work = M('daichong_user_work')->where('status', 1)->select();
        // var_dump($work);exit();
        foreach ($work as $key => $value) {
            $user_id = $value['user_id'];
            $user = M('daichong_user')->where('id', $user_id)->find();
            // var_dump($user);exit();
            if($user['way'] != 2){
                 continue;
            }
            $work[$key]['appkey'] = $user['user'];
            $work[$key]['appsecret'] = $user['pwd'];
            $work[$key]['userid'] = $user['child'];
            $work[$key]['vender_id'] = $user['zhuanqu'];
            $work[$key]['way'] = $user['way'];
        }
        // var_dump($work);exit();
        $insert = [];
        foreach ($work as $key =>$value) {
            echo("开始循环获取");
            
            if($value['way'] == 2){
                echo('进来了');
                $userid = $value['userid'];
                $appkey = $value['appkey'];
                $appsecret = $value['appsecret'];
                $params = array(
                    'channelId'=>2
                );
                if($value['yunying'] == 1){ //移动
                    $params['productIds'] = '6';
                }elseif ($value['yunying'] == 2){ //联通
                    $params['productIds'] = '8';
                }elseif ($value['yunying'] == 3){ //电信
                    $params['productIds'] = '7';
                }
                $params['faceValues'] = $value['money'];
                $params['minSettleAmounts'] = $value['minSettleAmounts'];
                // var_dump($params);exit();
                $orders =  $this->get_token($params);
                foreach ($orders as $k =>$val){
                    $num = M('daichong_orders')->where('order_id',$val['orderNumber'])->count();
                    if($num == 1){
                        continue;
                    }
                    $insert[] = array(
                      'user'=>$userid,
                        'yr_order_id'=>$this->getordersn(),
                        'order_id'=>$val['orderNumber'],
                        'account'=>$val['accountNum'],
                        'prov'=>$val['accountLocation'],
                        'yunying'=>str_replace('中国','',$val['productName']),
                        'denom'=>$val['faceValue'],
                        'settlePrice'=>$val['settlementAmount'],
                        'createTime'=>time(),
                        'status'=>5,
                        'settleStatus'=>0,
                        'way'=>3
                    );
                    $res = M('daichong_orders')->insertAll($insert);
                }

            }
            
        }
        
        if($res){
            if(Cache::get('is_pf') == 1){
                echo("进入派发逻辑");
                $this->paifa();
            }
            // return $this->success('操作成功!');
        }

    }
    public function paifa()
    {
        $list = M('daichong_orders')->where('status',5)->select();
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
            echo("开始派发");
            $this->post_porder($account,$order_sn,$denom,$isp);

        }

    }
    public function post_porder($mobile, $out_trade_num, $denom,$isp)
    {
        echo('222派发');
        $product = M('product')->where(['voucher_name' => $denom, 'isp'=>$isp,'added' => 1, 'is_del' => 0])->find();
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
    public  function get_token($data)
    {
        $token_data = M('token')->find();
        // var_dump($token_data);
        // var_dump($token_data);exit();
        if(!$token_data){
            $token = $this->xzx_submit($data);
        }else{

            if(time() - $token_data['date_time'] <300 ){

                $token = $token_data['token'];
            }else{

                $token = $this->xzx_submit($data);
            }
        }
        // echo("获取到的token".$token);
        //开始查询申请做单是否匹配订单
        $url='https://api.xianzhuanxia.com';
        $url1 = 'https://cusapitest.xianzhuanxia.com';
        $post_url = $url.'/api/task/recharge/query';
        $params = [
            'token'=>$token
        ];

        $appid = '565846A';
        $appsecret = 'uy/GDbT3poOcN0BKSkGbAvr8O77iOv/s24W7HJLN79w=';
        $appkey = '8855404e6df64892a9bde62d8e26d231';
//        $appid = '558346';
//        $appkey = '9a95033d04624caaa1c378bea2689346'; //签名密钥
        $sing = $this->get_xzxsing($params,$appkey,$appid);
        $result = $this->xzx_post($post_url,$params,$sing);
        $result = json_decode($result,true);
        echo("获取的订单数据");
        // var_dump($result);
        if($result){
            $code = $result['code'];
            if($code == 0){
                $datas = $result['result'];
                $orders = $datas['orders'];
                return $orders;
            }else{
                echo ('暂未匹配到订单');
            }
        }

    }
    public function xzx_submit($data)
    {
        $url='https://api.xianzhuanxia.com';
        $url1 = 'https://cusapitest.xianzhuanxia.com';
        $post_url = $url.'/api/task/recharge/submit';

        // var_dump($params);
        $appid = '565846A';
        $appsecret = 'uy/GDbT3poOcN0BKSkGbAvr8O77iOv/s24W7HJLN79w=';
        $appkey = '8855404e6df64892a9bde62d8e26d231';
//        $appid = '558346';
//        $appkey = '9a95033d04624caaa1c378bea2689346'; //签名密钥
        $sing = $this->get_xzxsing($data,$appkey,$appid);
        // var_dump($data);
        $result = $this->xzx_post($post_url,$data,$sing);
        // var_dump($result);
        $datas = json_decode($result,true);
        if($datas){
            $code = $datas['code'];
            $msg  = $datas['msg'];
            if($code == 0){
                $result = $datas['result'];
                $token = $result['token'];
                $token_data = Db::name('token')->find();
                if(!$token_data){
                    Db::name('token')->insert(array('token'=>$token,'date_time'=>time()));
                }else{
                    Db::name('token')->where('id',$token_data['id'])->update(array('token'=>$token,'date_time'=>time()));
                }
                return $token;
            }
        }

//        var_dump($datas);exit();
    }
    public function get_xzxsing($params,$signatureKey,$userNumber)
    {
        $sortedParams = [];
        foreach ($params as $key => $value) {
            if (!empty($value)) {
                // 特别处理 double 类型的值
                if (is_float($value)) {
                    $value = rtrim(rtrim(sprintf('%.2f', $value), '0'), '.');
                }
                $sortedParams[$key] = $value;
            }
        }
        if(!empty($sortedParams)){
            ksort($sortedParams);
        }


// 拼接成待加密字符串
        $originalString = '';
        foreach ($sortedParams as $key => $value) {
            $originalString .= $key . '=' . $value;
        }

// 第二步：添加 queryTime 和 key
        // $queryTime = '1730774310838'; // 当前系统毫秒数
        $queryTime = round(microtime(true) * 1000);
        $originalString .= "queryTime=$queryTime" . "key=$signatureKey";
//        echo($originalString."<br/>");
// 第三步：MD5 加密并转为小写
        $md5Hash = strtolower(md5($originalString));

// 第四步：生成 Auth_Token 并进行 Base64 编码
        $md5Hash.=','.$userNumber.','.$queryTime;
//        echo($md5Hash."<br/>");
        $authToken = base64_encode($md5Hash);
//        echo($authToken);
        return $authToken;
    }
    public function xzx_post($url,$data,$sing)
    {
        // 初始化 cURL 会话
        $ch = curl_init();


// 设置 cURL 选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        if($data){
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// 设置头部参数
        $headers = [
            'Content-Type: application/json;charset:utf-8;',
            'Auth_Token: '.$sing
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// 执行请求
        $response = curl_exec($ch);

// 检查是否有错误发生
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            // 处理响应
            return $response;
        }

// 关闭 cURL 会话
        curl_close($ch);

    }

}