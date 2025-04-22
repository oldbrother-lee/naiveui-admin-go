<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use app\common\library\Configapi;
use think\Cache;

class Daichongs extends Admin
{
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
        }

        //获取地区
        $prov = Cache::get('daichongs_prov', null);
        $this->assign('daichongs_prov', $prov ?: []);

        $this->assign('page', $page);
        $this->assign('list', $list);
        return view();
    }


    public function zidonghua()
    {
        //增加用户批量登陆
        // $this->userlogin();
        //$this->set_order();
        // $this->set_orders();
       $this->get_order();
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
    
    protected function get_order()
    {

        mylog('测试打印1  ', '開始接單');

        $daichonginfo = Cache::get('daichong');

        mylog('测试打印2  ', json_encode($daichonginfo));
        if ($daichonginfo['type'] == 0) {
            return false;
        }

        foreach ($daichonginfo['user'] as $k => $v) {

        mylog('测试打印3  ', 'user');

            /* $daichonginfo = null;
            $daichonginfo = Cache::get('daichong');
            if (empty($daichonginfo['moneyn'])) {
                $money = $this->huoquemoney(0, $daichonginfo);
            } else {
                $money = $this->huoquemoney($daichonginfo['moneyn'], $daichonginfo);
            }*/
            
            $amountArray = [];
            $amountText="";
            if ($v['money10'] == 1) {
                array_push($amountArray, "10");
            }
            if ($v['money20'] == 1) {
                array_push($amountArray, "20");
            }
            if ($v['money30'] == 1) {
                array_push($amountArray, "30");
            }
            if ($v['money50'] == 1) {
                array_push($amountArray, "50");
            }
            if ($v['money100'] == 1) {
                array_push($amountArray, "100");
            }
            if ($v['money200'] == 1) {
                array_push($amountArray, "200");
            }
            if ($v['money300'] == 1) {
                array_push($amountArray, "300");
            }
            if ($v['money500'] == 1) {
                array_push($amountArray, "500");
            }
            if (count($amountArray) > 0) {
                $amountText = join(',', $amountArray);
            }
            
            $yunying = $v['yunying'];
            if ($v['yunying'] == "1") {
                $yunying = "移动";
            } else if ($v['yunying'] == "2") {
                $yunying = "联通";
            } else if ($v['yunying'] == "3") {
                $yunying = "电信";
            }
            $count_text=$v['count'];
            if($count_text==0){
                $count_text=1;
            }

        mylog('测试打印4  ', $amountText . '   ' . $yunying . '   ' . $count_text . '   ' . $daichonginfo['prov_text']);
            // $data = [
            //     'amount' => $money,
            //     'operator' => $daichonginfo['type'],
            // ];    我 注释  的 by lif
         //$amountText="1,2";
            $jsonStr='{"amount":"' . $amountText . '","operator_id":"' . $yunying . '","order_num":"' . $count_text . '","prov_code":"' . $daichonginfo['prov_text'] . '"}';

        mylog('测试打印5  ', $jsonStr);
        
            mylog('用户取单', json_encode($jsonStr));

            $this->userorderadd($v, $jsonStr, $daichonginfo['type']);
        }
    }

    private function userorderadd($user, $jsonStr, $yunying = 1)
    {
        
        $urls = explode(",", $user['token']); // 运营商 地区 金额
        $res = dc_action($urls[0], 'get', $user, $jsonStr);
        mylog('用户取单', json_encode([$user['token'], 'get', $user['user'], $jsonStr]));

        $responseJson = json_decode($res, true);
        mylog('用户取单返回', json_encode($responseJson));

        if ($responseJson['code'] == 0) {
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
                    mylog('添加订单', json_encode($list));
                    $this->sert($list, 1);
                }
            }
        }else{
            echo '无新的数据';
            return false;
        }

        // if ($resd['ret'] == 0) {
        //     if (!empty($resd['data'])) {
        //         $list['user'] = $user['id'];
        //         $list['order_id'] = $resd['data']['id'];
        //         $list['account'] = $resd['data']['mobile'];
        //         $list['denom'] = $resd['data']['amount']; //金额
        //         $list['createTime'] = time();
        //         $list['yunying'] = $yunying;
        //         $list['chargeTime'] = $resd['data']['timeout']; //超时时间
        //         $list['status'] = 5;


        //         $get = M('daichong_orders')->where(['order_id' => $resd['data']['id']])->find();
        //         if ($get) {
        //             M('daichong_orders')->where(['order_id' => $resd['data']['id']])->update($list);
        //         } else {

        //             M('daichong_orders')->insert($list);
        //             mylog('添加订单', json_encode($list));
        //             $this->sert($list, 1);
        //         }
        //     } else {
        //         echo '无新的数据';
        //         return false;
        //     }
        // }
    }

    /**
     * 设置订单成功
     * https://id4.cgtest.bolext.com/cg/api/TaskPub/Partner/UploadCert
     */
    public function setstaus()
    {
        $id = I('id');
        if ($id > 0) {
            $order = db('daichong_orders')->where(['order_id' => $id])->find();
            db('daichong_orders')->where(['order_id' => $id])->update(['uploadTime' => time(), 'status' => 6, 'type' => 2]);
            //$this->setordertype($order, 9, '成功');
            //$this->sert($id, 259);
            
            
           $aaa= $this->setordertype($order, 9, '成功');
           return $this->success($aaa);  //調試測試使用

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
        $rest = $this->http_get('http://mf.onetar.top/yrapi.php/index/recharge', $data);
        mylog('看看下单的结果 ', json_encode($rest));
        
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
            if ($result['errno'] == 0) {
                //return rjson(0, $result['errmsg'], $result['data']);
            } else {
                // return rjson(1, $result['errmsg'], $result['data']);
            }
        } else {
            // return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"]);
        }
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
            
            db('daichong_orders')->where(['order_id' => $id])->update(['uploadTime' => time(), 'status' => 9, 'type' => 1]);
            
        
           $this->setordertype($order, 8, '失败');
           //return $this->success($aaa);  //調試測試使用
        }

        return $this->success('操作成功!');
    }

    /**
     * @param $data
     * @param $status
     * @param string $msg
     * 上报订单 失败于成功
     */


    public function setordertype($datas, $status, $msg = '成功')
    {
// $redata = $this->post_porder($data['account'], $data['order_id'], $data['denom']);
        //   $orjson = json_decode($redata,true);
        //   $orjson = json_decode($redata,true);
        //$user = get_dc_user(2);
        $user = db('daichong_user')->where(['id' => $datas['user']])->find();
        // $data = [
        //     'id' => $datas['order_id'],
        //     'mobile' => $datas['account'],
        //     'result' => $status,
        //     'remark' => '充值-订单号' . $datas['yr_order_id'] . $msg,

        // ];
        
        mylog('上报订单信息' . $datas['order_id'], '返回：');

        if ($status == 8) {
            db('daichong_orders')->where(['order_id' => $datas['order_id']])->update(['uploadTime' => time(), 'status' => 9, 'type' => 1]);
        } else {
            db('daichong_orders')->where(['order_id' => $datas['order_id']])->update(['uploadTime' => time(), 'status' => 6, 'type' => 2]);
        }

        $jsonStr = '{"user_order_id":"' . $datas['order_id'] . '","status":"' . $status . '","rsp_info":"' . $msg . '"}';
        $urls = explode(",", $user['token']); // 运营商 地区 金额
        $res = dc_action($urls[1], 'status', $user, $jsonStr);

        $resd = json_decode($res, true);
        mylog('上报订单信息' . $datas['order_id'], $jsonStr . '返回：' . $res);
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
        //        {
        //            "action" : "report",  //接口名
        //		"token"  : "xxxx",   //除login接口外，需填写
        //		"flag"   ："SUNING", //渠道标识，如苏宁SUNING
        //		"data"   : {
        //            "id":"1231231",
        //			"mobile":"13800138000",
        //			"result":1,
        //			"context":"xxxxxxxxxxxx"
        //			"remark" : "下单成功，订单号xxx"
        //			}
        //	}


        mylog('开始下单 1  ' . $data['order_id'], json_encode($data) . '返回：' . $status);
        mylog('开始下单 2  ' . $data['order_id'], $data['account'] . '   ' . $data['order_id'] . '   ' .  $data['denom'] . '   ' .  $data['yunying']);
        $redata = $this->post_porder($data['account'], $data['order_id'], $data['denom'], $data['yunying']);
        $orjson = json_decode($redata, true);
        
        mylog('看看返回的啥 2  ' , $orjson['errmsg']);
        // $user = get_dc_user(2);
        //$user = db('daichong_user')->where(['id' => $data['user']])->find();
        /*
        $datas = [
            'id' => $data['order_id'],
            'mobile' => $data['account'],
            'result' => $status,
            'context' => $orjson['data']['order_number'],
            'remark' => '下单成功，订单号' . $orjson['data']['order_number'],

        ];
        */
        $res = "不需要上报report了，上报状态就好了" . '  ' . $orjson['errno'];//dc_action($user['token'], 'report', $user['zhuanqu'], $datas);
        

        //$resd = json_decode($res, true);
        mylog('上报订单充值信息' . $data['order_id'], json_encode($data) . '返回：' . $res);
        
        //根据返回的errno，如果是1，上报失败，如果是0，上报成功
        
        db('daichong_orders')->where(['order_id' => $data['order_id']])->update(['beizhu' => $orjson['errmsg']]);
        
        $order = db('daichong_orders')->where(['order_id' => $data['order_id']])->find();
        if($orjson['errno']==11){
           
            mylog('订单开始上报，' . $data['order_id'] , '  因为该订单 ' . $orjson['errmsg'] . ' ,所以订单上报状态为 失败');
           $aaa= $this->setordertype($order, 8, $orjson['errmsg']);
            mylog('订单开始上报，' . $data['order_id'] , '  完成' . json_encode($aaa));
        }
        
    }

    /**
     * @return mixed
     */
    public function deletes()
    {

        $id = I('id');
        if ($id > 0) {

            M('daichong_orders')->where(['id' => $id])->delete();
        }
        return $this->success('操作成功!');
    }

    public function deall()
    {


        M('daichong_orders')->where('id', '>', 0)->delete();
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
         $denomInfos['te'] = input('te');
         $denomInfos['prov_text'] = $prov_text;
         $demomInfos['count_text']=$countText;
         $te = input('te');
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
         $aaa = $this->get_order();
         $msg = [
             'code' => 1,
             'msg' => '开始接单成功！' . $aaa,
             'Start' => true,
             'set' => true,
         ];


         return ($msg);

        //end add by Lif at 2023-10-9


        // $denomInfos = [];
        // $user = get_dc_user(2);
        // $denomInfos['user'] = db('daichong_user')->where(['is_lex' => 2])->select();
        // $denomInfos['type'] = input('yunying');
        // $denomInfos['te'] = input('te');
        // $te = input('te');
        // if(empty($te)){
        //     $te = 0;
        // }
        // if ($user['money10'] == 1) {
        //     $denomInfos['money'][] = ['denom' => 10];
        // }
        // if ($user['money20'] == 1) {
        //     $denomInfos['money'][] = ['denom' => 20];
        // }
        // if ($user['money30'] == 1) {
        //     $denomInfos['money'][] = ['denom' => 30];
        // }
        // if ($user['money50'] == 1) {
        //     $denomInfos['money'][] = ['denom' => 50];
        // }
        // if ($user['money100'] == 1) {
        //     $denomInfos['money'][] = ['denom' => 100];
        // }
        // if ($user['money200'] == 1) {
        //     $denomInfos['money'][] = ['denom' => 200];
        // }
        // if ($user['money300'] == 1) {
        //     $denomInfos['money'][] = ['denom' => 300];
        // }
        // if ($user['money500'] == 1) {
        //     $denomInfos['money'][] = ['denom' => 500];
        // }
        // Cache::set('daichong', $denomInfos);
        // db('daichong_user')->where(['id' => $user['id']])->update(['type'=>1,'yunying'=> $denomInfos['type'] ,'te'=>$te]);
        // $this->userlogin();
        // $msg = [
        //     'code' => 1,
        //     'msg' => '开始接单成功！',
        //     'Start' => true,
        //     'set' => true,
        // ];


        // return ($msg);
    }


    public function add()
    {


        if (request()->isPost()) {
            $arr = $_POST;
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
        $list = M('daichong_user')->where(array('is_lex' => 2))->select();
        $this->assign('_listn', $usrlis['nouser']);
        $this->assign('_list', $list);
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
}
