<?php

//decode by http://chiran.taobao.com/
namespace app\yrapi\controller;

use app\common\library\Createlog;
use app\common\model\Client;
use app\common\model\Porder as PorderModel;
use app\common\model\Product as ProductModel;
use QL\QueryList;
use think\Cache;
// 实例化 对象
class Tests
{
    public function nerwtimeout(){

      echo   date('Y-m-d H:i:s',time());die;



        $user = get_dc_user(2);// 6成功 9失败 5充值中
        $u_time = $user['te'] * 60;//超时时间
        if($u_time<1){
            return false;
        }


        $porder = M('porder')->where(['status'=>3])->order('id ASC')->limit(30)->select();


        if($u_time>0){
            foreach ($porder as $k=>$v){
                $atime = time()-$v['create_time'];
                dump($u_time);
                dump($atime);die;
                if($atime>$u_time){
                    echo $v['id'].PHP_EOL;

                    //执行订单申请失败
                  //  PorderModel::applyCancelOrder($v['id'], "超时系统自动申请-后台自动化");
                }

            }
        }

    }

    /**
     * @return void
     * 新增上报信息 每个订单都推送3次信息到系统
     */
    public function posttype()
    {


        $order = Db('daichong_orders')
            ->where('status', 'in', '6,9')
            ->where('sb_type', '<', 3)
            ->order('id ASC')
            ->limit(10)
            ->select();


        // $getdaic =  M('daichong_orders')->where(['order_id'=>$param['out_trade_num']])->find();
        foreach ($order as $k => $v) {
            if ($v['type'] == 1) {
                $this->so_post_order($v, 3, '失败');
            } elseif ($v['type'] == 2) {
                $this->so_post_order($v, 1);
            }
        }


    }

    public function so_post_order($datas, $status, $msg = '成功')
    {

        $user = db('daichong_user')->where(['id' => $datas['user']])->find();

        $data = [
            'id' => $datas['order_id'],
            'mobile' => $datas['account'],
            'result' => $status,
            'remark' => '充值-订单号' . $datas['yr_order_id'] . $msg,
        ];

        db('daichong_orders')->where(['order_id' => $datas['order_id']])->update(['sb_type' => $datas['sb_type'] + 1]);

        $res = dc_action($user['token'], 'status', $user['zhuanqu'], $data);

        mylog('超时上报订单信息' . $datas['order_id'], json_encode($data) . '返回：' . $res, 'csdd' . date('Y-m-d', time()));

    }


