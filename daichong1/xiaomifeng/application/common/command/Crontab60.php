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
class Crontab60 extends Command
{
	protected function configure()
	{
		$this->setName('Crontab60')->setDescription('60秒定时器');
	}
	protected function execute(Input $input, Output $output)
	{
		while (1) {
			C(Configapi::getconfig());
			//$this->set_order();
          //  $this->get_order();
			Porder::delayTimeOrderSub();//处理列队订单
			$this->notification();  //回调处理
			SmsNotice::agentBlalow(); //代理 短息通知
			//Porder::timeOutCancelOrder(); //时间超时 系统取消订单
			Porder::apiSelfCheck();   //自动检查订单 查询订单状态
			Porder::jiemaCheckOrder();//接码检查订单
			SmsNotice::porderSusNotice(); //订单处理 回调ixni

            $this->dier();
			echo "执行完成！" . date("Y-m-d H:i:s", time()) . PHP_EOL;
			sleep(15);
		}
	}



    public function dier(){
        //http://mf.onetar.top/yrapi.php/test/dczidonghua
        $header = [
            'Authorization:Bearer ——————————',
        ];
        $xturl = df_get('http://mf.onetar.top/yrapi.php/tests/posttype', '', $header);
    }



	//apiSelfCheck
	private function notification()
	{
		$lists = M('porder')->where(array('status' => array('in', '4,5,6,13'), 'client' => Client::CLIENT_API, 'is_notification' => 0, 'notification_num' => array('elt', 4), 'finish_time' => array('elt', time() - 60 * 5)))->field("id,status,notification_num,notification_time")->select();
		foreach ($lists as $k => $v) {
			if ($v['notification_num'] > 0 && $v['notification_time'] > time() - $v['notification_num'] * $v['notification_num'] * 60) {
				continue;
			}
			if ($v['status'] == 4) {
				Notification::rechargeSus($v['id']);
			} elseif ($v['status'] == 13) {
				Notification::rechargePart($v['id']);
			} else {
				Notification::rechargeFail($v['id']);
			}
		}
	}
}