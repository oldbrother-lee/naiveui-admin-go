<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use app\common\library\Configapi;

class Daichong extends Admin
{
    private $user = '123';
    private $pwd = '';
    



    /**
     * 中控首页
     *    S('bane',1);
     */
    public function mian()
    {

        $user = M('daichong_user')->where(['id' => 1])->find();
        $this->assign('user', $user);
        // $list = M('daichong_order d')
        //    ->join('porder o','d.yr_order_id=o.id')
        //  ->select();
        $map= [];
        if (I('key')) {
            $map['id|account|order_id|yr_order_id'] = array('like', '%' . I('key') . '%');
        }


        $lists = M('daichong_order')->where($map)->order('id desc')->paginate(20);
        $page = $lists->render();
        $list = $lists->all();


        foreach ($list as $k => $v) {
            $a_time = strtotime($v['chargeTime']);
            $b_time = time();
            $u_time = $user['te'] * 60;
            $a = $b_time - $u_time;

            if ($a < $a_time) {
                $list[$k]['is_cs'] = 1;
            } else {
                if($v['type']<1){
                    $list[$k]['is_cs'] = 2;
                }else{
                    $list[$k]['is_cs'] = 1;
                }

            }
            if($list[$k]['is_cs']==1){
                if($v['type']==2){
                    $list[$k]['is_cs'] = 3;
                }
            }


        }

        $this->assign('page', $page);
        $this->assign('list', $list);
        return view();
    }


    public function zidonghua(){
        //$this->set_order();
       // $this->set_orders();
        $this->get_order();
    }
    protected function get_order()
    {
        $user = get_dc_user();
        $header = [
            'Authorization:Bearer ' . $user['token'],
        ];
        $s = 0;


        if ($user['type'] == 1) {

            $xml_data = df_get('https://www.xitupt.com/cg/api/TaskPub/Partner/SyncOrder', '', $header);
            if(empty($xml_data)){
                echo  '无新的数据';
                return false;
            }
            $data = json_decode($xml_data, true);
            mylog('接收到数据',json_encode($data),'order'.date('Y-m-d H',time()));
            echo  '接收到数据'.count($data);
            if ($data) {
                $list = [];
                foreach ($data as $k => $v) {


                    $list['user'] = $user['id'];
                    $list['order_id'] = $v['id'];
                    $list['account'] = $v['account'];
                    $list['denom'] = $v['denom'];
                    $list['settlePrice'] = $v['settlePrice'];
                    $list['createTime'] = $v['createTime'];
                    $list['chargeTime'] = $v['chargeTime'];

                    $list['uploadTime'] = $v['uploadTime'];
                    $list['status'] = $v['status'];
                    $list['settleStatus'] = $v['settleStatus'];
                    $list['chargeTime'] = $v['chargeTime'];
                    $get = M('daichong_order')->where(['order_id' => $v['id']])->find();
                    if ($get) {
                        M('daichong_order')->where(['order_id' => $v['id']])->update($list);

                        //  mylog('更新订单' .$v['id'],json_encode($list));
                        //   echo '更新订单：' .$v['id'] . $s . PHP_EOL;
                    } else {
                        $s++;
                        M('daichong_order')->insert($list);
                        mylog('添加订单',json_encode($list));
                        // echo '添加订单：' .$v['id'] . $s . PHP_EOL;
                        //提交 开始充值
                          $this->sert($v['id'], 5);
                        //  mylog('提交充值状态返回',json_encode($as));

                    }

                }


            }
            echo '账号' . $user['user'] . '获取订单' . $s . PHP_EOL;

        }else{

            echo '账号' . $user['user'] . '停止接单状态' . PHP_EOL;
        }


    }

