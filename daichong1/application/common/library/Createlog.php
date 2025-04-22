<?php

//decode by http://chiran.taobao.com/
namespace app\common\library;

class Createlog
{
	public static function porderLog($porderid, $log)
	{
	    try{
	       // dump(mb_strpos('订单申请退单', $log));
    	    if(mb_strpos($log, '订单申请退单') !== false){
    	       // dump(1);
    	        self::appendOrderId($porderid);
    	    }
    	    if(mb_strpos($log,'回调信息返回') !== false){
    	       // dump(2);
    	        self::removeOrderId($porderid);
    	    }
	    }catch(Exception $e){
	        dump($e->getMessge());
	    }
		M('porder_log')->insertGetId(array('porder_id' => $porderid, 'log' => $log, 'create_time' => time()));
	}
	public static function customerLog($customer_id, $log, $operator)
	{
		M('customer_log')->insertGetId(array('customer_id' => $customer_id, 'log' => $log, 'operator' => $operator, 'create_time' => time()));
	}
	public static function appendOrderId($porder_id){
	    $order = M('porder')->where('id', $porder_id)->find();
	    if(!$order){
	        return false;
	    }else{
	        $redis = self::getRedis();
            $redis->sAdd('out_time_orders',$order['id']);
	    }
	}
	public static function removeOrderId($porder_id){
	    $order = M('porder')->where('id', $porder_id)->find();
	    if(!$order){
	        return false;
	    }else{
	        $redis = self::getRedis();
            $redis->sRem('out_time_orders',$order['id']);
	    }
	}
	public static function getRedis(){
        $redis = new \redis();
        $redis->connect('127.0.0.1', '6379');
        $redis->select(1);
        return $redis;
    }
}