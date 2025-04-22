<?php

namespace app\common\command;

use app\admin\Traits\DaichongsTrait;
use app\admin\Traits\DaichongTrait;
use app\common\library\Configapi;
use app\common\library\Notification;
use app\common\library\SmsNotice;
use app\common\model\Client;
use app\common\model\Porder as PorderModel;
use think\Cache;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;

class UploadToken extends Command
{
//    use DaichongsTrait;
    protected function configure()
    {
        $this->setName('uploadToken')->setDescription('上传日志');
    }

    protected function execute(Input $input, Output $output)
    {
        while(1){
            if(Cache::get('upload_token_log_open')){
                $this->main();
            }
            sleep(60);
        }
    }
    public function main(){
        $list = M('daichong_order o')
            ->join('dyr_upload_token_log log', 'log.order_id = o.order_id', 'left')
            ->join('dyr_porder porder', 'porder.out_trade_num = o.order_id', 'left')
            ->whereNull('log.id')->where('porder.status', 4)
            ->where('o.status', 6)->where('o.is_post', 1)->field('o.*')->select();
        $times = Cache::get('upload_token_log_times');
        foreach ($list as $index => $item) {
            $porder = M('porder')->where(['order_number' => $item['yr_order_id']])->find();
            var_dump($porder);
            if(!$porder){
                continue;
            }
            if(!$porder['base']){
                $file_name = time().$porder['id'].'.png';
                exec("wkhtmltoimage http://115.126.57.143/yrapi.php/open/voucher/id/{$porder['id']}.html ./public/uploads/{$file_name}");
                $file_path = './public/uploads/'.$file_name;
                // 创建一个新的空白画布，大小与原始图像相同
                list($width, $height) = getimagesize($file_path);
                
                $originalImage = imagecreatefrompng($file_path);
                $endY = $height;
                $startY = 0;
                $startX = $endX = floor($width/2);
                for ($y = 0; $y < $height; ++$y) {
                    for ($x = 0; $x < $width; ++$x) {
                        // 获取当前像素点的RGB值
                        $rgbColor = imagecolorsforindex($originalImage, imagecolorat($originalImage, $x, $y));
                        // 判断该像素点是否为白色
                        if (!(($rgbColor['red'] == 255) && ($rgbColor['green'] == 255) && ($rgbColor['blue'] == 255))) {
                            // 更新起始和结束坐标
                            if ($endX < $x) {
                                $endX = $x;
                            } 
                            if ($startX > $x) {
                                $startX = $x;
                            }
                        }
                    }
                }
                $newCanvas = imagecreatetruecolor($endX - $startX, $height);
                imagecopyresampled($newCanvas, $originalImage, 0, 0, $startX, $startY, $endX - $startX, $height, $endX - $startX, $height);
                imagepng($newCanvas, "./public/uploads/{$file_name}");
                echo "http://115.126.57.143/yrapi.php/open/voucher/id/{$porder['id']}.html";
                $imageData = file_get_contents("./public/uploads/{$file_name}");
                $base64Image = base64_encode($imageData);
                @unlink("./public/uploads/{$file_name}");
                M('porder')->where(['order_number' => $item['yr_order_id']])->update(['base' => $base64Image]);
                $porder['base'] = $base64Image;
            }
            Db::startTrans();
            if(!M('upload_token_log')->where('order_id', $item['order_id'])->find()){
                $data = [
                    'status' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'order_id' => $item['order_id'],
                    'times' => $times
                ];
                $log_id = M('upload_token_log')->insertGetId($data);
                $json_arr = [];
                for($i = $times; $i > 0; $i --){
                    M('upload_token_log')->where('id',$log_id)->update(['status' => 1]);
                    $resp = $this->upload($item['order_id'], $porder['base'],  $times);
                    if($resp){
                        echo '订单id'.$item['order_id'].'成功'.PHP_EOL;
                        $json_arr[] = ['第'.($times - $i +1).'次上传成功'];
                    }else{
                        echo '订单id'.$item['order_id'].'失败'.PHP_EOL;
                        $json_arr[] = ['第'.($times - $i +1).'次上传失败'];
                    }
                }
                M('upload_token_log')->where('id',$log_id)->update(['json_str' => json_encode($json_arr), 'status' => 2]);
            }
            Db::commit();
        }
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
        if($xturl){
            return true;
        }else{
            get_dc_token();
            return false;
        }
    }
}