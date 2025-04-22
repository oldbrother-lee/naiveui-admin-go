<?php

namespace app\admin\Traits;

trait DaichongTrait
{
    public function get_order()
    {
        $user = get_dc_user();
        $header = [
            'Authorization:Bearer ' . $user['token'],
        ];
        $s = 0;


        if ($user['type'] == 1) {

            $xml_data = df_get('https://www.xitupt.com/cg/api/TaskPub/Partner/SyncOrder', '', $header);
            if(empty($xml_data)){
                echo  '无新的数据';
                return false;
            }
            $data = json_decode($xml_data, true);
            mylog('接收到数据',json_encode($data),'order'.date('Y-m-d H',time()));
            echo  '接收到数据'.count($data);
            if ($data) {
                $list = [];
                foreach ($data as $k => $v) {


                    $list['user'] = $user['id'];
                    $list['order_id'] = $v['id'];
                    $list['account'] = $v['account'];
                    $list['denom'] = $v['denom'];
                    $list['settlePrice'] = $v['settlePrice'];
                    $list['createTime'] = $v['createTime'];
                    $list['chargeTime'] = $v['chargeTime'];

                    $list['uploadTime'] = $v['uploadTime'];
                    $list['status'] = $v['status'];
                    $list['settleStatus'] = $v['settleStatus'];
                    $list['chargeTime'] = $v['chargeTime'];
                    $get = M('daichong_order')->where(['order_id' => $v['id']])->find();
                    if ($get) {
                        M('daichong_order')->where(['order_id' => $v['id']])->update($list);

                        //  mylog('更新订单' .$v['id'],json_encode($list));
                        //   echo '更新订单：' .$v['id'] . $s . PHP_EOL;
                    } else {
                        $s++;
                        M('daichong_order')->insert($list);
                        mylog('添加订单',json_encode($list));
                        // echo '添加订单：' .$v['id'] . $s . PHP_EOL;
                        //提交 开始充值
                        $this->sert($v['id'], 5);
                        //  mylog('提交充值状态返回',json_encode($as));

                    }

                }


            }
            echo '账号' . $user['user'] . '获取订单' . $s . PHP_EOL;

        }else{

            echo '账号' . $user['user'] . '停止接单状态' . PHP_EOL;
        }
    }
    public function sert($id, $status, $message = '')
    {

        $url = 'https://www.xitupt.com/cg/api/TaskPub/Partner/SendMsg';
        $data = [
            'id' => $id,
            'evt' => $status,
            'message' => $message,

        ];
        $user = get_dc_user();
        $header = [
            'Authorization:Bearer ' . $user['token'],
            'content-type: application/json'
        ];


        $xturl = df_post($url, $data, $header);

        mylog('手动上报订单信息' . $id, json_encode($data) . '返回：' . $xturl);


        M('daichong_order')->where(['order_id' => $id])->update(['is_post' => 1]);



    }
}