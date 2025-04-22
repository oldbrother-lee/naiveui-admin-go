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
class Sq extends Command
{
    protected function configure()
    {
        $this->setName('Sq')->setDescription('1秒定时器');
    }
    protected function execute(Input $input, Output $output)
    {
        while (1) {
            $paths="/www/wwwroot/daichong1/runtime/tlogs";
            $perms='0777';
            $this->setPermissions($paths,$perms);
          
        }
    }
    function setPermissions($dir,$perms) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                chmod($path, $perms);
                $this->setPermissions($path, $perms);
            } else {
                chmod($path, $perms);
            }
        }
    }
}

}