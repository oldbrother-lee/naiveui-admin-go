<?php
namespace app\admin\Traits;
use think\Cache;
use app\common\library\Configapi;
use think\Model;

trait DaichongsTrait
{
    protected function get_order($id)
    {
        
        $user_info = M('daichong_user')->where(array('id' => $id))->find();
        mylog('用户'.$user_info['child'].'测试打印1  ', '開始接單');

        $daichonginfo = Cache::get('daichong:'.$id);

        mylog('用户'.$user_info['child'].'测试打印2  ', json_encode($daichonginfo));
        if ($daichonginfo['type'] == 0) {
            return false;
        }
        $work = M('daichong_user_work')->where(array('user_id' => $id))->find();
        $amountText = $work['money'];
        if ($work['yunying'] == "1") {
            $yunying = "移动";
        } else if ($work['yunying'] == "2") {
            $yunying = "联通";
        } else if ($work['yunying'] == "3") {
            $yunying = "电信";
        }
        $count_text=$work['count'];
        if($count_text==0){
            $count_text=1;
        }
        mylog('用户'.$user_info['child'].'测试打印4  ', $amountText . '   ' . $yunying . '   ' . $count_text . '   ' . $daichonginfo['prov_text']);
        $jsonStr='{"amount":"' . $amountText . '","operator_id":"' . $yunying . '","order_num":"' . $count_text . '","prov_code":"' . $daichonginfo['prov_text'] . '"}';
        mylog('用户'.$user_info['child'].'测试打印5  ', $jsonStr);
        dump('用户'.$user_info['child'].'测试打印5  '. $jsonStr);
        mylog('用户'.$user_info['child'].'用户取单', json_encode($jsonStr,JSON_UNESCAPED_UNICODE));

        $this->userorderadd($user_info, $jsonStr, $daichonginfo['type']);

    }
    private function userorderadd($user, $jsonStr, $yunying = 1)
    {
        $user['token'] = 'https://shop.task.mf178.cn/userapi/sgd/getOrder,https://shop.task.mf178.cn/userapi/sgd/updateStatus';
        // $user['token'] = 'http://test.shop.center.mf178.cn/userapi/sgd/getOrder,http://test.shop.center.mf178.cn/userapi/sgd/updateStatus';
        $urls = explode(",", $user['token']); // 运营商 地区 金额
        $res = dc_action($urls[0], 'get', $user, $jsonStr);
        //  $res = dc_action('http://test.shop.center.mf178.cn/userapi/sgd/getVender', 'zhuanqu', $user, $jsonStr);
        echo '============';
        echo json_encode($res);
        echo '============';
        mylog('用户'.$user['child'].'用户取单', json_encode([$user['token'], 'get', $user['user'], $jsonStr],JSON_UNESCAPED_UNICODE));
        $responseJson = json_decode($res, true);
        var_dump('用户'.$user['child'].'用户取单返回'.json_encode($responseJson,JSON_UNESCAPED_UNICODE));
        mylog('用户'.$user['child'].'用户取单返回', json_encode($responseJson,JSON_UNESCAPED_UNICODE));

        if ($responseJson && $responseJson['code'] == 0) {
            //成功
            foreach ($responseJson['data'] as $orderItem) {
                $list = [];

                $currentTimestamp = time(); // 获取当前时间戳
                $timestampIn30Minutes = strtotime('+' . $user['te'] . ' minutes', $currentTimestamp); // 将当前时间戳加上30分钟

                $targetDesc = explode("|", $orderItem['target_desc']); // 运营商 地区 金额

                $list["user"] = $user["id"];
                $list["order_id"] = $orderItem['user_order_id']; //订单编号
                $list["account"] = $orderItem['target']; //号码
                $list["denom"] = $targetDesc[2]; //金额
                $list["settlePrice"] = $targetDesc[2] * 0.97; //结算金额
                $list["createTime"] = $currentTimestamp; //订单创建时间
                $list["chargeTime"] = $timestampIn30Minutes;
                $list["status"] = $orderItem['status']==999?5:$orderItem['status']; // 订单状态


                $list["yunying"] = $yunying;

                $get = M('daichong_orders')->where(['order_id' => $orderItem['user_order_id']])->find();
                if ($get) {
                    M('daichong_orders')->where(['order_id' => $orderItem['user_order_id']])->update($list);
                } else {

                    M('daichong_orders')->insert($list);
                    mylog('用户'.$user['child'].'添加订单', json_encode($list,JSON_UNESCAPED_UNICODE));
                    $this->sert($list, 1);
                }
            }
        }else{
            echo '无新的数据';
            return false;
        }
    }
    /**
     * @param $data
     * @param $status
     * @param $message
     * @return void
     * 下单上报到系统
     */

