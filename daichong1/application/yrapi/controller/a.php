<?php

//decode by http://chiran.taobao.com/
namespace app\yrapi\controller;

use app\common\library\Createlog;
use app\common\model\Client;
use app\common\model\Porder as PorderModel;
use app\common\model\Product as ProductModel;
use QL\QueryList;// 实例化 对象
class Test
{
    public function index()

    {
        die;
        $ql = new QueryList();
        $url = "http://115.126.57.143/yrapi.php/open/voucher/id/23085.html";

// 获取页面html内容

        $html = $ql->get($url)->getHtml();
        dump($html);


        die;
        echo $this->getWebTag ( 'id="img"', 'http://115.126.57.143/yrapi.php/open/voucher/id/23085.html', 'div' );
        die;
        $str =file_get_contents('http://115.126.57.143/yrapi.php/open/voucher/id/23085.html');

        dump($str);die;

        $pattern="/\<img src=\"(.+?)\"\>/";

        preg_match_all($pattern,$str,$match);

        $src=$match[1];

        dump($src);

        return djson(1, '欢迎访问');
        return djson(1, '欢迎访问');
    }

    /*
    * 参数说明: $tag_id:所要获取的元素Tag Id $url:所要获取页面的Url $tag:所要获取的标签 $data
    */
    public   function getWebTag($tag_id, $url = false, $tag = 'div', $data = false) {
        if ($url !== false) {
            $data = file_get_contents ( $url );
        }
        $charset_pos = stripos ( $data, 'charset' );
        if ($charset_pos) {
            if (stripos ( $data, 'utf-8', $charset_pos )) {
                $data = iconv ( 'utf-8', 'utf-8', $data );
            } else if (stripos ( $data, 'gb2312', $charset_pos )) {
                $data = iconv ( 'gb2312', 'utf-8', $data );
            } else if (stripos ( $data, 'gbk', $charset_pos )) {
                $data = iconv ( 'gbk', 'utf-8', $data );
            }
        }
        preg_match_all ( '/<' . $tag . '/i', $data, $pre_matches, PREG_OFFSET_CAPTURE ); // 获取所有div前缀
        preg_match_all ( '/<\/' . $tag . '/i', $data, $suf_matches, PREG_OFFSET_CAPTURE ); // 获取所有div后缀
        $hit = strpos ( $data, $tag_id );
        if ($hit == - 1)
            return false; // 未命中
        $divs = array (); // 合并所有div
        foreach ( $pre_matches [0] as $index => $pre_div ) {
            $divs [( int ) $pre_div [1]] = 'p';
            $divs [( int ) $suf_matches [0] [$index] [1]] = 's';
        }
        // 对div进行排序
        $sort = array_keys ( $divs );
        asort ( $sort );
        $count = count ( $pre_matches [0] );
        foreach ( $pre_matches [0] as $index => $pre_div ) {
            // <div $hit <div+1 时div被命中
            if (($pre_matches [0] [$index] [1] < $hit) && ($hit < $pre_matches [0] [$index + 1] [1])) {
                $deeper = 0;
                // 弹出被命中div前的div
                while ( array_shift ( $sort ) != $pre_matches [0] [$index] [1] && ($count --) )
                    continue;
                // 对剩余div进行匹配，若下一个为前缀，则向下一层，$deeper加1，
                // 否则后退一层，$deeper减1，$deeper为0则命中匹配，计算div长度
                foreach ( $sort as $key ) {
                    if ($divs [$key] == 'p')
                        $deeper ++;
                    else if ($deeper == 0) {
                        $length = $key - $pre_matches [0] [$index] [1];
                        break;
                    } else {
                        $deeper --;
                    }
                }
                $hitDivString = substr ( $data, $pre_matches [0] [$index] [1], $length ) . '</' . $tag . '>';
                break;
            }
        }
        return $hitDivString;
    }