    /**
     * 设置订单成功
     * https://id4.cgtest.bolext.com/cg/api/TaskPub/Partner/UploadCert
     */
    public function setstaus()
    {
        $id = I('id');
        if ($id > 0) {

            $this->sert($id, 259);

        }


        return $this->success('操作成功!');

    }
    public function set_orders()
    {
        //先处理提交订单
        echo '订单出来开始工作:';
        $order = M('daichong_order')->where(['type' => 0])->order('id desc')->limit(20)->select();

        echo '准备提交订单'.count($order). PHP_EOL;

        foreach ($order as $k => $v) {
            $porder =null;
            if (empty($v['yr_order_id'])) {//未提交订单状态 向后台提交订单
                $porder = M('porder')->where(['out_trade_num' => $v['order_id']])->find();
                echo '存在订单号'.$v['order_id']. PHP_EOL;

                if ($porder) {

                    $p=   M('daichong_order')->where(['order_id' => $v['order_id']])->update(['yr_order_id' => $porder['order_number'],'is_post'=>1]);
                    if($p){
                        echo '更新成功'.$v['order_id']. PHP_EOL;
                    }else{
                        echo '更新失败'.$v['order_id']. PHP_EOL;
                    }
                }else{
                    // mylog('提交订单'.$v['order_id'],$v['account'].$v['denom']);
                    echo '提交订单'.$v['order_id']. PHP_EOL;
                    $this->post_porder($v['account'], $v['order_id'], $v['denom']);

                }
                echo 1;


            } else {

                //查到订单的状态 就按照订单状态上报信息
                $porder= false;
                $porder = M('porder')->where(['order_number' => $v['yr_order_id']])->find();
                echo 'id'.$porder['order_number'];
                if ($porder) {
                    if ($porder['status'] == 4) {
                        echo '上报充值失败信息'.$porder['status'];
                        //执行成功操作
                        M('daichong_order')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>2]);
                        mylog('上报充值成功信息',$v['order_id'].'==5');
                        // $message='http://.jpg';
                        $this->sert1($v['order_id'], 259);
                        //echo "上报订单状态成功！" . $v['order_id'] . PHP_EOL;


                    } elseif ($porder['status'] == 6) {
                        //执行失败操作
                       // mylog('上报充值失败信息',$v['order_id'].'==2');
                        echo '上报充值失败信息'.$porder['status'];
                        M('daichong_order')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>1]);
                        $this->sert1($v['order_id'], 2);
                        // echo "上报订单状态失败！" . $v['order_id'] . PHP_EOL;


                    }else{
                    echo '上报充值失败信息'.$porder['status'];
                        M('daichong_order')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>1]);
                       // mylog('上报充值失败信息',$v['order_id'].'==2');
                    }

                }

            }

        }
        return false;
    }
    public function set_order()
    {
        //先处理提交订单
        echo '订单出来开始工作:';
        $order = M('daichong_order')->where(['is_post' => 0])->order('id desc')->limit(100)->select();

        echo '准备提交订单'.count($order). PHP_EOL;

        foreach ($order as $k => $v) {
            $porder =null;
            if (empty($v['yr_order_id'])) {//未提交订单状态 向后台提交订单
                $porder = M('porder')->where(['out_trade_num' => $v['order_id']])->find();
                echo '存在订单号'.$v['order_id']. PHP_EOL;

                if ($porder) {

                    $p=   M('daichong_order')->where(['order_id' => $v['order_id']])->update(['yr_order_id' => $porder['order_number'],'is_post'=>1]);
                    if($p){
                        echo '更新成功'.$v['order_id']. PHP_EOL;
                    }else{
                        echo '更新失败'.$v['order_id']. PHP_EOL;
                    }
                }else{
                   // mylog('提交订单'.$v['order_id'],$v['account'].$v['denom']);
                    echo '提交订单'.$v['order_id']. PHP_EOL;
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
                        M('daichong_order')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>2]);
                        mylog('上报充值成功信息',$v['order_id'].'==5');
                        // $message='http://.jpg';
                        $this->sert($v['order_id'], 259);
                        //echo "上报订单状态成功！" . $v['order_id'] . PHP_EOL;


                    } elseif ($porder['status'] == 6) {
                        //执行失败操作
                        mylog('上报充值失败信息',$v['order_id'].'==2');
                        M('daichong_order')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>1]);
                        $this->sert($v['order_id'], 2);
                        // echo "上报订单状态失败！" . $v['order_id'] . PHP_EOL;


                    }

                }

            }

        }
        return false;
    }



    /**
     * @param $mobile
     * @param $out_trade_num
     * @param $denom
     * @return mixed
     * 提交订单到后台
     */

    public function post_porder($mobile, $out_trade_num, $denom)
    {
        $product = M('product')->where(['voucher_name' => $denom, 'isp'=>3,'added' => 1, 'is_del' => 0])->find();

        $data = [
            "userid" => 3,
            "mobile" => $mobile,
            "out_trade_num" => $out_trade_num,
            "product_id" => $product['id'],
            "notify_url" => 'http://m.bnai.com',
        ];
        $data['sign'] = $this->sign($data);
        return $this->http_get('http://mf.onetar.top/yrapi.php/index/recharge', $data);
    }

    //签名
    public function sign($data)
    {
        ksort($data);
        $sign_str = http_build_query($data) . '&apikey=EojFDKLbOPJty1B8IX9iUQnrCTSml0ZR';
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
            if ($result['errno'] == 0) {
                // return rjson(0, $result['errmsg'], $result['data']);
            } else {
                // return rjson(1, $result['errmsg'], $result['data']);
            }
        } else {
            // return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"]);
        }
    }

    //上报操作
    public function sert1($id, $status,$message='')
    {

        $url = 'https://www.xitupt.com/cg/api/TaskPub/Partner/SendMsg';
        $data = [
            'id' => $id,
            'evt' => $status,
            'message' => $message,

        ];
        $user = get_dc_user();
        $header = [
            'Authorization:Bearer ' . $user['token'],
            'content-type: application/json'
        ];
        //   echo "上报订单" . $id .'='.json_encode($data). PHP_EOL;

        $xturl = df_post($url, $data, $header);

        mylog('上报信息post',$url.'---'.json_encode($data).'---'.json_encode($header));
        mylog('上报返回','---'.json_encode($xturl));
        if ($xturl != 'true') {
            mylog('上报返回不为true再次提交','---'.json_encode($xturl).'--'.$id.'--'.$status);
            //  get_dc_token();
            // $this->sert($id, $status);


        }else{

            //   echo "上报订单" . $id .'='.json_encode($data). PHP_EOL;

        }

    }


    public function moneyset()
    {
        $tb = I('money');
        $user = get_dc_user();
        if ($user[$tb] == 1) {
            $up = 0;
        } else {
            $up = 1;
        }


        M('daichong_user')->where(['id' => 1])->update([$tb => $up]);

        $msg = [
            'code' => 1,

        ];

        return ($msg);
    }


    public function seorderimg($id, $start)
    {
        $order = M('porder')->where(['out_trade_num' => $id])->find();
        if (!$order) {
            return $this->success('操作失败!');
//Use of undefined constant upload - assumed 'upload' (this will throw an Error in a future version of PHP)"
        } else {
            //访问 url
            $url = 'http://mf.onetar.top/yrapi.php/open/voucher/id/' . $order['id'] . '.html';
            $res = df_get($url);
            $getmsg = M('daichong_order')->where(['order_id' => $id])->find();
            if (empty($getmsg['base'])) {
                return $this->success('操作失败!无凭证');

            } else {

                $this->upload($id, $getmsg['base']);
            }
        }

    }

    /**
     * 设置订单失败
     */
    public function setstaus_w()
    {
        $id = I('id');


        if ($id > 0) {

            $this->sert($id, 2);

        }

        return $this->success('操作成功!');

    }

    public function upload($id, $imgStr)
    {
        $url = 'https://id4.cgtest.bolext.com/cg/api/TaskPub/Partner/UploadCert';
        $data = [
            'id' => $id,
            'imgStr' => $imgStr
        ];

        $user = get_dc_user();
        $header = [
            'Authorization:Bearer ' . $user['token'],
            'content-type: application/json'
        ];


        $xturl = df_post($url, $data, $header);
        mylog('手动上报订单成功信息' . $id, '返回：' . json_encode($xturl));
        if ($xturl != 'true') {
            get_dc_token();
            $this->upload($id, $imgStr);
        } else {

            M('daichong_order')->where(['order_id' => $id])->update(['is_post' => 1]);

        }


    }


    public function sert($id, $status, $message = '')
    {

        $url = 'https://www.xitupt.com/cg/api/TaskPub/Partner/SendMsg';
        $data = [
            'id' => $id,
            'evt' => $status,
            'message' => $message,

        ];
        $user = get_dc_user();
        $header = [
            'Authorization:Bearer ' . $user['token'],
            'content-type: application/json'
        ];


        $xturl = df_post($url, $data, $header);

        mylog('手动上报订单信息' . $id, json_encode($data) . '返回：' . $xturl);


            M('daichong_order')->where(['order_id' => $id])->update(['is_post' => 1]);



    }

    public function deletes()
    {

        $id = I('id');
        if ($id > 0) {

            M('daichong_order')->where(['id' => $id])->delete();

        }
        return $this->success('操作成功!');

    }
    public function deall()
    {


   

        M('daichong_order')->where('id','>',0)->delete();
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
        $user = get_dc_user();
        $user['type'] = 0;
        M('daichong_user')->where(['id' => $user['id']])->update($user);
        $header = [
            'Authorization:Bearer ' . $user['token'],
        ];

        $xturl = df_get('https://www.xitupt.com/cg/api/TaskPub/Partner/Stop', '', $header);

        $msg = [
            'code' => 1,

        ];

        return ($msg);

    }

    /**
     * 开始系统接单
     *
     */
    public function start()
    {

        $user = get_dc_user();
        $user['type'] = 1;
        //  $money = I('money');
        $count = I('count');
        //  $user['money'] = $money;
        $user['count'] = $count;

        M('daichong_user')->where(['id' => $user['id']])->update($user);
        $header = [
            'Authorization:Bearer ' . $user['token'],
            'content-type: application/json'
        ];
        if ($user['money30'] == 1) {
            $denomInfos[] = ['denom' => 30, 'checked' => true];
        }
        if ($user['money50'] == 1) {
            $denomInfos[] = ['denom' => 50, 'checked' => true];
        }
        if ($user['money100'] == 1) {
            $denomInfos[] = ['denom' => 100, 'checked' => true];
        }
        if ($user['money200'] == 1) {
            $denomInfos[] = ['denom' => 200, 'checked' => true];
        }
        if ($user['money300'] == 1) {
            $denomInfos[] = ['denom' => 300, 'checked' => true];
        }
        if ($user['money500'] == 1) {
            $denomInfos[] = ['denom' => 500, 'checked' => true];
        }

        $data = [
            'count' => $count,
           // 'chId' => '774640825492591826',
            'chId' =>$user['zhuanqu'],

           
            'denomInfos' => $denomInfos

        ];
        $msg = [
            'code' => 0,

        ];


        $xturls = df_post('https://www.xitupt.com/cg/api/TaskPub/Partner/Set', $data, $header);


        $xturl = df_post('https://www.xitupt.com/cg/api/TaskPub/Partner/Start', $data, $header);

        if ($xturl == 'true') {
            $msg = [
                'code' => 1,
                'msg' => '开始接单成功！',
                'Start' => $xturl,
                'set' => $xturls,
            ];

        }


        return ($msg);
    }

    public function ooo()
    {
        //先处理提交订单
        $order = M('daichong_order')->where(['is_post' => 0])->order('id desc')->limit(100)->select();


        foreach ($order as $k => $v) {
            if (empty($v['yr_order_id'])) {//未提交订单状态 向后台提交订单
                $porder = M('porder')->where(['out_trade_num' => $v['order_id']])->find();
                if ($porder) {
                  //  M('daichong_order')->where(['order_id' => $v['order_id']])->update(['yr_order_id' => $porder['order_number'], 'is_post' => 1]);
                }

                    dump($porder);
                dump($v);die;

            }

        }

    }

}