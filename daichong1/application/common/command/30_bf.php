<?php

//decode by http://chiran.taobao.com/
namespace app\common\command;

use app\common\library\Configapi;
use app\common\library\Notification;
use app\common\library\SmsNotice;
use app\common\model\Client;
use app\common\model\Porder;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;

class Crontab30 extends Command
{
    protected function configure()
    {
        $this->setName('Crontab30')->setDescription('30秒定时器');
    }

    /**
     * @param Input $input
     * @param Output $outputnvalidArgumentException ]
     * 30秒执行任务
     *
     */
    protected function execute(Input $input, Output $output)
    {
        while (1) {
            C(Configapi::getconfig());

            $this->xintiao();
            echo "心跳-执行完成！" . date("Y-m-d H:i:s", time()) . PHP_EOL;
            $this->get_order();//获取订单同步到数据库
            echo "获取订单-执行完成！" . date("Y-m-d H:i:s", time()) . PHP_EOL;
//
//            $this->shangbao();
//            echo "上报订单-执行完成！" . date("Y-m-d H:i:s", time()) . PHP_EOL;
//
//            echo "执行完成！" . date("Y-m-d H:i:s", time()) . PHP_EOL;
            sleep(30);
        }
    }


    /**
     * 订单同步，获取后入库 并且写入到数据库
     * https://www.xitupt.com/cg/api/TaskPub/Partner/SyncOrder
     *
     * [
     * {
     * "id": "812527273176504083",
     * "areaName": "浙江",
     * "arsName": "联通",
     * "account": "17557287198",
     * "denom": 50,
     * "settlePrice": 48,
     * "createTime": "2022-12-30T19:10:24",
     * "chargeTime": "2022-12-30T19:10:26",
     * "uploadTime": "1970-01-01T00:00:00",
     * "status": 5,
     * "settleStatus": 0,
     * "channelOrderId": null,
     * "remark": null
     * },
     * {
     * "id": "812527341895980865",
     * "areaName": "广东",
     * "arsName": "联通",
     * "account": "17666416424",
     * "denom": 50,
     * "settlePrice": 48,
     * "createTime": "2022-12-30T19:10:33",
     * "chargeTime": "2022-12-30T19:10:34",
     * "uploadTime": "1970-01-01T00:00:00",
     * "status": 5,
     * "settleStatus": 0,
     * "channelOrderId": null,
     * "remark": null
     * }
     */
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
            mylog('接收到数据',json_encode($data),'order');
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
                    //$list['chargeTime'] = $v['chargeTime'];

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
                        // mylog('添加订单',json_encode($list));
                        // echo '添加订单：' .$v['id'] . $s . PHP_EOL;
                        //提交 开始充值
                        //  $this->sert($v['id'], 5);
                        //  mylog('提交充值状态返回',json_encode($as));

                    }

                }


            }
            echo '账号' . $user['user'] . '获取订单' . $s . PHP_EOL;

        }else{

            echo '账号' . $user['user'] . '停止接单状态' . PHP_EOL;
        }


    }
    protected function shangbao(){
        $order = M('daichong_order')->where(['sb_type' => 0])->order('id desc')->limit(100)->select();
        foreach ($order as $k=>$v){

            $as =    $this->sert($v['order_id'], 5);
        }


    }
    protected function get_order_bf()
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
            mylog('接收到数据',json_encode($data));
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
                        echo '添加订单：' .$v['id'] . $s . PHP_EOL;
                        //提交 开始充值
                        $this->sert($v['id'], 5);
                        //  mylog('提交充值状态返回',json_encode($as));

                    }

                }


            }
            echo '账号' . $user['user'] . '获取订单' . $s . PHP_EOL;

        }


    }

    //处理订单状态 无提交的提交 提交后成功的反馈状态，失败的提交失败

    /**
     * is_post 向后台提交信息 无论是 成功 或者废弃订单后 都会修改状态
     */
    protected function set_order()
    {
        //先处理提交订单
        echo '订单出来开始工作:';
        $order = M('daichong_order')->where(['is_post' => 0])->order('id desc')->limit(100)->select();
        mylog('准备提交订单',count($order));
        echo '准备提交订单'.count($order). PHP_EOL;
        foreach ($order as $k => $v) {
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
                    mylog('提交订单'.$v['order_id'],$v['account'].$v['denom']);
                    $this->post_porder($v['account'], $v['order_id'], $v['denom']);

                }



            } else {
                //查到订单的状态 就按照订单状态上报信息
                $porder = M('porder')->where(['order_number' => $v['yr_order_id']])->find();
                if ($porder) {
                    if ($porder['status'] == 4) {
                        //执行成功操作
                        M('daichong_order')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>2]);
                        mylog('上报充值成功信息',$v['order_id'].'==5');
                        // $message='http://.jpg';
                        $this->sert($v['order_id'], 5);
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

    }



    public function seorderimg($id,$start)
    {
        $order = M('porder')->where(['out_trade_num' => $id])->find();
        if (!$order) {
            //return $this->success('操作失败!');
            mylog('上报充值成功信息-操作失败无订单',$id);
//Use of undefined constant upload - assumed 'upload' (this will throw an Error in a future version of PHP)"
        } else {
            //访问 url
            $url ='http://115.126.57.143/yrapi.php/open/voucher/id/'.$order['id'].'.html';
            $res = df_get($url);
            $getmsg = M('daichong_order')->where(['order_id'=>$id])->find();
            if(empty($getmsg['base'])){
                // return $this->success('操作失败!无凭证');
                mylog('上报充值成功信息-操作失败无凭证',$id);
                $getmsg['base'] ='http://bdu.jpg';

            }else{


            }
            $this->upload($id, $getmsg['base']);
        }

    }



    public function upload($id,$imgStr){
        $url = 'https://www.xitupt.com/cg/api/TaskPub/Partner/UploadCert';
        $data =[
            'id'=>$id,
            'imgStr'=>$imgStr
        ];

        $user = get_dc_user();
        $header = [
            'Authorization:Bearer ' . $user['token'],
            'content-type: application/json'
        ];


        $xturl = df_post($url, $data, $header);
        mylog('手动上报订单成功信息'.$id,'返回：'.json_encode($xturl));
        if ($xturl != 'true') {
            get_dc_token();
            $this->upload($id, $imgStr);
        } else {

            M('daichong_order')->where(['order_id' => $id])->update(['is_post' => 1]);

        }


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
        $product = M('product')->where(['voucher_name' => $denom, 'added' => 1, 'is_del' => 0])->find();

        $data = [
            "userid" => 3,
            "mobile" => $mobile,
            "out_trade_num" => $out_trade_num,
            "product_id" => $product['id'],
            "notify_url" => 'http://m.bnai.com',
        ];
        $data['sign'] = $this->sign($data);
        return $this->http_get('http://115.126.57.143/yrapi.php/index/recharge', $data);
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
    public function sert($id, $status,$message='')
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
            M('daichong_order')->where(['order_id'=>$id])->update(['sb_type'=>1]);


            //   echo "上报订单" . $id .'='.json_encode($data). PHP_EOL;

        }

    }


    /**
     * 说明：心跳检查，用于告知服务器客户端当前状态，建议每30s调用一次
     * 请求地址：https://id4.cgtest.bolext.com/cg/api/TaskPub/Partner/HeartBeat
     * 请求方式：get
     * 返回结果：true
     * 说明：心跳检查，用于告知服务器客户端当前状态，建议每30s调用一次
     * 请求地址：https://id4.cgtest.bolext.com/cg/api/TaskPub/Partner/SyncOrder
     * 请求方式：get
     * 请求参数：
     *
     */
    protected function xintiao()
    {
        // $this->set_order();//设置订单 ， 无提交的提交 提交后成功的反馈状态，失败的提交失败
        $user = get_dc_user();
        $header = [
            'Authorization:Bearer ' . $user['token'],
        ];
        if ($user['type'] == 1) {
            $xturl = df_get('https://www.xitupt.com/cg/api/TaskPub/Partner/HeartBeat', '', $header);
            echo $xturl. PHP_EOL;
            if ($xturl == 'true') {
                echo '账号' . $user['user'] . '心跳正常' . PHP_EOL;
            } else {
                get_dc_token();
                echo '账号' . $user['user'] . '心跳更换token' . PHP_EOL;
                $this->xintiao();
            }




        } else {


            echo '账号' . $user['user'] . '未启动' . PHP_EOL;
        }


    }




}