    public function dczidonghua(){
        //$this->set_order();
        // $this->set_orders();
        //  $this->get_dczidonghua_order();

        $this->set_dczidonghua_order();
    }
    public function set_dczidonghua_order()
    {
        //先处理提交订单
        echo '订单出来开始工作:';
        $order = M('daichong_orders')->where(['is_post' => 0])->where('type','<',1)->order('id desc')->limit(100)->select();




        echo '准备提交订单'.count($order). PHP_EOL;



        foreach ($order as $k => $v) {
            $porder =null;
            if (empty($v['yr_order_id'])) {//未提交订单状态 向后台提交订单
                $porder = M('porder')->where(['out_trade_num' => $v['order_id']])->find();
                echo '存在订单号'.$v['order_id']. PHP_EOL;

                if ($porder) {

                    $p=   M('daichong_orders')->where(['order_id' => $v['order_id']])->update(['yr_order_id' => $porder['order_number']]);
                    if($p){
                        echo '更新成功'.$v['order_id']. PHP_EOL;
                    }else{
                        echo '更新失败'.$v['order_id']. PHP_EOL;
                    }
                }else{
                    // mylog('提交订单'.$v['order_id'],$v['account'].$v['denom']);
                    echo '提交订单'.$v['order_id']. PHP_EOL;
                    $this->post_daichong_porder($v['account'], $v['order_id'], $v['denom']);

                }
                echo 1;


            } else {
                echo 2;
                //查到订单的状态 就按照订单状态上报信息
                $porder = M('porder')->where(['order_number' => $v['yr_order_id']])->find();

                if ($porder) {
                    if ($porder['status'] == 4) {
                        //执行成功操作
                        M('daichong_orders')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>2]);
                        // $list = M('daichong_orders')->where(['id' => $v['id']])->find();
                        mylog('上报充值成功信息',$v['order_id'].'==5');
                        // $message='http://.jpg';
                        $this->setordertype($v, 1);//result 订单状态  1充值成功 2充值中 3充值失败
                        //echo "上报订单状态成功！" . $v['order_id'] . PHP_EOL;


                    } elseif ($porder['status'] == 6) {
                        //执行失败操作
                        mylog('上报充值失败信息',$v['order_id'].'==2');
                        M('daichong_orders')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>1]);
                        $this->setordertype($v, 3,'失败');
                        // echo "上报订单状态失败！" . $v['order_id'] . PHP_EOL;


                    }

                }

            }

        }
        return false;
    }
    /**
     * @param $data 订单
     * @param $status
     * @param string $msg
     * 上报订单 失败于成功
     */


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
            db('daichong_orders')->where(['order_id'=>$datas['order_id']])->update(['uploadTime'=>time(),'status'=>9,'type'=>1]);
        }else{
            db('daichong_orders')->where(['order_id'=>$datas['order_id']])->update(['uploadTime'=>time(),'status'=>6,'type'=>2]);
        }
        $res =    dc_action($user['token'],'status',$user['zhuanqu'],$data);

        $resd = json_decode($res,true);
        mylog('上报订单信息' . $datas['order_id'], json_encode($data) . '返回：' . $res);



    }
    protected function get_dczidonghua_order()
    {
        $user = get_dc_user(2);


        $s = 0;
        if ($user['money10'] == 1) {
            $user['money'] = 10;

        }
        if ($user['money20'] == 1) {
            $user['money'] = 20;

        }
        if ($user['money30'] == 1) {
            $user['money'] = 30;

        }
        if ($user['money50'] == 1) {
            $user['money'] = 50;
        }
        if ($user['money100'] == 1) {
            $user['money'] = 100;
        }
        if ($user['money200'] == 1) {
            $user['money'] = 200;
        }
        if ($user['money300'] == 1) {
            $user['money'] = 300;
        }
        if ($user['money500'] == 1) {
            $user['money'] = 500;
        }


        $data = [
            'amount' => $user['money'],
            'operator' => $user['yunying'],
        ];
        $res = dc_action($user['token'], 'get', $user['zhuanqu'], $data);

        $resd = json_decode($res, true);

        if ($resd['ret'] == 0) {
            if (!empty($resd['data'])) {
                $list['user'] = $user['id'];
                $list['order_id'] = $resd['data']['id'];
                $list['account'] = $resd['data']['mobile'];
                $list['denom'] = $resd['data']['amount'];//金额
                $list['createTime'] = time();
                $list['chargeTime'] = $resd['data']['timeout'];//超时时间
                $list['status'] = 5;

                $get = M('daichong_orders')->where(['order_id' => $resd['data']['id']])->find();
                if ($get) {
                    M('daichong_orders')->where(['order_id' => $resd['data']['id']])->update($list);

                } else {

                    M('daichong_orders')->insert($list);
                    $this->daichong_sdd($list, 1);
                    mylog('添加订单', json_encode($list));

                }
            } else {
                echo '无新的数据';
                return false;
            }

        }

    }



    public function daichong_sdd($data, $status, $message = '')
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


        $redata = $this->post_daichong_porder($data['account'], $data['order_id'], $data['denom']);
        $orjson = json_decode($redata,true);
        $user = get_dc_user(2);

        $data = [
            'id' => $data['order_id'],
            'mobile' => $data['account'],
            'result'=>$status,
            'context'=>$orjson['data']['order_number'],
            'remark'=>'下单成功，订单号'.$orjson['data']['order_number'],

        ];
        $res =    dc_action($user['token'],'report',$user['zhuanqu'],$data);

        $resd = json_decode($res,true);
        mylog('上报订单充值信息' . $data['order_id'], json_encode($data) . '返回：' . $res);



    }











    public function zidonghua(){

        $this->set_order();
        $this->set_orders();
        //   $this->get_order();
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
            // mylog('接收到数据',json_encode($data));
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
                        //  mylog('添加订单',json_encode($list));
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

    /**
     * @return bool 处理 已经提交但是没有 更新状态的 订单 有可能是卡住了
     */
    public function set_orders()
    {
        //先处理提交订单
        echo '订单查询type0开始工作:';
        $order = M('daichong_order')->where(['type' => 0])->order('id desc')->limit(100)->select();

        echo '准备提交订单'.count($order). PHP_EOL;

        foreach ($order as $k => $v) {
            $porder =null;
            if (empty($v['yr_order_id'])) {//未提交订单状态 向后台提交订单
                $porder = M('porder')
                    ->where(['out_trade_num' => $v['order_id']])

                    ->find();
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
                $porder = M('porder')
                    ->where(['order_number' => $v['yr_order_id']])
                    //   ->where('base','<>',null)
                    ->find();
                echo 'id'.$porder['order_number'];
                if ($porder) {
                    if ($porder['status'] == 4) {
                        echo '上报充值成功信息'.$porder['status'];
                        //执行成功操作
                        M('daichong_order')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>2]);
                        $oss = M('porder')
                            ->where(['order_number' => $v['yr_order_id']])
                            ->where('base','<>',null)
                            ->find();
                        if($oss){
                            $this->setimg($v['order_id'],$oss['base']);

                            $this->sert1($v['order_id'], 259);
                        }

                        //echo "上报订单状态成功！" . $v['order_id'] . PHP_EOL;


                    } elseif ($porder['status'] == 6) {
                        //执行失败操作
                        // mylog('上报充值失败信息',$v['order_id'].'==2');
                        echo '上报充值失败信息'.$porder['status'];
                        M('daichong_order')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>1]);
                        $this->sert1($v['order_id'], 2);
                        // echo "上报订单状态失败！" . $v['order_id'] . PHP_EOL;


                    }else{
                        echo '充值中信息'.$porder['status'];
                        //  M('daichong_order')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>0]);
                        // mylog('上报充值失败信息',$v['order_id'].'==2');
                    }

                }

            }

        }
        return false;
    }

    /**
     * @return bool处理未提交订单
     */
    public function set_order()
    {
        //先处理提交订单
        echo '订单出来开始工作:';
        $order = M('daichong_order')->where(['is_post' => 0])->order('id desc')->limit(100)->select();

        echo '准备提交订单'.count($order). PHP_EOL;



        foreach ($order as $k => $v) {
            $porder =null;
            if (empty($v['yr_order_id'])) {//未提交订单状态 向后台提交订单
                $porder = M('porder')
                    ->where(['out_trade_num' => $v['order_id']])

                    ->find();
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
                $porder = M('porder')
                    ->where(['order_number' => $v['yr_order_id']])

                    ->find();

                if ($porder) {
                    if ($porder['status'] == 4) {
                        //执行成功操作
                        M('daichong_order')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>2]);
                        $oss = M('porder')
                            ->where(['order_number' => $v['yr_order_id']])
                            ->where('base','<>',null)
                            ->find();
                        if($oss){
                            $this->setimg($v['order_id'],$oss['base']);

                            $this->sert1($v['order_id'], 259);
                        }

                        //echo "上报订单状态成功！" . $v['order_id'] . PHP_EOL;


                    } elseif ($porder['status'] == 6) {
                        //执行失败操作
                        //  mylog('上报充值失败信息',$v['order_id'].'==2');
                        M('daichong_order')->where(['id' => $v['id']])->update(['is_post' => 1,'type'=>1]);
                        $this->sert1($v['order_id'], 2);
                        // echo "上报订单状态失败！" . $v['order_id'] . PHP_EOL;


                    }

                }

            }

        }
        return false;
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

        //mylog('手动上报订单信息' . $id, json_encode($data) . '返回：' . $xturl);
        if ($xturl != 'true') {
            get_dc_token();
            $this->sert($id, $status);
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

    public function post_daichong_porder($mobile, $out_trade_num, $denom)
    {
        $product = M('product')->where(['voucher_name' => $denom,'isp'=>1,'cate_id'=>16, 'added' => 1, 'is_del' => 0])->find();
        // dump($product);
        $data = [
            "userid" => 3,
            "mobile" => $mobile,
            "out_trade_num" => $out_trade_num,
            "product_id" => $product['id'],
            "notify_url" => 'http://m.bnai.com',
        ];
        $data['sign'] = $this->sign($data);
        //dump($data);die;
        return $this->http_get('http://115.126.57.143/yrapi.php/index/recharge', $data);
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
        $product = M('product')->where(['voucher_name' => $denom,'cate_id'=>,'isp'=>3, 'added' => 1, 'is_del' => 0])->find();

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
    public function setimg($id, $img)
    {

        $url = 'https://www.xitupt.com/cg/api/TaskPub/Partner/UploadCert';
        $data = [
            'id' => $id,
            'imgStr' => $img,
        ];
        $user = get_dc_user();
        $header = [
            'Authorization:Bearer ' . $user['token'],
            'content-type: application/json'
        ];
        //   echo "上报订单" . $id .'='.json_encode($data). PHP_EOL;

        $xturl = df_post($url, $data, $header);

        // mylog('上报信息post',$url.'---'.json_encode($data).'---'.json_encode($header));
        // mylog('上报返回','---'.json_encode($xturl));
        if ($xturl != 'true') {
            //   mylog('上报返回不为true再次提交','---'.json_encode($xturl).'--'.$id.'--'.$status);
            //  get_dc_token();
            // $this->sert($id, $status);


        }else{

            //   echo "上报订单" . $id .'='.json_encode($data). PHP_EOL;

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

        // mylog('上报信息post',$url.'---'.json_encode($data).'---'.json_encode($header));
        // mylog('上报返回','---'.json_encode($xturl));
        if ($xturl != 'true') {
            //   mylog('上报返回不为true再次提交','---'.json_encode($xturl).'--'.$id.'--'.$status);
            //  get_dc_token();
            // $this->sert($id, $status);


        }else{

            //   echo "上报订单" . $id .'='.json_encode($data). PHP_EOL;

        }

    }




























    /**
     *
     * 请求地址：https://id4.cgtest.bolext.com/uc/connect/token
    请求方式：post
    Content-type：application/x-www-form-urlencoded
    参数说明：
    参数名称	含义	是否必传	说明
    grant_type	授权类型	是	固定值：password
    client_id	客户端id	是	联系管理员获取
    client_secret	客户端密钥	是	联系管理员获取
    username	登录名	是	联系管理员获取
    password	登录密码	是	联系管理员获取

     */

    public function daifulogin(){
        $data = [
            'grant_type'=>'password',
            'client_id'=>'dc_client',
            'client_secret'=>'b23009b84462c77aa4182c47f2a474a6',
            'username'=>'18635637493',
            'password'=>'939429',
        ];


        $info = file_get_contents('https://id4.cgtest.bolext.com/uc/connect/token?'.http_build_query($data));
        dump($info);
        die;

        //  $this->http_post_data('https://id4.cgtest.bolext.com/uc/connect/token?'.http_build_query($data),$data);
    }



    /**
     * get请求
     */
    private function http_post_data($url, $param)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $strPOST = http_build_query($param);
        }


        dump($url);
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, false);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 90);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        curl_setopt($oCurl, CURLOPT_HEADER, 0);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, ["ContentType:application/x-www-form-urlencoded;charset=utf-8"]);
        $sContent = curl_exec($oCurl);

        dump($sContent);die;
        $aStatus = curl_getinfo($oCurl);
        $curl_err = curl_error($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            $result = json_decode($sContent, true);
            dump($result);
            return rjson(0, $result['szRtnCode'], $result);

        } else {
            return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"] . 'curl err:'.$curl_err);
        }
    }














    public function recharge()
    {
        $mobile = trim(I('mobile'));
        $product_id = trim(I('product_id'));
        $out_trade_num = trim(I('out_trade_num'));
        $notify_url = trim(I('notify_url'));
        $area = trim(I('area'));
        $id_card_no = trim(I('id_card_no'));
        $city = trim(I('city'));
        $amount = I('amount');
        $price = I('price');
        $ytype = I('?ytype') ? trim(I('ytype')) : 1;
        $param1 = trim(I('param1'));
        $param2 = trim(I('param2'));
        $param3 = trim(I('param3'));
        $t1 = microtime(true);
        if ($reod = M('porder')->where(array('out_trade_num' => $out_trade_num, 'status' => array('not in', array(7)), 'customer_id' => $this->customer['id']))->find()) {
            return djson(1, '已经存在相同商户订单号的订单');
        }
        $res = PorderModel::createOrder($mobile, $product_id, array('prov' => $area, 'city' => $city, 'ytype' => $ytype, 'id_card_no' => $id_card_no, 'param1' => $param1, 'param2' => $param2, 'param3' => $param3), $this->customer['id'], Client::CLIENT_API, '', $out_trade_num, $amount, $price);

        if ($res['errno'] != 0) {
            return djson($res['errno'], $res['errmsg'], $res['data']);
        }

        $aid = $res['data'];
        queue('app\\queue\\job\\Work@agentApiPayPorder', array('porder_id' => $aid, 'customer_id' => $this->customer['id'], 'notify_url' => $notify_url));

        $porder = M('porder')->where(array('id' => $aid))->field("id,order_number,mobile,product_id,total_price,create_time,guishu,title,out_trade_num")->find();

        $t2 = microtime(true);

        Createlog::porderLog($aid, "创建订单耗时：" . round($t2 - $t1, 3) . 's');
        return djson(0, "提交成功", $porder);
    }
    public function user()
    {
        return djson(0, "ok", array('id' => $this->customer['id'], 'username' => $this->customer['username'], 'balance' => $this->customer['balance']));
    }
    public function check()
    {
        $out_trade_nums = I('out_trade_nums');
        $porder = M('porder')->where(array('customer_id' => $this->customer['id'], 'out_trade_num' => array('in', $out_trade_nums), 'is_apart' => array('in', '0,2'), 'is_del' => 0))->field("id,order_number,status,out_trade_num,create_time,mobile,product_id,charge_amount,charge_kami,isp,product_name,finish_time,remark")->select();
        foreach ($porder as &$vo) {
            $state = PorderModel::getState($vo['status']);
            $vo['state'] = $state;
            $vo['voucher'] = PorderModel::getVoucherUrl($vo['id'], $state);
            C('IS_SHOW_CLIENT_REMARK') != 1 && ($vo['remark'] = "");
        }
        return djson(0, 'ok', $porder);
    }
    public function product()
    {
        $map['p.is_del'] = 0;
        $map['p.added'] = 1;
        $map['p.show_style'] = array('in', '1,3');
        I('type') && ($map['p.type'] = I('type'));
        I('cate_id') && ($map['p.cate_id'] = I('cate_id'));
        $resdata = ProductModel::getProducts($map, $this->customer['id']);
        $lists = $resdata['data'];
        return djson(0, 'ok', $lists);
    }
    public function typecate()
    {
        $types = M('product_type')->where(array('status' => 1))->order('sort asc,id asc')->field('id,type_name')->select();
        foreach ($types as &$item) {
            $item['cate'] = M('product_cate')->where(array('type' => $item['id']))->order('sort asc,id asc')->field('id,cate,type')->select();
        }
        return djson(0, 'ok', $types);
    }
    public function elecity()
    {
        $lists = M('electricity_city')->where(array('is_del' => 0, 'pid' => 0))->order('initial asc,sort asc')->field('id,city_name,sort,initial,need_ytype,need_city,pid')->select();
        foreach ($lists as &$v) {
            if ($v['need_city']) {
                $v['city'] = M('electricity_city')->where(array('pid' => $v['id'], 'is_del' => 0))->order('sort asc,city_name asc')->field('id,city_name,sort,initial,pid')->select();
            } else {
                $v['city'] = array();
            }
        }
        return djson(0, 'ok', $lists);
    }




    public function gettest(){

        $json = '{"account":"13400526667","clientOrderId":"CZH22120548771A01N3","clientUsername":"20042","orderId":"22120519401470916456","sign":"e0b439222e219781b86439c00e36cb05","status":0,"voucher":""}';
        $I5pvPB4 = json_decode($json,true);
//{"account":"13400526667","clientOrderId":"CZH22120548771A01N3","clientUsername":"20042","orderId":"22120519401470916456","sign":"e0b439222e219781b86439c00e36cb05","status":0,"voucher":""}

//【参数：{"amount":"200.00","msg":"success","orderid":"1536618426647027","proof":"0e92f2310f3fe47edcd1479369faa879","rechargeno":15665391000,"status":88,"times":1669090536,"trade_id":"CZH22112214881A00N1","sign":"b5f1dec3a0409bcad08c80a80201c1b4","voucher":"110103308082211221217010830494"}】

        if(!empty($I5pvPB4)){
            if($I5pvPB4['status']=='2'){

            }else{

                echo 2;
            }
            echo 1;
        }

        die;




        $mobile='90590465027';
        $out_trade_num='CZH22112925649A00N1';
        $price='100';
        //   $teltype='';

        $data = [
            "userid" => '10002943',
            "mobile" => $mobile,
            "sporderid" => $out_trade_num,
            "price" => $price,
            // "paytype" => $teltype,
            "productid" =>'30000005192',//台商品编号(向平台索取)。 全国类商品需填写商品编号，分省商品时为空字符串，为空时根据号码自动判断运营商。充流量时填写对应流量商品编号。
            "num" => 1,
//
            "spordertime" => date("YmdHis",time()),
        ];
//
//
//sign=MD5(userid=xxxx&productid=xxxxxxx&price=xxxx&num=xxx&mobile=xxxxx&spordertime=xxxxxxx&sporderid=xxxxx&key=xxxxxxx)


        $data['sign']=MD5('userid='.$data['userid'].'&productid='.$data['productid'].'&price='.$data['price'].'&num='.$data['num'].'&mobile='.$data['mobile'].'&spordertime='.$data['spordertime'].'&sporderid='.$data['sporderid'].'&key=5YNEkhEESSDYe2yCdchzFC83RYjNJ2Ke');
        $data['back_url']= 'https://huafei.xishiyuan.com.cn/api.php/apinotify/Onlinepay';
        echo '请求数据：';
        var_dump($data);
        return $this->urlencoded_post('http://47.106.64.208:9086/onlinepay.do',$data);

        // $mobile='90590465027';
        //   $out_trade_num='CZH22112925649A00N1';
        //   $price='100';
        //   $teltype='';

        //  $data = [
        //     "userid" => '10002943',
        //     "account" => ['userid'],
        //     "sporderid" => $out_trade_num,
        //  "price" => $price,
        // "paytype" => $teltype,
        //     "productid" =>'30000005192',//台商品编号(向平台索取)。 全国类商品需填写商品编号，分省商品时为空字符串，为空时根据号码自动判断运营商。充流量时填写对应流量商品编号。
        //    "num" => 1,

        //     "spordertime" => date("YmdHis",time()),
        //  ];



        // $data['sign']=MD5('userid='.$data['userid'].'&productid='.$data['productid'].'&num='.$data['num'].'&account='.$data['account'].'&spordertime='.$data['spordertime'].'&sporderid='.$data['sporderid'].'&key=5YNEkhEESSDYe2yCdchzFC83RYjNJ2Ke');
        //  $data['back_url']= 'https://huafei.xishiyuan.com.cn/api.php/apinotify/Onlinepay';
        //  echo '请求数据：';
        //   var_dump($data);
        //   return $this->http_post('http://47.106.64.208:9086/gameonlinepay.do',$data);



    }
    public function getjianche(){





        $data = [
            "app_key" => 'BCAhHpen3f4XQ+ntCGbvcHBsqPhceLXOORfWWu335sjtfX6iuSkmjR5kf9sj/Ouc',

            "method" =>'fulu.order.info.get',
            'timestamp'=>date('Y-m-d H:i:s',time()),
            'version'=>'2.0',
            'format'=>'json',
            'charset'=>'utf-8',
            'sign_type'=>'md5',
            'app_auth_token'=>'',
        ];

        $biz_content = [

            'customer_order_no'=>'CZH22113028602A00N1'
        ];
        $data['biz_content'] = json_encode($biz_content);

        //$sig = hash_hmac('sha256', $string, $secret)
        //  $string = $this->getSortParams($data);
        $data['sign'] =$this->getSign($data,'603f461f4e7247e0911e093ae7dfbea4');
        //  Createlog::porderLog($param['logid'],'加密串:'.$string.'&key='.$this->apikey);
        //  Createlog::porderLog($order['id'],'订单自检请求数据:'.json_encode($data));
        //{"order_id":"22113024595794711936","charge_finish_time":"2022-11-30 20:14:15","customer_order_no":"CZH22113028634A00N1","order_status":"success","recharge_description":"充值成功","product_id":"18221081","price":"46.3000","buy_num":"1","operator_serial_number":"asix110103308202211302013270830661","sign":"90f1941940b0be9b245886d634d2ce58"}

        var_dump($data);



        $ress =  $this->http_posts('https://openapi.fulu.com/api/getway',$data);

        $res = $ress['data']['result'];

        $res = json_decode($res, true);



        // $res = json_decode($ress, true);


        print_r($res);die;

    }




    /**
     * php签名方法
     */
    public function getSign($Parameters,$key,$logid=null)
    {
        //签名步骤一：把字典json序列化
        $json = json_encode( $Parameters, 320 );
        //签名步骤二：转化为数组
        $jsonArr = $this->mb_str_split( $json );
        //签名步骤三：排序
        sort( $jsonArr );
        //签名步骤四：转化为字符串
        $string = implode( '', $jsonArr );
        //签名步骤五：在string后加入secret
        $string = $string .$key;

        if($logid>1){
            Createlog::porderLog($logid,'加密串原版:'.$string);
        }

        //签名步骤六：MD5加密
        $result_ = strtolower( md5( $string ) );
        return $result_;
    }
    /**
     * 可将字符串中中文拆分成字符数组
     */
    public  function mb_str_split($str){
        return preg_split('/(?<!^)(?!$)/u', $str );
    }






    /**
     * get请求
     */
    private function http_posts($url, $param,$logid=null)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1);
        }
