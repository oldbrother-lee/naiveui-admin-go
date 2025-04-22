<?php

namespace app\common\command;

use app\common\model\Porder as PorderModel;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class BackOrder extends Command
{
//    use DaichongsTrait;
    protected function configure()
    {
        $this->setName('BackOrder')->setDescription('取消支付');
    }

    protected function execute(Input $input, Output $output)
    {
        while(1){
            try{
                // echo("2121");
                //设置失败
                $order_list = M('porder')->whereIn('status', [7,8,6,4])->select();
                // var_dump($order_list);
                foreach ($order_list as $index => $item) {
                    // echo($item['out_trade_num']);
                    $order = M('daichong_orders')->where('yr_order_id', $item['out_trade_num'])->find();
                        if($order['is_post'] == 1){
                            continue;
                        }
                        // echo('2');
                        if($item['status']  == 4){
                        $order = M('daichong_orders')->where('yr_order_id', $item['out_trade_num'])->find();
                        M('daichong_orders')->where(['id' => $order['id']])->update(['is_post' => 1,'type'=>1]);
                        $this->setordertype($order, 9,'成功');
                        }else{
                        $order = M('daichong_orders')->where('yr_order_id', $item['out_trade_num'])->find();
                        M('daichong_orders')->where(['id' => $order['id']])->update(['is_post' => 1,'type'=>1]);
                        $this->setordertype($order, 8,'失败');
                        }
                        
                    
                }
                $redis = $this->getRedis();
                echo '处理重复单'.PHP_EOL;
                $order_ids = $redis->sMembers('out_time_orders');
                foreach ($order_ids as $index => $order_id) {
                    echo '执行退单操作'.$order_id.PHP_EOL;
                    for($i=10; $i>0; $i--){
                        $as=  PorderModel::applyCancelOrder2($order_id, "重试:超时系统自动申请(新)-后台自动化");
                    }
                    $redis->sRem('out_time_orders',$order_id);
                }
            }catch(\Exception $exception){
                echo $exception->getMessage().':'.$exception->getLine().':'.$exception->getFile();
            }
            sleep(5);
        }
    }
    public function setordertype($datas, $status,$msg='成功'){
        // var_dump($datas);
        // echo("3");
        $user =db('daichong_user')->where(['child'=>$datas['user']])->find();
        $orders = M('daichong_orders')->where('order_id',$datas['order_id'])->find();
        if($orders['way'] == 3){
            $userid = $user['child'];
            $appkey = $user['user'];
            $appsecret = $user['pwd'];
            $params = array(
                'orderNumber'=>$orders['order_id']
            );
            if($status==8){
                $params['status'] = '2';
                db('daichong_orders')->where(['order_id'=>$datas['order_id']])->update(['uploadTime'=>time(),'status'=>9,'type'=>1]);
            }else{
                db('daichong_orders')->where(['order_id'=>$datas['order_id']])->update(['uploadTime'=>time(),'status'=>6,'type'=>2]);
                $params['status'] = '1';
            }
            $sing = $this->get_xzxsing($params,$appkey,$userid);
            $url='https://api.xianzhuanxia.com';
            $post_url = $url.'/api/task/recharge/reported';
            $result = $this->xzx_post($post_url,$params,$sing);
        }else{
            echo("小蜜蜂回调");
            if($status==8){
                db('daichong_orders')->where(['order_id'=>$datas['order_id']])->update(['uploadTime'=>time(),'status'=>9,'type'=>1]);
            }else{

                db('daichong_orders')->where(['order_id'=>$datas['order_id']])->update(['uploadTime'=>time(),'status'=>6,'type'=>2]);
            }

            $jsonStr = '{"user_order_id":"' . $datas['order_id'] . '","status":"' . $status . '","rsp_info":"' . $msg . '"}';
            $urls = explode(",", $user['token']); // 运营商 地区 金额
            $urls[1] = 'https://shop.task.mf178.cn/userapi/sgd/updateStatus';
            // $urls[1] = 'https://test.shop.center.mf178.cn/userapi/sgd/updateStatus';
            $res = dc_action($urls[1], 'status', $user, $jsonStr);
        }
        
    }
    public function getRedis(){
        $redis = new \redis();
        $redis->connect('127.0.0.1', '6379');
        $redis->select(1);
        return $redis;
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