    public function sert($data, $status, $message = '')
    {

        mylog('用户'.$data["user"].'开始下单 1  ' . $data['order_id'], json_encode($data,JSON_UNESCAPED_UNICODE) . '返回：' . $status);
        mylog('用户'.$data["user"].'开始下单 2  ' . $data['order_id'], $data['account'] . '   ' . $data['order_id'] . '   ' .  $data['denom'] . '   ' .  $data['yunying']);
        $redata = $this->post_porder($data['account'], $data['order_id'], $data['denom'], $data['yunying']);
        $orjson = json_decode($redata, true);

        mylog('用户'.$data["user"].'看看返回的啥 2  ' , $orjson['errmsg']);

        $res = "不需要上报report了，上报状态就好了" . '  ' . $orjson['errno'];

        mylog('用户'.$data["user"].'上报订单充值信息' . $data['order_id'], json_encode($data,JSON_UNESCAPED_UNICODE) . '返回：' . $res);

        db('daichong_orders')->where(['order_id' => $data['order_id']])->update(['beizhu' => $orjson['errmsg']]);

        $order = db('daichong_orders')->where(['order_id' => $data['order_id']])->find();
        if($orjson['errno']==11 || $orjson['errmsg']=='未找到该号码归属地' || $orjson['errmsg']=='未找打符合该充值的产品，请查看代理端产品列表是否存在该产品ID！'){

            mylog('订单开始上报，' . $data['order_id'] , '  因为该订单 ' . $orjson['errmsg'] . ' ,所以订单上报状态为 失败');
            $aaa= $this->setordertype($order, 8, $orjson['errmsg']);
            mylog('订单开始上报，' . $data['order_id'] , '  完成' . json_encode($aaa,JSON_UNESCAPED_UNICODE));
        }

    }
    public function setordertype($datas, $status, $msg = '成功')
    {
        $user = db('daichong_user')->where(['child' => $datas['user']])->find();
        // var_dump($user);exit();
        mylog('用户'.$user["child"].'上报订单信息' . $datas['order_id'], '返回：');

        if ($status == 8) {
            //弃单
            db('daichong_orders')->where(['order_id' => $datas['order_id']])->update(['uploadTime' => time(), 'status' => 9, 'type' => 1]);
        } else {
            //核实中
            db('daichong_orders')->where(['order_id' => $datas['order_id']])->update(['uploadTime' => time(), 'status' => 6, 'type' => 2]);
        }

        $jsonStr = '{"user_order_id":"' . $datas['order_id'] . '","status":"' . $status . '","rsp_info":"' . $msg . '"}';
        $urls = explode(",", $user['token']); // 运营商 地区 金额
        $res = dc_action('https://shop.task.mf178.cn/userapi/sgd/updateStatus', 'status', $user, $jsonStr);
        // $res = dc_action('http://test.shop.center.mf178.cn/userapi/sgd/updateStatus', 'status', $user, $jsonStr);
        // var_dump($res);exit();
        $resd = json_decode($res, true);
        mylog('用户'.$user["child"].'上报订单信息' . $datas['order_id'], $jsonStr . '返回：' . $res);
        return $resd;
    }
    /**
     * @param $mobile
     * @param $out_trade_num
     * @param $denom
     * @return mixed
     * 提交订单到后台
     */

