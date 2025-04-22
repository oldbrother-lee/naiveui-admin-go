<?php
/**
@to user 1006000670@qq.com
 */


namespace app\yrapi\controller;

use app\common\library\Createlog;
use app\common\model\Client;
use app\common\model\Porder as PorderModel;
use app\common\model\Product as ProductModel;
use think\Log;

class Index extends Home
{
    public function index()
    {
        return djson(1, '欢迎访问');
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
            return djson(1, '已经存在相同商户订单号的订单' . '    ' . $out_trade_num . '    ' . $this->customer['id'] );
        }
        //增加空号检测API
        $ol = M('config')->where(['name'=>'IS_JC'])->find();
        if($ol['value']>0){
            $is = konghaojiance($mobile);
            if(!$is){
                return djson(1, '该账号检测状态不正常，请核实后再提交订单'. '   ' . $mobile);
            }
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

    /**
     * 增加 退款api接口
     * PorderModel::
     */
    public function cancel()
    {
        $out_trade_nums = I('out_trade_nums');
        $porders = M('porder')->where(array('customer_id' => $this->customer['id'], 'out_trade_num' => array('in', $out_trade_nums), 'is_del' => 0, 'apply_refund' => 0, 'status' => array('in', '2,3,10')))->select();

        if (!$porders) {
            return rjson(1, '订单不可申请撤单');
        }
        $counts = 0;
        $errmsg = '';
        foreach ($porders as $porder) {
            if (in_array($porder['status'], array(3))) {
                Createlog::porderLog($porder['id'], "为订单申请退单，申请人id：" . $this->customer['id']);
                $res = PorderModel::cancelApiOrder($porder['id']);
                if ($res['errno'] == 0) {
                    Createlog::porderLog($porder['id'], "订单API申请取消成功|返回：" . var_export($res['data'], true));
                    self::rechargeFailDo($porder['order_number'], "取消订单|操作人：" . $this->customer['id']);
                } else {
                    Createlog::porderLog($porder['id'], "订单API申请取消失败|返回：" . var_export($res['data']), true);
                    Createlog::porderLog($porder['id'], "返回数据：" . json_encode($res));
                    Createlog::porderLog($porder['id'], "该订单进入退单等待期，不再提交后续api|该订单不会立马失败，当前api失败回调后订单才会自动失败，如果api回调充值成功，订单依然会成功");
                    M('porder')->where(array('id' => $porder['id']))->setField(array('apply_refund' => 1));
                }
                $counts++;
            } else {
                if (in_array($porder['status'], array(2, 10))) {
                    if ($porder['status'] == 2 && $porder['api_open'] == 1) {
                        $errmsg .= "订单" . $porder['order_number'] . "正在提交，暂时不可取消；";
                    }
                    PorderModel::rechargeFailDo($porder['order_number'], "取消订单|操作人：" . $this->customer['id']);
                    $counts++;
                }
            }
        }
        if ($counts == 0) {
            return rjson(1, $errmsg);
        }
        return rjson(1, 'ok',$porders);

    }


}