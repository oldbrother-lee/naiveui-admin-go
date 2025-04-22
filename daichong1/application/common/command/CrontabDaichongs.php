<?php

namespace app\common\command;

use app\common\library\Configapi;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Cache;

class CrontabDaichongs extends Command
{
    protected function configure()
    {
        $this->setName('CrontabDaichongs')->setDescription('5秒获取小蜜蜂订单');
    }
    protected function execute(Input $input, Output $output)
    {
        while (1) {
            C(Configapi::getconfig());
            if(Cache::get('is_getplan') == 1){
                 sleep(5);
                echo('开启了'.date('Y-m-d H:i:s')."<br/>");
                $this->getplan();
            }else{
                echo('关闭了'."<br/>");
            }
           
        }
    }
    public function getplan()
    {
        //先查询代充任务
        $work = M('daichong_user_work')->where('status',1)->select();
        // var_dump($work);exit();
        foreach ($work as $key =>$value){
            $user_id = $value['user_id'];
            $user =  M('daichong_user')->where('id',$user_id)->find();
            // var_dump($user);exit();
            $work[$key]['appkey'] = $user['user'];
            $work[$key]['appsecret'] = $user['pwd'];
            $work[$key]['userid'] = $user['child'];
            $work[$key]['vender_id'] = $user['zhuanqu'];
        }
        // var_dump($work);exit();
        $url = 'https://shop.task.mf178.cn/userapi/sgd/getOrder';
        $insert = [];
        foreach ($work as $key =>$value){

            $userid = $value['userid'];
            $appkey = $value['appkey'];
            $appsecret = $value['appsecret'];
            $params = array(
                'vender_id'=>$value['vender_id']
            );
            if($value['yunying'] == 1){
                $operator_id = '移动';
            }elseif ($value['yunying'] == 2){
                $operator_id = '联通';
            }elseif ($value['yunying'] == 3){
                $operator_id = '电信';
            }
            if($value['prov_select'] == ''){
                $prov_text="北京, 广东, 上海, 天津, 重庆, 辽宁, 江苏, 湖北, 四川, 陕西, 河北, 山西, 河南, 吉林, 黑龙江, 内蒙古, 山东, 安徽, 浙江, 福建, 湖南, 广西, 江西, 贵州, 云南, 西藏, 海南, 甘肃, 宁夏, 青海, 新疆";
            }else{
                $prov_text = $value['prov_select'];
            }
            $data = array(
                'amount'=>$value['money'],
                'operator_id'=>$operator_id,
                'order_num'=>$value['count'],
                'prov_code'=>$prov_text
            );
            $params['data'] = json_encode($data);
            // var_dump($params);
            $params = $this->getsing($params,$appkey,$appsecret);
            // var_dump($params);
            $result = $this->curl($url,$params);
            $result = json_decode($result,true);
            // var_dump($result);
            $code = $result['code'];
            if($code != 0){
                $message = $result['message'];
                echo ('请求小蜜蜂返回信息'.$message);
                // exit();
            }
            // var_dump($result);exit();
            // exit();
            $data = $result['data'];
            foreach ($data as $k =>$val){
                $desc = explode('|',$val['target_desc']);
                $insert[] = array(
                    'user'=>$userid,
                    'yr_order_id'=>$this->getordersn(),
                    'order_id'=>$val['user_order_id'],
                    'account'=>$val['target'],
                    'prov'=>$desc[1],
                    'yunying'=>$desc[0],
                    'denom'=>$desc[2],
                    'settlePrice'=>$desc[2],
                    'createTime'=>$val['create_time'],
                    'status'=>5,
                    'settleStatus'=>0
                );
            }

        }
        $res = M('daichong_orders')->insertAll($insert);
        if($res){
            if(Cache::get('is_pf') == 1){
                $this->paifa();
            }
        //   echo('订单获取成功已派发!');
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
    //取单的sing
    public function getsing($params = [],$app_key,$app_secret){
        // if(empty($params) || empty($app_key) || empty($app_secret)){
        //     throw new \Exception('参数错误');
        // }

        $params = array_merge($params, ['app_key' => $app_key, 'timestamp' => time()]);

        ksort($params);

        $buff = '';
        foreach ($params as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . $v;
            }
        }

        $buff .= $app_secret;

        $params['sign'] = md5($buff);

        return $params;
    }
    public function setpf(){
        $val = $this->request->get('val');
        if(!Cache::get('is_pf')){
            Cache::set('is_pf',$val);
        }else{
            Cache::set('is_pf',$val);
        }
        return $this->success('操作成功!');
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
            $this->post_porder($account,$order_sn,$denom,$isp);

        }

    }
    public function post_porder($mobile, $out_trade_num, $denom,$isp)
    {
        // echo($isp);exit();
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
        return $this->http_get('http://58.87.96.100/yrapi.php/index/recharge', $data);
    }
    //签名
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
     //访问
    // add by Lif at 2023-10-10 
    public function curl($url, $data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode == 404) {
            $response = '404 Page Not Found';
        }
        curl_close($curl);
        return $response;
    }
    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
	{
		return djson(0, $msg, array('url' => $url, 'wait' => $wait, 'data' => $data))->send();
	}
	protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
	{
		return djson(1, $msg, array('url' => $url, 'wait' => $wait, 'data' => $data))->send();
	}

}