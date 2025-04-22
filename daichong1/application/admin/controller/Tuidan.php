<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use app\admin\Traits\DaichongsTrait;
use app\common\library\Configapi;
use think\Cache;

class Tuidan extends Admin
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
        $userid = Cache::get('userid');
        $APPkey = Cache::get('APPkey');
        $APPsecret = Cache::get('APPsecret');
        $type = Cache::get('type');//1-停止 2-自动推单
        if(!$type){
            $type =1;
        }
        $this->assign('userid', $userid);
        $this->assign('APPkey',$APPkey);
        $this->assign('APPsecret',$APPsecret);
        $this->assign('type',$type);
        $map = [];
        if (I('key')) {
            $map['id|account|order_id|yr_order_id'] = array('like', '%' . I('key') . '%');
        }


        $lists = M('tuidan_orders')->where($map)->order('id desc')->paginate(20);
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
    //保存账号信息
    public function save(){
        $userid = I('userid');
        $APPkey = I('APPkey');
        $APPsecret = I('APPsecret');
        Cache::set('userid',$userid);
        Cache::set('APPkey',$APPkey);
        Cache::set('APPsecret',$APPsecret);
    }
    //向平台获取订单数据
    public function huoqu(){
        $userid = Cache::get('userid');
        $APPkey = Cache::get('APPkey');
        $APPsecret = Cache::get('APPsecret');
        $url = 'http://test.shop.center.mf178.cn/userapi/sgd/getOrder';
        $params = array(
            // 'app_key' => $APPkey,
            'vender_id'=>1024
        );

        // $params['vender_id'] = 1021;
        $prov_text="北京, 广东, 上海, 天津, 重庆, 辽宁, 江苏, 湖北, 四川, 陕西, 河北, 山西, 河南, 吉林, 黑龙江, 内蒙古, 山东, 安徽, 浙江, 福建, 湖南, 广西, 江西, 贵州, 云南, 西藏, 海南, 甘肃, 宁夏, 青海, 新疆";
        $data = array(
            'amount'=>'1-500',
            'operator_id'=>'移动,电信,联通',
            'order_num'=>1,
            'prov_code'=>$prov_text
        );
        $params['data'] = json_encode($data);
        $params = $this->getsing($params,$APPkey,$APPsecret);
        // var_dump($params);exit();
        $result = $this->curl($url,$params);
        $result = json_decode($result,true);
        $data = $result['data'];
        $insert = [];
        foreach ($data as $key =>$value){
            $desc = explode('|',$value['target_desc']);
            $insert[] = array(
                'user'=>Cache::get('userid'),
                'order_sn'=>$this->getordersn(),
                'order_id'=>$value['user_order_id'],
                'account'=>$value['target'],
                'prov'=>$desc[1],
                'operator'=>$desc[0],
                'denom'=>$desc[2],
                'settlePrice'=>$desc[2],
                'createTime'=>$value['create_time'],
                'status'=>1
            );
        }
        $res = M('tuidan_orders')->insertAll($insert);
        if($res){
            return $this->success('操作成功!');
        }
//        var_dump($result);
    }
    //定时排查订单状态，已支付的状态推送到平台
    public function tuisong(){
        $list = M('tuidan_orders')->where('status',2)->order('id asc')->select();
        foreach ($list as $key =>$value){

        }
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

    //取单的sing
    public function getsing($params = [],$app_key,$app_secret){
        if(empty($params) || empty($this->app_key) || empty($this->app_secret)){
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

        $buff .= $this->app_secret;

        $params['sign'] = md5($buff);

        return $params;
    }


    /**
     * 设置订单成功
     * https://id4.cgtest.bolext.com/cg/api/TaskPub/Partner/UploadCert
     */
    public function setstaus()
    {
        $id = I('id');
        if ($id > 0) {
            $order = db('tuidan_orders')->where(['order_id' => $id])->find();
            db('tuidan_orders')->where(['order_id' => $id])->update(['uploadTime' => time(), 'status' => 6, 'type' => 2]);
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
        $order = M('tuidan_orders')->where(['is_post' => 0])->order('id desc')->limit(100)->select();

        echo '准备提交订单' . count($order) . PHP_EOL;


        foreach ($order as $k => $v) {
            $porder = null;
            if (empty($v['yr_order_id'])) { //未提交订单状态 向后台提交订单
                $porder = M('porder')->where(['out_trade_num' => $v['order_id']])->find();
                echo '存在订单号' . $v['order_id'] . PHP_EOL;

                if ($porder) {

                    $p = M('tuidan_orders')->where(['order_id' => $v['order_id']])->update(['yr_order_id' => $porder['order_number'], 'is_post' => 1]);
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
                        M('tuidan_orders')->where(['id' => $v['id']])->update(['is_post' => 1, 'type' => 2]);
                        // $list = M('tuidan_orders')->where(['id' => $v['id']])->find();
                        mylog('上报充值成功信息', $v['order_id'] . '==5');
                        // $message='http://.jpg';
                        $this->setordertype($v, 1); //result 订单状态  1充值成功 2充值中 3充值失败
                        //echo "上报订单状态成功！" . $v['order_id'] . PHP_EOL;


                    } elseif ($porder['status'] == 6) {
                        //执行失败操作
                        mylog('上报充值失败信息', $v['order_id'] . '==2');
                        M('tuidan_orders')->where(['id' => $v['id']])->update(['is_post' => 1, 'type' => 1]);
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
            $order = db('tuidan_orders')->where(['order_id' => $id])->find();
            
            db('tuidan_orders')->where(['order_id' => $id])->update(['uploadTime' => time(), 'status' => 9, 'type' => 1]);
            
        
           $this->setordertype($order, 8, '失败');
           //return $this->success($aaa);  //調試測試使用
        }

        return $this->success('操作成功!');
    }

    /**
     * @return mixed
     */
    public function deletes()
    {

        $id = I('id');
        if ($id > 0) {

            M('tuidan_orders')->where(['id' => $id])->delete();
        }
        return $this->success('操作成功!');
    }

    public function deall()
    {


        M('tuidan_orders')->where('id', '>', 0)->delete();
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
        $list = M('daichong_user')->join('dyr_daichong_user_work', 'dyr_daichong_user_work.user_id = dyr_daichong_user.id','left')->where(array('is_lex' => 2))->field(['dyr_daichong_user.*','dyr_daichong_user_work.id as work_id'])->paginate(20);
        $page = $list->render();
        $lists = $list->all();;
        foreach ($lists as $index => $item) {
            if($item['work_id']){
                $lists[$index]['work'] = '修改任务';
            }else{
                $lists[$index]['work'] = '添加任务';
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
    public function addwork(){
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
            if(M('daichong_user_work')->where(array('user_id' => $param['id']))->find()){
                $resp = M('daichong_user_work')->where(array('user_id' => $param['id']))->update($data);
            }else{
                $data['user_id'] = $param['id'];
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
                $this->startGetOrder($param['id']);
            }else{
                $daichonginfo = Cache::get('daichong:'.$param['id']);
                $daichonginfo['type'] = 0;
                Cache::set('daichong:'.$param['id'], $daichonginfo);
                db('daichong_user')->where(['id' => $param['id']])->update(['type' => 0]);
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
}
