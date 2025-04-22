<?php

namespace app\common\command;

use app\common\library\Configapi;
use think\Cache;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\admin\Traits\DaichongsTrait;
class Sbfail extends Command
{
    use DaichongsTrait;
    protected function configure()
    {
        $this->setName('Sbfail')->setDescription('5秒判断拉单成功但是没有派发的全部上报失败');
    }
    protected function execute(Input $input, Output $output)
    {
        while (1) {
            C(Configapi::getconfig());
            $this->setfail();
            sleep(5);
        }
    }
    public function setfail()
    {
        $list = M('daichong_orders')->where('status',5)->order('id asc')->select();
        foreach ($list as $key =>$value){
                $num = M('porder')->where('out_trade_num',$value['yr_order_id'])->count();
                echo($num);
                if($num == 0){
                    $order = db('daichong_orders')->where(['id' => $value['id']])->find();
                    db('daichong_orders')->where(['id' => $value['id']])->update(['uploadTime' => time(), 'status' => 9, 'type' => 1]);
                    echo('执行上报失败');
                    $this->setordertype($order, 8, '失败');
                }
        }
    }
}