    public function dczidonghua()
    {
        $this->get_order();
    }


//    public function setordertype($datas, $status,$msg='成功'){
//
//        // $redata = $this->post_porder($data['account'], $data['order_id'], $data['denom']);
//        //   $orjson = json_decode($redata,true);
//        $user =db('daichong_user')->where(['id'=>$datas['user']])->find();
//
//        $data = [
//            'id' => $datas['order_id'],
//            'mobile' => $datas['account'],
//            'result'=>$status,
//            'remark'=>'充值-订单号'.$datas['yr_order_id'].$msg,
//
//        ];
//        if($status==3){
//            db('daichong_orders')->where(['order_id'=>$datas['order_id']])->update(['uploadTime'=>time(),'status'=>9,'type'=>1]);
//        }else{
//            db('daichong_orders')->where(['order_id'=>$datas['order_id']])->update(['uploadTime'=>time(),'status'=>6,'type'=>2]);
//        }
//        $res =    dc_action($user['token'],'status',$user['zhuanqu'],$data);
//
//        $resd = json_decode($res,true);
//        //   mylog('上报订单信息' . $datas['order_id'], json_encode($data) . '返回：' . $res);
//
//
//
//    }
    public function timeout(){
        $user = get_dc_user(2);// 6成功 9失败 5充值中

        $order =Db('daichong_orders')->where(['status'=>5])->order('id ASC')->limit(50)->select();
        $u_time = $user['te'] * 60;//超时时间
        foreach ($order as $k=>$v){
            $atime = time()-$v['createTime'];
            $porder = M('porder')->where(['order_number'=>$v['yr_order_id']])->find();

            if($porder){
                if($u_time<=$atime){
                    //执行订单申请失败

                    return PorderModel::applyCancelOrder($porder['id'], "超时系统自动申请-后台自动化");

                }
            }else{
                M('daichong_orders')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>1]);
                $this->setordertype($v, 3,'失败');
            }

        }
        //  $order =Db('daichong_orders')->where(array('chargeTime' => array('ELT',$atime)))->select();

        //   dump($order);
    }
    public function ooo(){
        $mobile = 15537288720;
        $is = konghaojiance($mobile);
        if(!$is){
            return djson(1, '该账号检测状态不正常，请核实后再提交订单');
        }
        echo 1;
    }
    public function daitimeout(){
        $user = get_dc_user();// 6成功 9失败 5充值中

        $order =Db('daichong_order')->where(['type'=>0])->order('id ASC')->limit(30)->select();

        $u_time = $user['te'] * 60;//超时时间
        foreach ($order as $k=>$v){

            echo '处理id：'.$v['id'].'超时时间为：'.$u_time.PHP_EOL;

            if($u_time>0){
                $atime = time()-strtotime($v['chargeTime']);

                echo '已使用时间：'.$atime;
                $porder = M('porder')->where(['order_number' => $v['yr_order_id']])->find();


                if($porder){
                    if($atime>$u_time){

                        //执行订单申请失败
                        echo '执行退单操作'.$porder['id'].PHP_EOL;
                        $as =  PorderModel::applyCancelOrder($porder['id'], "超时系统自动申请-后台自动化");

                    }
                }else{
//                M('daichong_orders')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>1]);
//                $this->setordertype($v, 3,'失败');
                }
            }


        }
        //  $order =Db('daichong_orders')->where(array('chargeTime' => array('ELT',$atime)))->select();

        //   dump($order);
    }
    public function setordertype($datas, $status,$msg='成功'){

        // $redata = $this->post_porder($data['account'], $data['order_id'], $data['denom']);
        //   $orjson = json_decode($redata,true);
        $user =db('daichong_user')->where(['id'=>$datas['user']])->find();

        $data = [
            'id' => $datas['order_id'],
            'mobile' => $datas['account'],
            'result'=>$status,
            'remark'=>'充值-订单号'.$datas['yr_order_id'].$msg,

        ];
        if($status==3){
            db('daichong_orders')->where(['order_id'=>$datas['order_id']])->update(['uploadTime'=>time(),'status'=>9,'type'=>1,'sb_type'=>1]);
        }else{
            db('daichong_orders')->where(['order_id'=>$datas['order_id']])->update(['uploadTime'=>time(),'status'=>6,'type'=>2,'sb_type'=>1]);
        }
        $res =    dc_action($user['token'],'status',$user['zhuanqu'],$data);

        mylog('超时上报订单信息' . $datas['order_id'], json_encode($data) . '返回：' . $res,'csdd'.date('Y-m-d',time()));




    }


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
            //获取地区
            $prov = Cache::get('daichongs_prov', null);
            if(!empty($prov) && is_array($prov)){
                $data['prov'] = implode(',', $prov);
            }
            mylog('用户取单', json_encode($data),'dier'.date('Ymd H',time()));

            $this->userorderadd($v, $data, $daichonginfo['type']);
        }


    }

    private function userorderadd($user, $data, $yunying = 1)
    {


        $res = dc_action($user['token'], 'get', $user['zhuanqu'], $data);
        mylog('用户取单', json_encode([$user['token'], 'get', $user['zhuanqu'], $data]),'dier'.date('Ymd H',time()));

        $resd = json_decode($res, true);
        mylog('用户取单返回', json_encode($resd),'dier'.date('Ymd H',time()));

        if ($resd['ret'] == 0) {
            if (!empty($resd['data'])) {
                $list['user'] = $user['id'];
                $list['order_id'] = $resd['data']['id'];
                $list['account'] = $resd['data']['mobile'];
                $list['denom'] = $resd['data']['amount'];//金额
                $list['createTime'] = time();
                $list['yunying'] = $yunying;
                $list['chargeTime'] = $resd['data']['timeout'];//超时时间
                $list['status'] = 5;

                $get = M('daichong_orders')->where(['order_id' => $resd['data']['id']])->find();
                if ($get) {
                    M('daichong_orders')->where(['order_id' => $resd['data']['id']])->update($list);

                } else {

                    M('daichong_orders')->insert($list);
                    // mylog('添加订单', json_encode($list));
                    mylog('添加订单', json_encode($data),'dier'.date('Ymd H',time()));

                    $this->sert($list, 1);
                }
            } else {
                echo '无新的数据';
                return false;
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


    /**
     * @param $data
     * @param $status
     * @param $message
     * @return void
     * 下单上报到系统
     */

    public function sert($data, $status, $message = '')
    {

        $redata = $this->post_porder($data['account'], $data['order_id'], $data['denom'], $data['yunying']);
        $orjson = json_decode($redata, true);
        // $user = get_dc_user(2);
        $user = db('daichong_user')->where(['id' => $data['user']])->find();
        $datas = [
            'id' => $data['order_id'],
            'mobile' => $data['account'],
            'result' => $status,
            'context' => $orjson['data']['order_number'],
            'remark' => '下单成功，订单号' . $orjson['data']['order_number'],
        ];
        $res = dc_action($user['token'], 'report', $user['zhuanqu'], $datas);

        $resd = json_decode($res, true);
        mylog('上报订单充值信息' . $data['order_id'], json_encode($datas) . '返回：' . $res);
    }
    public function serrrr(){
        $data = M('daichong_orders')->where(['id'=>68893])->find();
        $redata = $this->post_porder($data['account'], $data['order_id'], $data['denom'], $data['yunying']);

    }

    /**
     * @param $mobile
     * @param $out_trade_num
     * @param $denom
     * @return mixed
     * 提交订单到后台
     */

    public function post_porder($mobile, $out_trade_num, $denom,$yunying=1)
    {
        if($yunying==1){ //1移动 2电信  3联通
            $product = M('product')->where(['voucher_name' => $denom,'isp'=>1,'cate_id'=>22, 'added' => 1, 'is_del' => 0])->find();
        }else if($yunying==2){
            $product = M('product')->where(['voucher_name' => $denom,'isp'=>3,'cate_id'=>21, 'added' => 1, 'is_del' => 0])->find();

        }else{
            $product = M('product')->where(['voucher_name' => $denom,'isp'=>2,'cate_id'=>23, 'added' => 1, 'is_del' => 0])->find();
        }

        mylog('订单下单', json_encode($product),'dier'.date('Ymd H',time()));
        $data = [
            "userid" => 3,
            "mobile" => $mobile,
            "out_trade_num" => $out_trade_num,
            "product_id" => $product['id'],
            "notify_url" => 'http://m.bnai.com',
        ];
        $data['sign'] = $this->sign($data);
        mylog('下单数据', json_encode($data),'dier'.date('Ymd H',time()));

        return $this->http_get('http://115.126.57.143/yrapi.php/index/recharge', $data);
    }

    //签名
    public function sign($data)
    {
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
        mylog('下单返回', $sContent,'dier'.date('Ymd H',time()));
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            $result = json_decode($sContent, true);
            M('daichong_orders')->where(['order_id'=>$param['out_trade_num']])->update(['beizhu'=>$result['errmsg']]);
            if ($result['errno'] == 0) {
                // M('daichong_orders')->where(['order_id'=>$param['out_trade_num']])->update(['is_post' => 1]);
                // $getdaic =  M('daichong_orders')->where(['order_id'=>$param['out_trade_num']])->find();
                // $this->setordertype($getdaic, 1,'成功');
                //return rjson(0, $result['errmsg'], $result['data']);
            } else {
                // return rjson(1, $result['errmsg'], $result['data']);
                //订单失败直接执行失败操作
                M('daichong_orders')->where(['order_id'=>$param['out_trade_num']])->update(['is_post' => 1,'type'=>1]);
                $getdaic =  M('daichong_orders')->where(['order_id'=>$param['out_trade_num']])->find();
                $this->setordertype($getdaic, 3,'失败');

            }

        } else {
            // return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"]);
        }
        return $sContent;
    }


}