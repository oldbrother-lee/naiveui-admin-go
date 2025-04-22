<?php

//decode by http://chiran.taobao.com/
namespace app\queue\job;

use app\common\controller\Base;
use app\common\library\Createlog;
use app\common\library\Email;
use app\common\library\PayWay;
use app\common\model\Porder;
use think\Log;
use think\queue\Job;
class Work extends Base
{
    public function failed($data)
    {
        Log::error('队列Work失败' . var_export($data, true));
    }
    public function fire(Job $job, $data)
    {
        if ($job->attempts() > 2) {
            Log::error('Work超过2次了，将停止' . json_encode($data));
            $job->delete();
            return;
        }
        Log::error("消息已经执行了" . $job->attempts() . '次');
        Log::error('Work执行了' . json_encode($data));
        $job->release(3);
    }
    public function porderSubApi(Job $job, $porder_id)
    {
        $t1 = microtime(true);
        Porder::subApi($porder_id);
        $t2 = microtime(true);
        Createlog::porderLog($porder_id, "提交API充值耗时：" . round($t2 - $t1, 3) . 's');
        $job->delete();
    }
    public function pordersSubApi(Job $job, $data)
    {
        $api_arr = array();
        foreach ($data['apiparam'] as $k => $api) {
            $apione = M('reapi_param rp')->join('reapi r', 'r.id=rp.reapi_id')->where(array('rp.id' => $api['param_id']))->field("rp.reapi_id,rp.id as param_id,1 as num")->find();
            if (!$apione) {
                continue;
            }
            $apione['num'] = $api['num'];
            $api_arr[] = $apione;
        }
        foreach ($data['ids'] as $id) {
            Createlog::porderLog($id, '后台批量提交接口充值|管理员:' . $data['op']);
            $porder = M('porder')->where(array('id' => $id))->field('id,status')->find();
            if (!in_array($porder['status'], array(2, 10))) {
                Createlog::porderLog($id, '后台批量提交接口充值|订单不是待充值状态，已经被驳回请求');
                Porder::untlock($id);
            } else {
                M('porder')->where(array('id' => $id))->setInc('api_cur_count', 1);
                M('porder')->where(array('id' => $id))->setField(array('api_arr' => json_encode($api_arr), 'status' => 2, 'api_open' => 1, 'api_cur_index' => -1, 'apply_refund' => 0, 'delay_time' => 0));
                Createlog::porderLog($id, '后台批量提交接口充值|数据:' . json_encode($api_arr));
                $t1 = microtime(true);
                Porder::subApi($id);
                $t2 = microtime(true);
                Createlog::porderLog($id, "提交API充值耗时：" . round($t2 - $t1, 3) . 's');
                Porder::untlock($id);
            }
        }
        $job->delete();
    }
    public function pordersBatchApart(Job $job, $param)
    {
        $product_arr = array();
        foreach ($param['products'] as $k => $ap) {
            $pone = M('product p')->where(array('p.id' => $ap['product_id']))->field("p.id,p.name,p.cate_id,p.desc,p.api_open,p.type,1 as num,(select type_name from dyr_product_type where id=p.type) as type_name")->find();
            if (!$pone) {
                continue;
            }
            $apiarr = M('product_api')->where(array('product_id' => $pone['id'], 'status' => 1))->order('sort')->select();
            $pone['api_open'] = $pone['api_open'] && count($apiarr) > 0 ? 1 : 0;
            $pone['api_arr'] = json_encode($apiarr);
            $pone['num'] = $ap['num'];
            $product_arr[] = $pone;
        }
        foreach ($param['ids'] as $id) {
            Createlog::porderLog($id, '后台批量拆单|管理员:' . $param['op']);
            $porder = M('porder')->where(array('id' => $id))->field('customer_id,order_number as apart_order_number,out_trade_num,total_price,create_time,status,remark,mobile,guishu,isp,client,pay_way,weixin_appid,param1,param2,param3')->find();
            if (!$porder) {
                continue;
            }
            if (!in_array($porder['status'], array(2, 9, 10))) {
                Createlog::porderLog($id, '后台批量拆单|订单不是待充值、部分充值、压单状态，已经被驳回请求');
            } else {
                $chaicount = 0;
                foreach ($product_arr as $k => $product) {
                    Createlog::porderLog($id, '后台批量拆单|开始|拆成产品:[' . $product['id'] . ']' . $product['type_name'] . '-' . $product['name'] . ',数量：' . $product['num'] . '单');
                    for ($i = 0; $i < $product['num']; $i++) {
                        $data = $porder;
                        $data['product_id'] = $product['id'];
                        $data['total_price'] = 0;
                        $data['status'] = 1;
                        $data['type'] = $product['type'];
                        $data['title'] = $product['name'] . $product['type_name'];
                        $data['product_name'] = $product['name'];
                        $data['product_desc'] = $product['desc'];
                        $data['body'] = '为账号' . $data['mobile'] . '充值' . $product['name'] . $product['type_name'];
                        $data['api_open'] = $product['api_open'];
                        $data['api_arr'] = $product['api_arr'];
                        $data['api_cur_index'] = -1;
                        $data['api_cur_count'] = 0;
                        $data['is_apart'] = 1;
                        $model = new Porder();
                        $model->save($data);
                        if (!($aid = $model->id)) {
                            Createlog::porderLog($id, '后台批量拆单|失败|产品ID:' . $product['id']);
                            continue;
                        }
                        $neworder = M('porder')->where(array('id' => $aid))->field('id,order_number,pay_way')->find();
                        Createlog::porderLog($id, '后台批量拆单|成功|拆成产品:[' . $product['id'] . ']' . $product['type_name'] . '-' . $product['name'] . ',新单号：' . $neworder['order_number']);
                        Createlog::porderLog($aid, '来自订单拆单|原订单:[' . $id . ']' . $porder['apart_order_number']);
                        Porder::notify($neworder['order_number'], $neworder['pay_way'], '');
                        $chaicount++;
                    }
                }
                if ($chaicount > 0) {
                    M('porder')->where(array('id' => $id))->setField(array('is_apart' => 2, 'status' => 11));
                    Createlog::porderLog($id, '成功拆单|拆成' . $chaicount . '条');
                }
            }
        }
        $job->delete();
    }
    public function porderRefund(Job $job, $data)
    {
        $t1 = microtime(true);
        Porder::refund($data['id'], $data['remark'], $data['operator']);
        $t2 = microtime(true);
        Createlog::porderLog($data['id'], "退款耗时：" . round($t2 - $t1, 3) . 's');
        $job->delete();
    }
    public function adminPushExcel(Job $job, $data)
    {
        for ($i = 0; $i < count($data); $i++) {
            Porder::adminExcelOrder($data[$i]['id']);
        }
        $job->delete();
    }
    public function agentPushExcel(Job $job, $data)
    {
        for ($i = 0; $i < count($data); $i++) {
            Porder::agentExcelOrder($data[$i]['id']);
        }
        $job->delete();
    }
    public function agentApiPayPorder(Job $job, $data)
    {
        $t1 = microtime(true);
        Porder::agentApiPayPorder($data['porder_id'], $data['customer_id'], $data['notify_url']);
        $t2 = microtime(true);
        Createlog::porderLog($data['porder_id'], "代理API订单余额支付耗时：" . round($t2 - $t1, 3) . 's');
        $job->delete();
    }
    public function callFunc(Job $job, $data)
    {
        $job->delete();
        $classname = $data['class'];
        $fun = $data['func'];
        $param = $data['param'];
        call_user_func(array($classname, $fun), $param);
    }
}