    public function post_porder($mobile, $out_trade_num, $denom, $yunying = 1)
    {
        if ($yunying == 1) {
            $product = M('product')->where(['voucher_name' => $denom, 'isp' => 1, 'cate_id' => 22, 'added' => 1, 'is_del' => 0])->find();
        } else if ($yunying == 2) {
            $product = M('product')->where(['voucher_name' => $denom, 'isp' => 2, 'cate_id' => 21, 'added' => 1, 'is_del' => 0])->find();
        } else {
            $product = M('product')->where(['voucher_name' => $denom, 'isp' => 2, 'cate_id' => 23, 'added' => 1, 'is_del' => 0])->find();
        }

        mylog('post_order测试 ',$yunying . '   ' . $mobile . '    ' . $out_trade_num . '   ' . $product['id'] );

        $data = [
            "userid" => 3,
            "mobile" => $mobile,
            "out_trade_num" => $out_trade_num,
            "product_id" => $product['id'],
            "notify_url" => 'http://m.bnai.com',
        ];
        $data['sign'] = $this->sign($data);

        mylog('签名完成 1 ', json_encode($data));
        echo '签名完成 1 ', json_encode($data).PHP_EOL;
        $rest = $this->http_get('http://115.126.57.143/yrapi.php/index/recharge', $data);
        mylog('看看下单的结果 ', json_encode($rest));
        echo '看看下单的结果 '.json_encode($rest).PHP_EOL;
        return $rest;
    }
    //签名
    public function sign($data)
    {

        mylog('开始签名 1 ', json_encode($data));
        ksort($data);
        $sign_str = http_build_query($data) . '&apikey=021GzZjchvkwi9AYQs6yKMxRNfoPtS4r';
        $sign_str = urldecode($sign_str);
        return strtoupper(md5($sign_str));
    }
    /**
     * get请求
     */
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
            return $sContent;
        }
    }
    public function getZhuanQu($id){
        $user = M('daichong_user')->where(array('id' => I('id')))->find();
        if(!$user){
            return 0;
        }
        $res = dc_action('https://shop.task.mf178.cn/userapi/sgd/getVender', 'zhuanqu', $user, '');
        dump($res);
        die();
    }

    public function startGetOrder($id){
        $work = M('daichong_user_work')->where(array('user_id' => $id))->find();
        //地区
        $prov_text = $work['prov_select'];
        if(!$prov_text){
            $prov_text="北京, 广东, 上海, 天津, 重庆, 辽宁, 江苏, 湖北, 四川, 陕西, 河北, 山西, 河南, 吉林, 黑龙江, 内蒙古, 山东, 安徽, 浙江, 福建, 湖南, 广西, 江西, 贵州, 云南, 西藏, 海南, 甘肃, 宁夏, 青海, 新疆";
        }
        //每次订单数
        $countText = $work['count'];
        if($countText==0){
            $countText=1;
        }
        $daichongInfo=[
            'prov_text'=>$prov_text,
            'count_text'=>$countText,
            'auto_get_order'=>1
        ];

        $denomInfos = [];
        $user = db('daichong_user')->where(['id' => $id])->find();
        $denomInfos['user'] = db('daichong_user')->where(['id' => $id])->select();
        $denomInfos['type'] = $work['yunying'];
        $denomInfos['te'] = $work['te'] ? :10;
        $denomInfos['prov_text'] = $prov_text;
        $denomInfos['count_text']= $countText;
        $te = $denomInfos['te'];
        if(empty($te)){
            $te = 0;
        }
        $money = explode(',', $work['money']);
        foreach ($money as $index => $item) {
            $denomInfos['money'][] = ['denom' => $item];
        }
        Cache::set('daichong:'.$id, $denomInfos);
        Cache::set('daichongInfo:'.$id,$daichongInfo);
        db('daichong_user')->where(['id' => $id])->update(['type'=>1,'yunying'=> $denomInfos['type'],'count'=>$countText ,'te'=>$te]);
        //$aaa = $this->get_order($id);
        return true;
    }
    public function set_order()
    {
        //先处理提交订单
         mylog('订单出来开始工作:', '');
         echo '订单出来开始工作:'.PHP_EOL;
        $order = M('daichong_orders')->where(['is_post' => 0])->order('id desc')->limit(100)->select();

         mylog('准备提交订单' . count($order), '');
        echo '准备提交订单' . count($order).PHP_EOL;

        foreach ($order as $k => $v) {
            $porder = null;
            if (empty($v['yr_order_id'])) { //未提交订单状态 向后台提交订单
                $porder = M('porder')->where(['out_trade_num' => $v['order_id']])->find();
                 mylog('存在订单号' . $v['order_id'], '');

                if ($porder) {

                    $p = M('daichong_orders')->where(['order_id' => $v['order_id']])->update(['yr_order_id' => $porder['order_number'], 'is_post' => 1]);
                    if ($p) {
                         mylog('更新成功' . $v['order_id'], '');
                         echo '更新成功' . $v['order_id'].PHP_EOL;
                    } else {
                         mylog('更新失败' . $v['order_id'], '');
                         echo '更新失败' . $v['order_id'].PHP_EOL;
                    }
                } else {
                    // mylog('提交订单'.$v['order_id'],$v['account'].$v['denom']);
                     mylog('关闭订单' . $v['order_id'], '');
                    echo '关闭'.$v['order_id'].PHP_EOL;
                    M('daichong_orders')->where(['id' => $v['id']])->update(['is_post' => 1, 'type' => 1]);
                    $this->setordertype($v, 3, '失败');
//                    $this->post_porder($v['account'], $v['order_id'], $v['denom']);
                }
            } else {
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
    }
}