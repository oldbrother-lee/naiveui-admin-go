<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use app\admin\Traits\DaichongsTrait;
use app\common\library\Configapi;
use think\Cache;
use think\Db;
class Daichongs extends Admin
{
    use DaichongsTrait;
    private $user = '123';
    private $pwd = '';


    /**
     * 中控首页
     *    S('bane',1);
     */
    public function mian()
    {     // dump(Cache::get('daichong'));

        $user = get_dc_user(2);

        $this->assign('user', $user);
        $map = [];
        if (I('key')) {
            $map['id|account|order_id|yr_order_id'] = array('like', '%' . I('key') . '%');
        }


        $lists = M('daichong_orders')->where($map)->order('id desc')->paginate(20);
        $page = $lists->render();
        $list = $lists->all();


        foreach ($list as $k => $v) {
            $a_time = strtotime($v['chargeTime']);
            $b_time = time();
            if (empty($user['te'])) {
                $user['te'] = 0;
            }
            $u_time = $user['te'] * 60;
            $a = $b_time - $u_time;

            if ($a < $a_time) {
                $list[$k]['is_cs'] = 1;
            } else {
                if ($v['type'] < 1) {
                    $list[$k]['is_cs'] = 2;
                } else {
                    $list[$k]['is_cs'] = 1;
                }
            }
            if ($list[$k]['is_cs'] == 1) {
                if ($v['type'] == 2) {
                    $list[$k]['is_cs'] = 3;
                }
            }
            $list[$k]['createTime'] = date('Y-m-d H:i:s', $list[$k]['createTime']);
            $list[$k]['chargeTime'] = date('Y-m-d H:i:s', $list[$k]['chargeTime']);
            //     $list[$k]['uploadTime'] = date('Y-m-d H:i:s',$list[$k]['uploadTime']);
            if ($list[$k]['uploadTime']) {
                $list[$k]['uploadTime'] = date('Y-m-d H:i:s', $list[$k]['uploadTime']);
            }
            if($v['way'] == 1){
                $list[$k]['way'] ='取单';
            }else if($v['way'] == 2){
                $list[$k]['way'] = '推单';
            }else{
                $list[$k]['way'] = '';
            }
        }

        //获取地区
        $prov = Cache::get('daichongs_prov', null);
        $this->assign('daichongs_prov', $prov ?: []);

        $this->assign('page', $page);
        $this->assign('list', $list);
        if(!Cache::get('is_pf')){
            $this->assign('is_pf',0);
        }else{
            $this->assign('is_pf',Cache::get('is_pf'));
        }
         if(!Cache::get('is_xzx')){
            $this->assign('is_xzx',0);
        }else{
            $this->assign('is_xzx',Cache::get('is_xzx'));
        }
        if(!Cache::get('is_getplan')){
            $this->assign('is_getplan',0);
        }else{
            $this->assign('is_getplan',Cache::get('is_getplan'));
        }
        return view();
    }

//自动获取全平台的订单信息
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
                $this->error($message);
                exit();
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
                echo("1");
                $this->paifa();
            }
            return $this->success('操作成功!');
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
     public function setxzx(){
        $val = $this->request->get('val');
        if(!Cache::get('is_xzx')){
            Cache::set('is_xzx',$val);
        }else{
            Cache::set('is_xzx',$val);
        }
        return $this->success('操作成功!');
    }
    public function setgetplan(){
        $val = $this->request->get('val');
        Cache::set('is_getplan',$val);
        return $this->success('操作成功!');
    }
    public function paifa()
    {
        $list = M('daichong_orders')->where('status',5)->select();
        foreach ($list as $key =>$value){
            $account = $value['account'];
            $order_sn = $value['yr_order_id'];
            $denom  = $value['denom'];
            header('Content-Type: application/json; charset=utf-8');
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
        return $this->http_get('http://106.55.134.68/yrapi.php/index/recharge', $data);
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
    public function zidonghua()
    {
        //增加用户批量登陆
        // $this->userlogin();
        //$this->set_order();
        // $this->set_orders();
        $this->get_order(8);
        // $this->set_order();
    }

    protected function userlogin()
    {
        $userlist = Cache::get('daichong');
        if (empty($userlist['user'])) {
            $this->error('用户不存在！');
        }

        foreach ($userlist['user'] as $k => $v) {


            $data = [
                'username' => $v['user'],
                'password' => $v['pwd'],
            ];

            $res = dc_action('', 'login', $v['zhuanqu'], $data);
            mylog('用户登陆', json_encode($v));

            $urtoken = json_decode($res, true);
            mylog('登陆返回', json_encode($urtoken));
            if ($urtoken['ret'] > 0) {
                unset($userlist['user'][$k]);

                //                $msg = [
                //                    'code' => 0,
                //                    'access_token' => $res,
                //                    'msg' =>$urtoken['msg']
                //                ];
                $userlist['nouser'][$k] = $v['user'];
                Cache::set('daichong', $userlist);
            } else {
                $user_dc['token'] = $urtoken['data'];
                M('daichong_user')->where(['id' => $v['id']])->update($user_dc);

                $userlist['user'][$k] = db('daichong_user')->where(['id' => $v['id']])->find();
                $userlist['touser'][$k] = $v['id'];
                Cache::set('daichong', $userlist);
            }
        }
    }

    public function huoquemoney($n, $money)
    {

        if ($n < 1) {
            $money['moneyn'] = $n + 1;
            if (empty($money['money'][$money['moneyn']]['denom'])) {
                return false;
            }
            Cache::set('daichong', $money);
        } else {
            //否则根据循环计算出应该排序出的金额
            if (empty($money['money'][$n + 1]['denom'])) {
                $money['moneyn'] = 0;
            } else {
                $money['moneyn'] = $n + 1;
            }

            Cache::set('daichong', $money);
        }


        return $money['money'][$money['moneyn']]['denom'];
    }

    /*
        protected function get_order()
        {
    
            $daichonginfo = Cache::get('daichong');
    
            if ($daichonginfo['type'] == 0) {
                return false;
            }
    
            foreach ($daichonginfo['user'] as $k => $v) {
    
    
                $daichonginfo = null;
                $daichonginfo = Cache::get('daichong');
                if (empty($daichonginfo['moneyn'])) {
                    $money = $this->huoquemoney(0, $daichonginfo);
                } else {
                    $money = $this->huoquemoney($daichonginfo['moneyn'], $daichonginfo);
                }
                $yunying = get_dc_user(2);
    
                $data = [
                    'amount' => $money,
                    'operator' => $daichonginfo['type'],
                ];
                mylog('用户取单', json_encode($data));
    
                $this->userorderadd($v, $data, $daichonginfo['type']);
            }
        }
    
        private function userorderadd($user, $data, $yunying = 1)
        {
    
    
            $res = dc_action($user['token'], 'get', $user['zhuanqu'], $data);
            mylog('用户取单', json_encode([$user['token'], 'get', $user['zhuanqu'], $data]));
    
            $resd = json_decode($res, true);
            mylog('用户取单返回', json_encode($resd));
    
            if ($resd['ret'] == 0) {
                if (!empty($resd['data'])) {
                    $list['user'] = $user['id'];
                    $list['order_id'] = $resd['data']['id'];
                    $list['account'] = $resd['data']['mobile'];
                    $list['denom'] = $resd['data']['amount']; //金额
                    $list['createTime'] = time();
                    $list['yunying'] = $yunying;
                    $list['chargeTime'] = $resd['data']['timeout']; //超时时间
                    $list['status'] = 5;
    
                    $get = M('daichong_orders')->where(['order_id' => $resd['data']['id']])->find();
                    if ($get) {
                        M('daichong_orders')->where(['order_id' => $resd['data']['id']])->update($list);
                    } else {
    
                        M('daichong_orders')->insert($list);
                        mylog('添加订单', json_encode($list));
                        $this->sert($list, 1);
                    }
                } else {
                    echo '无新的数据';
                    return false;
                }
            }
        }
        
        */





    /**
     * 设置订单成功
     * https://id4.cgtest.bolext.com/cg/api/TaskPub/Partner/UploadCert
     */
    public function setstaus()
    {
        $id = I('id');
        if ($id > 0) {
            
            $order = db('daichong_orders')->where(['order_id' => $id])->find();
            if($order['way'] == 3){
                $user =db('daichong_user')->where(['child'=>$order['user']])->find();
               
                $userid = $user['child'];
            $appkey = $user['user'];
            $appsecret = $user['pwd'];
            $params = array(
                'orderNumber'=>$order['order_id']
            );
                $params['status'] = '1';
                db('daichong_orders')->where(['order_id'=>$order['order_id']])->update(['uploadTime'=>time(),'status'=>6,'type'=>1]);
          
            $sing = $this->get_xzxsing($params,$appkey,$userid);
            $url='https://api.xianzhuanxia.com';
            $post_url = $url.'/api/task/recharge/reported';
            $result = $this->xzx_post($post_url,$params,$sing);
            // var_dump($result);exit();
            return $this->success();
            }else{
            db('daichong_orders')->where(['order_id' => $id])->update(['uploadTime' => time(), 'status' => 6, 'type' => 2]);
            //$this->setordertype($order, 9, '成功');
            //$this->sert($id, 259);


            $aaa= $this->setordertype($order, 9, '成功');
            return $this->success($aaa);  //調試測試使用
            }

        }


        return $this->success('操作成功!');
    }

    public function set_order()
    {
        //先处理提交订单
        echo '订单出来开始工作:';
        $order = M('daichong_orders')->where(['is_post' => 0])->order('id desc')->limit(100)->select();

        echo '准备提交订单' . count($order) . PHP_EOL;


        foreach ($order as $k => $v) {
            $porder = null;
            if (empty($v['yr_order_id'])) { //未提交订单状态 向后台提交订单
                $porder = M('porder')->where(['out_trade_num' => $v['order_id']])->find();
                echo '存在订单号' . $v['order_id'] . PHP_EOL;

                if ($porder) {

                    $p = M('daichong_orders')->where(['order_id' => $v['order_id']])->update(['yr_order_id' => $porder['order_number'], 'is_post' => 1]);
                    if ($p) {
                        echo '更新成功' . $v['order_id'] . PHP_EOL;
                    } else {
                        echo '更新失败' . $v['order_id'] . PHP_EOL;
                    }
                } else {
                    // mylog('提交订单'.$v['order_id'],$v['account'].$v['denom']);
                    echo '提交订单' . $v['order_id'] . PHP_EOL;
                    $this->post_porder($v['account'], $v['order_id'], $v['denom']);
                }
                echo 1;
            } else {
                echo 2;
                //查到订单的状态 就按照订单状态上报信息
                $porder = M('porder')->where(['order_number' => $v['yr_order_id']])->find();

                if ($porder) {
                    if ($porder['status'] == 4) {
                        //执行成功操作
                        M('daichong_orders')->where(['id' => $v['id']])->update(['is_post' => 1, 'type' => 2]);
                        // $list = M('daichong_orders')->where(['id' => $v['id']])->find();
                        mylog('上报充值成功信息', $v['order_id'] . '==5');
                        // $message='http://.jpg';
                        $this->setordertype($v, 1); //result 订单状态  1充值成功 2充值中 3充值失败
                        //echo "上报订单状态成功！" . $v['order_id'] . PHP_EOL;


                    } elseif ($porder['status'] == 6) {
                        //执行失败操作
                        mylog('上报充值失败信息', $v['order_id'] . '==2');
                        M('daichong_orders')->where(['id' => $v['id']])->update(['is_post' => 1, 'type' => 1]);
                        $this->setordertype($v, 3, '失败');
                        // echo "上报订单状态失败！" . $v['order_id'] . PHP_EOL;


                    }
                }
            }
        }
        return false;
    }

    public function moneyset()
    {
        $tb = I('money');
        $user = get_dc_user(2);
        if ($user[$tb] == 1) {
            $up = 0;
        } else {
            $up = 1;
        }


        M('daichong_user')->where(['id' => $user['id']])->update([$tb => $up]);

        $msg = [
            'code' => 1,

        ];

        return ($msg);
    }


    /**
     * 设置订单失败
     */
    public function setstaus_w()
    {
        $id = I('id');


        if ($id > 0) {
            $order = db('daichong_orders')->where(['order_id' => $id])->find();
            if($order['way'] == 3){
                $user =db('daichong_user')->where(['child'=>$order['user']])->find();
               
                $userid = $user['child'];
            $appkey = $user['user'];
            $appsecret = $user['pwd'];
            $params = array(
                'orderNumber'=>$order['order_id']
            );
                $params['status'] = '2';
                db('daichong_orders')->where(['order_id'=>$order['order_id']])->update(['uploadTime'=>time(),'status'=>9,'type'=>1]);
          
            $sing = $this->get_xzxsing($params,$appkey,$userid);
            $url='https://api.xianzhuanxia.com';
            $post_url = $url.'/api/task/recharge/reported';
            $result = $this->xzx_post($post_url,$params,$sing);
            return $this->success();
            }else{
                 db('daichong_orders')->where(['order_id' => $id])->update(['uploadTime' => time(), 'status' => 9, 'type' => 1]);
            

            $this->setordertype($order, 8, '失败');
            }
           
            //return $this->success($aaa);  //調試測試使用
        }

        return $this->success('操作成功!');
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

    /**
     * @return mixed
     */
    public function deletes()
    {

        $id = I('id');
        if ($id > 0) {

            M('daichong_orders')->where(['id' => $id])->delete();
            Db::execute("TRUNCATE TABLE dyr_daichong_orders");
        }
        return $this->success('操作成功!');
    }

    public function deall()
    {


        M('daichong_orders')->where('id', '>', 0)->delete();
        Db::execute("TRUNCATE TABLE dyr_daichong_orders");
        $msg = [
            'code' => 1,
        ];

        return ($msg);
    }


    /**
     *
     * 停止系统接单
     * //停止接单 关闭心跳  设置
     *774640825492591826
     */

    public function stop()
    {
        $daichonginfo = Cache::get('daichong');
        $daichonginfo['type'] = 0;
        Cache::set('daichong', $daichonginfo);
        $user = get_dc_user(2);
        db('daichong_user')->where(['id' => $user['id']])->update(['type' => 0]);
        // $xturl = df_get('https://www.xitupt.com/cg/api/TaskPub/Partner/Stop', '', $header);

        $msg = [
            'code' => 1,

        ];

        return ($msg);
    }

    ///xiaomifeng interface sign function for getorders
    // add by Lif at 2023-10-10  sign
    public function get_xiaomifeng_sign($params, $appsecret)
    {
        //对参数按key进行排序
        ksort($params);

        //连接所有参数名与参数值
        $buff = '';
        foreach ($params as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . $v;
            }
        }
        //连接加密串
        $buff .= $appsecret;
        //使用md5计算参数串哈希值
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

    /**
     * 开始系统接单
     *
     */
    public function start()
    {
        // add by Lif at 2023-10-9


        //地区
        $prov_text = input('prov');
        if($prov_text==""){
            $prov_text="北京, 广东, 上海, 天津, 重庆, 辽宁, 江苏, 湖北, 四川, 陕西, 河北, 山西, 河南, 吉林, 黑龙江, 内蒙古, 山东, 安徽, 浙江, 福建, 湖南, 广西, 江西, 贵州, 云南, 西藏, 海南, 甘肃, 宁夏, 青海, 新疆";
        }

        //每次订单数
        $countText = input('count');
        if($countText==0){
            $countText=1;
        }

        $daichongInfo=[
            'prov_text'=>$prov_text,
            'count_text'=>$countText,
            'auto_get_order'=>1
        ];

        $denomInfos = [];
        $user = get_dc_user(2);
        $denomInfos['user'] = db('daichong_user')->where(['is_lex' => 2])->select();
        $denomInfos['type'] = input('yunying');
        $denomInfos['te'] = 10;
        $denomInfos['prov_text'] = $prov_text;
        $demomInfos['count_text']=$countText;
        $te = 10;
        if(empty($te)){
            $te = 0;
        }
        if ($user['money10'] == 1) {
            $denomInfos['money'][] = ['denom' => 10];
        }
        if ($user['money20'] == 1) {
            $denomInfos['money'][] = ['denom' => 20];
        }
        if ($user['money30'] == 1) {
            $denomInfos['money'][] = ['denom' => 30];
        }
        if ($user['money50'] == 1) {
            $denomInfos['money'][] = ['denom' => 50];
        }
        if ($user['money100'] == 1) {
            $denomInfos['money'][] = ['denom' => 100];
        }
        if ($user['money200'] == 1) {
            $denomInfos['money'][] = ['denom' => 200];
        }
        if ($user['money300'] == 1) {
            $denomInfos['money'][] = ['denom' => 300];
        }
        if ($user['money500'] == 1) {
            $denomInfos['money'][] = ['denom' => 500];
        }
        Cache::set('daichong', $denomInfos);
        Cache::set('daichongInfo',$daichongInfo);
        db('daichong_user')->where(['id' => $user['id']])->update(['type'=>1,'yunying'=> $denomInfos['type'],'count'=>$countText ,'te'=>$te]);
//         $aaa = $this->get_order();
        $msg = [
            'code' => 1,
            'msg' => '开始接单成功！' . $aaa,
            'Start' => true,
            'set' => true,
        ];
        return ($msg);
    }


    public function add()
    {
        if (request()->isPost()) {
            $arr = $_POST;
            // $arr['te'] = 10;
            $arr['token'] = 'https://shop.task.mf178.cn/userapi/sgd/getOrder,https://shop.task.mf178.cn/userapi/sgd/updateStatus';
            if (I('id')) {
                $data = M('daichong_user')->where(array('id' => I('id')))->setField($arr);
                if ($data) {
                    return $this->success('保存成功');
                } else {
                    return $this->error('编辑失败');
                }
            } else {
                $aid = M('daichong_user')->insertGetId($arr);
                if ($aid) {
                    return $this->success('新增成功');
                } else {
                    return $this->error('新增失败');
                }
            }
        } else {
            $info = M('daichong_user')->where(array('id' => I('id')))->find();
            if(!$info){
                $info = array(
                    'id' => 0,
                    'user' => '',
                    'pwd' => '',
                    'child' => '',
                    'zhuanqu' => '',
                    'type'=>'',
                    'way'=>''
                );
            }
            $this->assign('info', $info);
            return view();
        }
    }

    public function userlist()
    {

        $usrlis = Cache::get('daichong');
        if (empty($usrlis['nouser'])) {
            $usrlis['nouser'] = '无';
        }
        $list = M('daichong_user')->where(array('is_lex' => 2))->field(['dyr_daichong_user.*'])->paginate(20);
        $page = $list->render();
        $lists = $list->all();;
        foreach ($lists as $index => $item) {
            $lists[$index]['work'] = '修改任务';
            if($item['type'] == 1){
                $lists[$index]['type'] = '取单';
            }else if($item['type']  == 2){
                $lists[$index]['type'] = '推单';
            }else{
                $lists[$index]['type'] = '';
            }
        }
        $this->assign('page', $page);
        $this->assign('_listn', $usrlis['nouser']);
        $this->assign('_list', $lists);
        return view();
    }

    public function deletes_param()
    {
        $param = M('daichong_user')->where(array('id' => I('id')))->find();
        if (!$param) {
            return $this->error('未找到用户');
        }

        if (M('daichong_user')->where(array('id' => $param['id']))->delete()) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

    public function update_prov()
    {
        $data = I('prov_data/a');
        if (is_array($data) && !empty($data)) {
            if (empty($data[0])) {
                Cache::set('daichongs_prov', null, 432000);
            } else {
                Cache::set('daichongs_prov', $data, 432000);
            }
        } else {
            Cache::set('daichongs_prov', null, 432000);
        }

        return $this->success('操作成功!', null, [
            'data' => Cache::get('daichongs_prov'),
            'bool' => empty($data)
        ]);
    }
    public function delwork(){
        if(I('id')) {
            $res = M('daichong_user_work')->where('id',I('id'))->delete();
            return $this->success('删除成功');
        }
    }
    public function addwork(){
        if(request()->isPost()){
            $param = $_POST;
            // if(!key_exists('prov_money', $param)){
            //     return $this->error('请选择钱数!');
            // }
            if(!key_exists('count', $param) || !$param['count']){
                return $this->error('请填写查询数量!');
            }
            if(!key_exists('prov_select', $param)){
                $param['prov_select'] = [];
            }
            $data = array(
                'money' => $param['money'],
                'yunying' => $param['yunying'],
                'prov_select' => implode(',', $param['prov_select']),
                'count' => $param['count'],
                'status' => $param['status'],
                'te' => $param['te'],
                'minSettleAmounts'=>$param['minSettleAmounts']
            );
            // var_dump($data);exit();
            if($param['id'] && $param['id'] !=0){
                $resp = M('daichong_user_work')->where('id',$param['id'])->update($data);
            }else{
                $data['user_id'] = $param['user_id'];
                $resp = M('daichong_user_work')->insert($data);
            }



            if($data['status']){
                $denomInfos['user'] = db('daichong_user')->where(['is_lex' => 2])->select();
                $denomInfos['type'] = $param['yunying'];
                $denomInfos['te'] = $param['te'];
                $denomInfos['prov_text'] = implode(',', $param['prov_select']);
                $demomInfos['count_text']= $param['count'];
                $te = $param['te'];
                
                if(empty($te)){
                    $te = 0;
                }
                $money = $param['money'];
                $this->startGetOrder($param['id']);
            }else{
                $daichonginfo = Cache::get('daichong:'.$param['id']);
                $daichonginfo['type'] = 0;
                Cache::set('daichong:'.$param['id'], $daichonginfo);
                db('daichong_user')->where(['id' => $param['id']])->update(['type' => 0]);
            }
            return $this->success('操作成功!',url('daichongs/userlist'),['url' => 'daichongs/userlist']);
        }else{
            // echo("2121");
            if(I('id')) {
                $work = M('daichong_user_work')->where(array('id' => I('id')))->find();
                // var_dump($work);exit();
                if ($work) {
                    $this->assign('daichongs_prov', explode(',', $work['prov_select']));
                    $work['money'] = $work['money'];
                    $this->assign('info', $work);
                }else{
                    $this->assign('daichongs_prov', []);
                    $this->assign('info', ['id' => 0, 'count' => 0,'money' => '', 'prov_select' => '', 'user_id' => I('id'), 'status' => 1, 'yunying' => 1, 'te' => 25]);
                }
            }else{
                // echo("2121");
                $this->assign('daichongs_prov', []);
                $this->assign('info', ['id' => 0, 'count' => 0,'money' => '', 'prov_select' => '', 'user_id' => I('userid'), 'status' => 1, 'yunying' => 1, 'te' => 25,'minSettleAmounts'=>'']);
                // echo("444");
            }
//            $prov = Cache::get('daichongs_prov', null);
//            $this->assign('daichongs_prov', $prov ?: []);
            return view();
        }
    }
    public function alladdwork(){
        if(request()->isPost()){
            $param = $_POST;
            if(!key_exists('prov_money', $param)){
                return $this->error('请选择钱数!');
            }
            if(!key_exists('count', $param) || !$param['count']){
                return $this->error('请填写查询数量!');
            }
            if(!key_exists('prov_select', $param)){
                $param['prov_select'] = [];
            }
            $data = array(
                'money' => implode(',', $param['prov_money']),
                'yunying' => $param['yunying'],
                'prov_select' => implode(',', $param['prov_select']),
                'count' => $param['count'],
                'status' => $param['status'],
                'te' => $param['te'],
            );
            $res_work = M('daichong_user_work')->select();
            // if(M('daichong_user_work')->where(array('user_id' => $param['id']))->find()){
            $resp = M('daichong_user_work')->where("1=1")->update($data);
            // }else{
            // $data['user_id'] = $param['id'];
            // $resp = M('daichong_user_work')->insert($data);
            // }
            if($data['status']){
                $denomInfos['user'] = db('daichong_user')->where(['is_lex' => 2])->select();
                $denomInfos['type'] = $param['yunying'];
                $denomInfos['te'] = $param['te'];
                $denomInfos['prov_text'] = implode(',', $param['prov_select']);
                $demomInfos['count_text']= $param['count'];
                $te = $param['te'];
                if(empty($te)){
                    $te = 0;
                }
                $money = $param['prov_money'];
                if (in_array(10, $money)) {
                    $denomInfos['money'][] = ['denom' => 10];
                }
                if (in_array(20, $money)) {
                    $denomInfos['money'][] = ['denom' => 20];
                }
                if (in_array(30, $money)) {
                    $denomInfos['money'][] = ['denom' => 30];
                }
                if (in_array(50, $money)) {
                    $denomInfos['money'][] = ['denom' => 50];
                }
                if (in_array(100, $money)) {
                    $denomInfos['money'][] = ['denom' => 100];
                }
                if (in_array(200, $money)) {
                    $denomInfos['money'][] = ['denom' => 200];
                }
                if (in_array(300, $money)) {
                    $denomInfos['money'][] = ['denom' => 300];
                }
                if (in_array(500, $money)) {
                    $denomInfos['money'][] = ['denom' => 500];
                }
                Cache::set('daichong', $denomInfos);
                foreach ($res_work as $k=>$v){
                    $this->startGetOrder($v['user_id']);
                }
            }else{
                foreach ($res_work as $k=>$v){
                    $daichonginfo = Cache::get('daichong:'.$v['user_id']);
                    $daichonginfo['type'] = 0;
                    Cache::set('daichong:'.$v['user_id'], $daichonginfo);
                    db('daichong_user')->where(['id' => $v['user_id']])->update(['type' => 0]);
                }
            }
            return $this->success('操作成功!',url('daichongs/userlist'),['url' => 'daichongs/userlist']);
        }else{
            if(I('id')) {
                $work = M('daichong_user_work')->where(array('user_id' => I('id')))->find();
                if ($work) {
                    $this->assign('daichongs_prov', explode(',', $work['prov_select']));
                    $work['money'] = explode(',', $work['money']);
                    $this->assign('info', $work);
                }else{
                    $this->assign('daichongs_prov', []);
                    $this->assign('info', ['id' => 0, 'count' => 0,'money' => [], 'prov_select' => '', 'user_id' => I('id'), 'status' => 1, 'yunying' => 1, 'te' => 25]);
                }
            }else{
                $this->assign('daichongs_prov', []);
                $this->assign('info', ['id' => 0, 'count' => 0,'money' => [], 'prov_select' => '', 'user_id' => I('id'), 'status' => 1, 'yunying' => 1, 'te' => 25]);
            }
//            $prov = Cache::get('daichongs_prov', null);
//            $this->assign('daichongs_prov', $prov ?: []);
            return view();
        }
    }
    public function worklist()
    {
        $userid = I('userid');
        $list = M('daichong_user_work')->where('user_id',$userid)->select();
        foreach ($list as $key =>$value){
            if($value['yunying'] == 1){
                $list[$key]['yunying'] = '移动';
            }elseif ($value['yunying'] == 2){
                $list[$key]['yunying'] = '联通';
            }elseif ($value['yunying'] == 3){
                $list[$key]['yunying'] = '电信';
            }
            if($value['status'] == 1){
                $list[$key]['status'] ='开启';
            }elseif ($value['status'] == 0){
                $list[$key]['status'] ='关闭';
            }
        }
        $this->assign('list',$list);
        $this->assign('userid',$userid);
        return view();
    }
    public function getqudao()
    {
        $url='https://api.xianzhuanxia.com';
        $url1 = 'https://cusapitest.xianzhuanxia.com';
        $post_url = $url.'/api/task/recharge/taskChannelList';
        $params=[];
        $appid = '565846A';
        $appkey = '8855404e6df64892a9bde62d8e26d231'; //签名密钥
        $appsecret = 'uy/GDbT3poOcN0BKSkGbAvr8O77iOv/s24W7HJLN79w=';//加密密钥
        $sing = $this->get_xzxsing($params,$appkey,$appid);
        $result = $this->xzx_post($post_url,$params,$sing);
        $datas = json_decode($result);
        var_dump($datas);exit();

    }
    
    public function getxzxsing($params = [],$app_key,$appid){
        // if(empty($params) || empty($app_key) || empty($app_secret)){
        //     throw new \Exception('参数错误');
        // }

        // $params = array_merge($params, ['key' => $app_key, 'queryTime' => time()]);

        ksort($params);

        $buff = '';
        foreach ($params as $k => $v) {
            $buff .= $k .'='. $v;
        }
        
        $queryTime = $this->getMillisecond();
        $buff .= 'queryTime='.$queryTime.'key='.$app_key;
        $str = strtolower(md5($buff));
        $sing = $str.','.$appid.','.$queryTime;
        $sing = base64_encode($sing);
        echo($sing);
        return $sing;
    }
    public function getMillisecond() {
        $microtime = microtime(true);
        return (int) ($microtime * 1000);
    }
    public function xzx_submit()
    {
        $url='https://api.xianzhuanxia.com';
        $url1 = 'https://cusapitest.xianzhuanxia.com';
        $post_url = $url1.'/api/task/recharge/submit';
        $params = [
            'channelId'=>2,
            'productIds'=>7,
            // 'provinces'=>'北京',
            'faceValues'=>'40',
            'minSettleAmounts'=>'39.1',
            // 'num'=>'1'
        ];
        // var_dump($params);
        // $appid = '565846A';
        $appsecret = 'uy/GDbT3poOcN0BKSkGbAvr8O77iOv/s24W7HJLN79w=';
        // $appkey = '8855404e6df64892a9bde62d8e26d231';
        $appid = '558346';
        $appkey = '9a95033d04624caaa1c378bea2689346'; //签名密钥
        $sing = $this->get_xzxsing($params,$appkey,$appid);
        $result = $this->xzx_post($post_url,$params,$sing);
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

        var_dump($datas);exit();
    }
    public function get_token()
    {
        $token_data = Db::name('token')->find();
        // var_dump($token_data);exit();
        if(!$token_data){
            $token = $this->xzx_submit();
        }else{
            
            if(time() - $token_data['date_time'] <300 ){
                echo("222");
            $token = $token_data['token'];
        }else{
            echo("333");
            $token = $this->xzx_submit();
        }
        }
        
        //开始查询申请做单是否匹配订单
        $url='https://api.xianzhuanxia.com';
        $url1 = 'https://cusapitest.xianzhuanxia.com';
        $post_url = $url1.'/api/task/recharge/query';
        $params = [
            'token'=>$token
        ];
        var_dump($params);
        // $appid = '565846A';
        $appsecret = 'uy/GDbT3poOcN0BKSkGbAvr8O77iOv/s24W7HJLN79w=';
        // $appkey = '8855404e6df64892a9bde62d8e26d231';
        $appid = '558346';
        $appkey = '9a95033d04624caaa1c378bea2689346'; //签名密钥
        $sing = $this->get_xzxsing($params,$appkey,$appid);
        $result = $this->xzx_post($post_url,$params,$sing);
        $result = json_decode($result,true);
        var_dump($result);exit();
        if($result){
            $code = $result['code'];
            if($code == 0){
                $datas = $result['result'];
                $orders = $datas['orders'];
            }
        }
    }
    
}