//        if (is_string($param)) {
//            $strPOST = $param;
//        } else {
//            $strPOST = http_build_query($param);
//        }



        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 90);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($param));
        curl_setopt($oCurl, CURLOPT_HEADER, 0);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, ["Content-type: application/json;charset=utf-8"]);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        $curl_err = curl_error($oCurl);
        curl_close($oCurl);
        if($logid>1){
            Createlog::porderLog($logid,'返回:'.$sContent);
        }




        if (intval($aStatus["http_code"]) == 200) {
            $result = json_decode($sContent, true);


            //  $I5pvPB4 = 'curl_content:'.$param['external_orderno'];
            // Createlog::porderLog($I5pvPB4,json_encode($sContent));


            //</pre>{"code":"200","res":{"result":"error","date":"1666772832633","string":"请激活商户"}}

            if($result['code']==0){
                if($result['message']=='接口调用成功'){
                    return rjson(0, 100, $result);
                }else{
                    return rjson(1, 100, $result);

                }

            }else{
                return rjson(0, 100, $result);
            }




//            if (isset($result['nRtn']) && $result['nRtn'] == 0) {
//                return rjson(0, $result['szRtnCode'], $result);
//            } else {
//                return rjson(1, $result['szRtnCode'], $result);
//            }
        } else {
            return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"] . 'curl err:'.$curl_err);
        }
    }
    public  function urlencoded_post($url, $data = NULL){

        $postUrl = $url;
        $postData = $data;
        $postData = http_build_query($postData);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $postUrl);
        curl_setopt($curl, CURLOPT_USERAGENT,'Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded;charset=gb2312'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $sContent = curl_exec($curl);

        print_r($postData);
        curl_close($curl);
        $xml = simplexml_load_string($sContent);
        var_dump($xml);die;
        // return json_decode($r, true);
    }
    /**
     * get请求
     */
    private function http_post($url, $param,$logid=null)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1);
        }
//        if (is_string($param)) {
//            $strPOST = $param;
//        } else {
//            $strPOST = http_build_query($param);
//        }
        print_r($param);
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 90);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $param);
        //curl_setopt($oCurl, CURLOPT_HEADER, 0);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, ["Content-Type:application/x-www-form-urlencoded;charset=gb2312"]);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        $curl_err = curl_error($oCurl);
        curl_close($oCurl);



        //load the xml string using simplexml
        $xml = simplexml_load_string($sContent);
        var_dump($xml);die;
        //   if($logid>1){
        //    Createlog::porderLog($logid,'返回:'.json_encode($xml));
        //   }
        // if($xml->resultno==5012){
        //echo 1;
        // }
        var_dump($xml->resultno);die;
        if (intval($aStatus["http_code"]) == 200) {


            $result = json_decode($sContent, true);
            if (isset($result['nRtn']) && $result['nRtn'] == 0) {
                return rjson(0, $result['szRtnCode'], $result);
            } else {
                return rjson(1, $result['szRtnCode'], $result);
            }



        } else {
            return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"] . 'curl err:'.$curl_err);
        }
    }
}