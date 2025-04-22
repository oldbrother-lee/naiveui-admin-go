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
class Crontab1 extends Command
{
    protected function configure()
    {
        $this->setName('Crontab1')->setDescription('1秒定时器');
    }
    protected function execute(Input $input, Output $output)
    {
        while (1) {
            C(Configapi::getconfig());
            $this->xitu();
            echo  date('Y-m-d H:i:s').'希图'.PHP_EOL;
          //  $this->dier();
            //echo 'dier'.date('Y-m-d H:i:s').PHP_EOL;
            sleep(1);
        }
    }

    public function dier(){
        //http://115.126.57.143/yrapi.php/test/dczidonghua
        $header = [
            'Authorization:Bearer ——————————',
        ];
        $xturl = df_get('http://115.126.57.143/yrapi.php/tests/posttype', '', $header);
    }
    protected function xitu()
    {
        $header = [
            'Authorization:Bearer ——————————',
        ];
        $xturl = df_get('http://115.126.57.143/yrapi.php/test/xituorder', '', $header);
        //dump($xturl);

    }
}