<?php

//decode by http://chiran.taobao.com/
namespace app\common\library;

class Createlog
{
	public static function porderLog($porderid, $log)
	{
		M('porder_log')->insertGetId(array('porder_id' => $porderid, 'log' => $log, 'create_time' => time()));
	}
	public static function customerLog($customer_id, $log, $operator)
	{
		M('customer_log')->insertGetId(array('customer_id' => $customer_id, 'log' => $log, 'operator' => $operator, 'create_time' => time()));
	}
}