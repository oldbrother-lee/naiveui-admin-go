<?php

//decode by http://chiran.taobao.com/
namespace app\yrapi\controller;

class Open extends \app\common\controller\Base
{
    function _base()
    {
    }

    public function getimg(){
        //自动访问没有凭证的订单
        $allconfig = M('voucher_config')->where(array('status' => 1))->select();
      //  dump(array('in', array_column($allconfig, 'type_id')));die;
        $order =M('porder')
            ->where(['base'=>null])
            ->where(['status'=>4,'type' => array('in', array_column($allconfig, 'type_id'))])
            ->order('id asc')->find();



        $uid = [];
     //  dump($order);die;
        return $order['id'];
    }
//模拟请求数据

 public  function request($url,$postfields=null,$cookie_jar=null,$referer=null){

        $ch = curl_init();

        $options = array(CURLOPT_URL => $url,

            CURLOPT_HEADER => 0,

            CURLOPT_NOBODY => 0,

            CURLOPT_PORT => 80,

            CURLOPT_POST => 1,

            CURLOPT_POSTFIELDS => $postfields,

            CURLOPT_RETURNTRANSFER => 1,

            CURLOPT_FOLLOWLOCATION => 1,

            CURLOPT_COOKIEJAR => $cookie_jar,

            CURLOPT_COOKIEFILE => $cookie_jar,

            CURLOPT_REFERER => $referer

        );

        curl_setopt_array($ch, $options);

        $code = curl_exec($ch);

        curl_close($ch);

        return $code;

    }

    public function imgsrc()
    {

        $id = I('id');
        $base = I('data');
        $poder = M('porder')->where(['id' => $id])->find();
        mylog(date('Y-m-d H:i:s').'-', '图请求', date('H').'img');
        if (!$poder) {
            echo "未获得凭证";
            exit;
        } else {
            $daichongorder = M('porder')->where(['id' => $id])->update(['base' => $base]);
            return true;

        }


    }

    public function addText()
    {
        // 获取图片路径和文字
        $image ='http://mf.onetar.top/uploads/20210826/fdf9461af7efedfbe46b55df4cc78649.jpg';// request()->param('image');
        $text = request()->param('text');
        $font =1;// request()->param('font');
        $size =120;// request()->param('size');
        $color ='#fff';// request()->param('color');
        $x = 120;//request()->param('x');
        $y =120;// request()->param('y');

        // 处理图片并添加文字
        $image = imagecreatefromjpeg($image);
        $color = imagecolorallocate($image, hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2)));

        $font = __DIR__ . '/fonts/' . $font;
        $size = intval($size);

        imagettftext($image, $size, 0, $x, $y, $color, '', $text);

        // 输出图片
        header('Content-Type: image/jpeg');
        imagejpeg($image);
        imagedestroy($image);
    }







    public function voucher()
    {
        $allconfig = M('voucher_config')->where(array('status' => 1))->select();
        if (!$allconfig) {
            echo "未获得凭证";
            exit;
        }
        // dump($allconfig);
     
       // $porder = M('porder o')->join('product p', 'p.id=o.product_id')->where(array('o.id' => I('id'), 'o.type' => array('in', array_column($allconfig, 'type_id')), 'o.status' => 4))->field('o.*,p.voucher_price,p.voucher_name')->find();

        $porder = M('porder o')->join('product p', 'p.id=o.product_id')->where(array('o.id' => I('id'), 'o.type' => array('in', array_column($allconfig, 'type_id')), 'o.status' => 4))->field('o.*,p.voucher_price,p.voucher_name')->find();
        if (!$porder) {
            echo "未获得凭证";
            exit;
        }
        $map['status'] = 1;
        in_array($porder['type'], array(1, 2)) && ($map['isp'] = ispstrtoint($porder['isp']));
        $map['type_id'] = $porder['type'];
        $voucher = M('voucher_config')->where($map)->find();
        if (!$voucher) {
            echo "订单没有凭证";
            exit;
        }
        $txtdata = array();
        if ($voucher['is_no']) {
            $txtdata[] = array('type' => 'txt', 'left' => $voucher['no_left'], 'top' => $voucher['no_top'], 'size' => $voucher['no_size'], 'color' => $voucher['no_color'], 'text' => $porder['order_number']);
        }
        if ($voucher['is_mobile']) {
            $txtdata[] = array('type' => 'txt', 'left' => $voucher['mobile_left'], 'top' => $voucher['mobile_top'], 'size' => $voucher['mobile_size'], 'color' => $voucher['mobile_color'], 'text' => $porder['mobile']);
        }
        if ($voucher['is_date']) {
            $txtdata[] = array('type' => 'txt', 'left' => $voucher['date_left'], 'top' => $voucher['date_top'], 'size' => $voucher['date_size'], 'color' => $voucher['date_color'], 'text' => time_format($porder['finish_time']));
        }
        if ($voucher['is_price']) {
            $txtdata[] = array('type' => 'txt', 'left' => $voucher['price_left'], 'top' => $voucher['price_top'], 'size' => $voucher['price_size'], 'color' => $voucher['price_color'], 'text' => $porder['voucher_price'] ?: $porder['total_price']);
        }
        if ($voucher['is_product']) {
            $txtdata[] = array('type' => 'txt', 'left' => $voucher['product_left'], 'top' => $voucher['product_top'], 'size' => $voucher['product_size'], 'color' => $voucher['product_color'], 'text' => $porder['voucher_name'] ?: $porder['product_name']);
        }


        $this->assign('txtdata', json_encode($txtdata));
        $this->assign('bgpath', $voucher['path']);
        $this->assign('id', I('id'));

        return view();
    }
}