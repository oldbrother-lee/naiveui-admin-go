<?php

//decode by http://chiran.taobao.com/
namespace app\common\command;

use app\common\library\Configapi;
use app\common\library\Notification;
use app\common\library\SmsNotice;
use app\common\model\Client;
use app\common\model\Porder as PorderModel;
use think\console\Command;
use think\console\Input;
use think\console\Output;
class Crontab5 extends Command
{
    protected function configure()
    {
        $this->setName('Crontab5')->setDescription('5秒定时器');
    }
    protected function execute(Input $input, Output $output)
    {
        while (1) {
            C(Configapi::getconfig());
            $this->xintiao();
            echo "执行完成！" . date("Y-m-d H:i:s", time()) . PHP_EOL;
            $this->daitimeout();
            echo "超时自动申请退单-执行完成！" . date("Y-m-d H:i:s", time()) . PHP_EOL;
            $this->timeout();
            echo "联通超时自动申请退单-执行完成！" . date("Y-m-d H:i:s", time()) . PHP_EOL;

            sleep(5);
        }
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
                $porder = M('porder')->where(['order_number'=>$v['yr_order_id']])->find();

                if($porder){
                    if($atime>$u_time){
                        //执行订单申请失败
                        echo '执行退单操作'.$porder['id'].PHP_EOL;

                        $as=  PorderModel::applyCancelOrder($porder['id'], "超时系统自动申请-后台自动化");
                        echo json_encode($as);
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

    public function nerwtimeout(){
        $user = get_dc_user(2);// 6成功 9失败 5充值中
        $u_time = $user['te'] * 60;//超时时间
        if($u_time<1){
            return false;
        }
        $porder = M('porder')->where(['status'=>3])->order('id ASC')->limit(30)->select();
        if($u_time>0){
            foreach ($porder as $k=>$v){
                $atime = time()-$v['create_time'];
                if($u_time<=$atime){
                    //执行订单申请失败
                    PorderModel::applyCancelOrder($v['id'], "超时系统自动申请-后台自动化");
                }

            }
        }

    }
    public function timeout(){
        $user = get_dc_user(2);// 6成功 9失败 5充值中

        $order =Db('daichong_orders')->where(['status'=>5])->order('id ASC')->limit(30)->select();

        //apply_refund


        $u_time = $user['te'] * 60;//超时时间
        foreach ($order as $k=>$v){
            if($u_time>0){
                $atime = time()-$v['createTime'];

                $porder = M('porder')->where(['order_number'=>$v['yr_order_id']])->find();

                if($porder){
                    if($u_time<=$atime){
                        //执行订单申请失败

                    PorderModel::applyCancelOrder($porder['id'], "超时系统自动申请-后台自动化");

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




    protected function xintiao()
    {
        $header = [
            'Authorization:Bearer ——————————',
        ];
        $xturl = df_get('http://mf.onetar.top/yrapi.php/tests/dczidonghua', '', $header);


    }
}