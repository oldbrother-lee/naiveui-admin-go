<?php

namespace app\admin\controller;

use think\Cache;

class Qudan extends Admin
{

    public function member(){
        $list = M('qd_member')->where('status',1)->select();
        $this->assign('list',$list);
        return view();
    }
    public function add($id = 0){
        if(request()->isPost()){
            $arr = I('post.');
            $arr['date_add'] = date('Y-m-d H:i:s');
            if(I('post.id')){
                $data = M('qd_member')->where('id',$id)->update($arr);
            }else{
                $data = M('qd_member')->insert($arr);
            }
            if($data){
               return $this->success('新增成功');
            }else{
               return $this->error('新增失败');
            }
        }
        $info = M('qd_member')->where('id',$id)->find();
        $this->assign('info',$info);
        return view();
    }
     public function yjtb()
    {
        if(request()->isPost()){
            $arr = I('post.');
            $member = M('qd_member')->where('status',1)->select();
            foreach ($member as $key =>$value){
                    M('qd_member')->where('id',$value['id'])->update($arr);
            }
            return $this->success('一键同步成功');
        }
        return view();

    }
    public function deletes($id = 0){
        if(I('post.id')){
            $res = M('qd_member')->where('id',$id)->update(array('status'=>2));
            if($res){
                return $this->success('删除成功');
            }else{
                return $this->error('删除失败');
            }
        }
    }
    public function config(){
        $list = M('qd_config')->select();
        $this->assign('list',$list);
        return view();
    }
    public function addconfig($id = 0){
        if(request()->isPost()){
            $arr = I('post.');
            if(I('post.id')){
                $data = M('qd_config')->where('id',$id)->update($arr);
            }else{
                $data = M('qd_config')->insert($arr);
            }
            if($data){
                return $this->success('新增成功');
            }else{
                return $this->error('新增失败');
            }
        }
        $info = M('qd_config')->where('id',$id)->find();
        $this->assign('info',$info);
        return view();
    }
    public function delconfig($id = 0){
        $res = M('qd_config')->where('id',$id)->delete();
        if($res){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }
    public function plan(){
        $lists = M('qd_orders')->order('id desc')->paginate(20);
        $page = $lists->render();
        $list = $lists->all();
        $zdhq = Cache::get('zdhq');
        // echo($zdhq);exit();
        $this->assign('zdhq',$zdhq);
        foreach ($list as $k =>$value){
            $list[$k]['createTime'] = date('Y-m-d H:i:s', $list[$k]['createTime']);
            if($list[$k]['chargeTime']){
                $list[$k]['chargeTime'] = date('Y-m-d H:i:s', $list[$k]['chargeTime']);
            }
            if ($list[$k]['uploadTime']) {
                $list[$k]['uploadTime'] = date('Y-m-d H:i:s', $list[$k]['uploadTime']);
            }

        }
        $this->assign('page', $page);
        $this->assign('list',$list);
        return view();
    }
    public function zdhq(){
        $way = $this->request->get('way');
        // echo($way);exit();
        if($way == 1){
            Cache::set('zdhq',1);
        }elseif ($way == 2){
            Cache::set('zdhq',2);
             return $this->success('操作成功!');
        }
        if($way == 1){
            $member = M('qd_member')->where('status',1)->select();
            $config = M('qd_config')->find();
            $url = 'https://shop.task.mf178.cn/userapi/sgd/getOrder';
            $insert = [];
            foreach ($member as $key =>$value){
                $userid = $value['userid'];
                $appkey = $value['app_key'];
                $appsecret = $value['app_secret'];
                $params = array(
                    'vender_id'=>$config['vender_id']
                );
                $data = array(
                    'amount'=>$config['amount'],
                    'operator_id'=>$config['operator_id'],
                    'order_num'=>$config['order_num'],
                    'prov_code'=>$config['prov_code']
                );
                $params['data'] = json_encode($data);
                $params = $this->getsing($params,$appkey,$appsecret);
                $result = $this->curl($url,$params);
                $result = json_decode($result,true);
                // var_dump($result);
                $data = $result['data'];
                $code = $result['code'];
                if($code != 0 ){
                    $message = $result['message'];
                    return $this->success($message);
                }
                foreach ($data as $k =>$val){
                    $desc = explode('|',$val['target_desc']);
                    $insert[] = array(
                        'user'=>Cache::get('userid'),
                        'order_sn'=>$this->getordersn(),
                        'order_id'=>$val['user_order_id'],
                        'account'=>$val['target'],
                        'prov'=>$desc[1],
                        'operator'=>$desc[0],
                        'denom'=>$desc[2],
                        'settlePrice'=>$desc[2],
                        'createTime'=>$val['create_time'],
                        'status'=>1
                    );
                }

            }
            var_dump($insert);exit();
            $res = M('qd_orders')->insertAll($insert);
            if($res){
                return $this->success('操作成功!');
            }
        }
    }
    //取单的sing
    public function getsing($params = [],$app_key,$app_secret){
        if(empty($params) || empty($app_key) || empty($app_secret)){
            throw new \Exception('参数错误');
        }

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
     //生成本地自己的订单编号
    public function getordersn(){
        $order_sn = date('YmdHms').rand('000000','999999');
        $num = M('tuidan_orders')->where('order_sn',$order_sn)->count();
        if($num == 1){
            $this->getordersn();
        }else{
            return $order_sn;
        }
    }
     //获取订单之后开始给代理分发订单，代理处理订单数据
    public function paifa()
    {
        $list = M('qd_orders')->where('status',1)->select();
        foreach ($list as $key =>$value){
            $account = $value['account'];
            $order_sn = $value['order_sn'];
            $denom  = $value['denom'];
            $this->post_porder($account,$order_sn,$denom);
        }

    }
    public function post_porder($mobile, $out_trade_num, $denom)
    {
        $product = M('product')->where(['voucher_name' => $denom, 'isp'=>3,'added' => 1, 'is_del' => 0])->find();
        // var_dump($product);exit();
        $data = [
            "userid" => 3,
            "mobile" => $mobile,
            "out_trade_num" => $out_trade_num,
            "product_id" => $product['id'],
            "notify_url" => 'http://m.bnai.com',
        ];
        $data['sign'] = $this->sign($data);
        return $this->http_get('http://139.224.204.212:85/yrapi.php/index/recharge', $data);
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

        echo '下单返回：' . $sContent . PHP_EOL;

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
}