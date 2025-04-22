<?php
/**
 * Created by PhpStorm.
 * User: 12048
 * Date: 2017-11-14
 * Time: 11:13
 */

namespace app\common\model;

use think\Model;
use Util\Ispzw;
use think\Log;

/**
 * 作者：lq
 * 邮箱：
 **/
class Product extends Model
{

    public static function getProducts($map, $user_id, $province = '', $city = '')
    {
        M('customer_hezuo_price')->where(['show_style' => 0])->setField(['show_style' => 1]);
        $map['p.is_del'] = 0;
        $user = M('customer')->where(['id' => $user_id, 'is_del' => 0])->field("*,grade_id as sgrade_id,(select is_zdy_price from dyr_customer_grade where id=grade_id) as is_zdy_price")->find();
        if (!$user) {
            return rjson(1, '查询产品时，用户信息无效', []);
        }
        $cateids = M('product p')->where($map)->group('p.cate_id')->field('p.cate_id')->select();
        if (!$cateids) return rjson(1, '查询产品时，没有查询条件中的分类！', []);

        $typesmap = ['status' => 1];
        //如果H5分销
        if ($user['f_id'] && $user['type'] == 1 && $fuser = M('customer')->where(['id' => $user['f_id'], 'is_del' => 0, 'is_h5fx' => 1])->field('id,grade_id')->find()) {
            $user['sgrade_id'] = $fuser['grade_id'];
            //如果H5分销代理关闭了类型
            if ($agtype = M('agent_product_type')->where(['customer_id' => $user['f_id'], 'status' => 0])->field('product_type_id')->select()) {
                $typesmap['id'] = ['not in', array_column($agtype, 'product_type_id')];
            }
            //如果H5分销设置了不显示的端
            if (isset($map['p.show_style']) && $showtss = M('customer_hezuo_price')->where(['customer_id' => $user['f_id'], 'show_style' => ['not in', $map['p.show_style'][1]]])->field('product_id')->select()) {
                $map['p.id'] = ['not in', array_column($showtss, 'product_id')];
            }
        }
        $types = M('product_type')->where($typesmap)->field('id')->select();
        if (!$types) return rjson(1, '查询产品时，没有查询条件中的产品类型！', []);
        $cates = M('product_cate')->where(['type' => ['in', array_column($types, 'id')]])->where(['id' => ['in', array_column($cateids, 'cate_id')]])->order('type asc,sort asc')->select();
        if (!$cates) return rjson(1, '查询产品时，没有查询条件中的分类，可能是H5分销关闭的！', []);
        $cateres = self::agentNCate($cates, $user_id);
        $cates = $cateres['data'];
        foreach ($cates as $ckey => &$cate) {
            $map['p.cate_id'] = $cate['id'];
            $cate['products'] = M('product p')
                ->where($map)
                ->order("p.type,p.sort asc")
                ->field("p.id,p.name,p.name as yname,p.desc,p.api_open,p.isp,p.ys_tag,p.price,p.show_style,p.cate_id,p.delay_api,p.grade_ids,0 as y_price,p.max_price,p.type,p.allow_pro,p.allow_city,p.forbid_pro,p.forbid_city,p.jmapi_id,p.jmapi_param_id,p.is_jiema,(select cate from dyr_product_cate where id=p.cate_id) as cate_name,(select type_name from dyr_product_type where id=p.type) as type_name,(select typec_id from dyr_product_type where id=p.type) as typec_id")
                ->select();
            foreach ($cate['products'] as $pkey => &$product) {
                if ($province && $product['allow_pro'] && !strstr($product['allow_pro'], $province)) {
                    unset($cate['products'][$pkey]);
                    continue;
                }
                if ($city && $product['allow_city'] && !strstr($product['allow_city'], $city)) {
                    unset($cate['products'][$pkey]);
                    continue;
                }
                if ($province && $product['forbid_pro'] && strstr($product['forbid_pro'], $province)) {
                    unset($cate['products'][$pkey]);
                    continue;
                }
                if ($city && $product['forbid_city'] && strstr($product['forbid_city'], $city)) {
                    unset($cate['products'][$pkey]);
                    continue;
                }
                if ($product['grade_ids'] && !inArrayDou($product['grade_ids'], $user['sgrade_id'])) {
                    unset($cate['products'][$pkey]);
                    continue;
                }
                $fdres = self::computePrice($product['id'], $user_id);
                $fd_price = $fdres['data']['price'];
                //如果自定义有名字
                if (isset($fdres['data']['name']) && $fdres['data']['name']) {
                    $product['name'] = $fdres['data']['name'];
                }
                if (isset($fdres['data']['ys_tag']) && $fdres['data']['ys_tag']) {
                    $product['ys_tag'] = $fdres['data']['ys_tag'];
                }
                $product['price'] = sprintf("%.2f", $product['price'] + $fd_price);

                //接码
                if ($product['is_jiema'] == 1 && $product['jmapi_id'] > 0 && $product['jmapi_param_id'] > 0) {
                    $jiema = M('jmapi_param jmp')
                        ->join('jmapi jm', 'jm.id=jmp.jmapi_id')
                        ->where(['jmp.id' => $product['jmapi_param_id'], 'jm.is_del' => 0])
                        ->field('jmp.jmnum,jmp.desc,jm.name')->find();
                    if ($jiema) {
                        $product['jiema'] = $jiema;
                    } else {
                        $product['is_jiema'] = 0;
                    }
                }
            }
            if (count($cate['products']) == 0) {
                unset($cates[$ckey]);
            } else {
                $cate['products'] = array_values($cate['products']);
            }
        }
        return rjson(0, '查询成功！', array_values($cates));
    }

    public static function getProduct($map, $user_id, $province = '', $city = '')
    {
        // echo("2121");
        $map['p.is_del'] = 0;
        $user = M('customer')->where(['id' => $user_id, 'is_del' => 0])->field("*,grade_id as sgrade_id,(select is_zdy_price from dyr_customer_grade where id=grade_id) as is_zdy_price")->find();
        if (!$user) {
            return rjson(1, '查询产品时，用户信息无效', false);
        }
        // echo("0000");
        // var_dump($map);
        // exit();
        $info = M('product p')
            ->where($map)
            ->order("p.sort asc")
            ->field("p.id,p.name,p.name as yname,p.desc,p.api_open,p.isp,p.ys_tag,p.price,p.show_style,p.cate_id,p.delay_api,p.grade_ids,0 as y_price,p.max_price,p.type,p.allow_pro,p.allow_city,p.forbid_pro,p.forbid_city,p.jmapi_id,p.jmapi_param_id,p.is_jiema,(select cate from dyr_product_cate where id=p.cate_id) as cate_name,(select type_name from dyr_product_type where id=p.type) as type_name,(select typec_id from dyr_product_type where id=p.type) as typec_id")
            ->find();
            // echo("bslsky");
            // var_dump($info);exit();
        if (!$info) {
            //Log::info($map);
            return rjson(102, '未找打符合该充值的产品，请查看代理端产品列表是否存在该产品ID！', false);
        }
        // echo("1111");
        if ($user['f_id'] && $user['type'] == 1 && $fuser = M('customer')->where(['id' => $user['f_id'], 'is_del' => 0, 'is_h5fx' => 1])->field('id,grade_id')->find()) {
            $user['sgrade_id'] = $fuser['grade_id'];
        }
        // echo("2222");
        if ($province && $info['allow_pro'] && !strstr($info['allow_pro'], $province)) {
            return rjson(1, '省份不在可充范围！', false);
        }
        // echo("3333");
        if ($city && $info['allow_city'] && !strstr($info['allow_city'], $city)) {
            return rjson(1, '城市不在可充范围！', false);
        }
        // echo("4444");
        if ($province && $info['forbid_pro'] && strstr($info['forbid_pro'], $province)) {
            return rjson(1, '省份在限制范围！', false);
        }
        // echo("555");
        if ($city && $info['forbid_city'] && strstr($info['forbid_city'], $city)) {
            return rjson(1, '城市在限制范围！', false);
        }
        if ($info['grade_ids'] && !inArrayDou($info['grade_ids'], $user['sgrade_id'])) {
            return rjson(1, '产品被限制用户等级使用！', false);
        }
        $fdres = self::computePrice($info['id'], $user_id);
        $fd_price = $fdres['data']['price'];

        //如果自定义有名字
        if (isset($fdres['data']['name']) && $fdres['data']['name']) {
            $info['name'] = $fdres['data']['name'];
        }
        $info['price'] = sprintf("%.2f", $info['price'] + $fd_price);
        $apiarr = M('product_api')->where(['product_id' => $info['id'], 'status' => 1])->order('sort')->select();
        $info['api_open'] = ($info['api_open'] && count($apiarr) > 0) ? 1 : 0;
        $info['api_arr'] = json_encode($apiarr);

        //接码
        if ($info['is_jiema'] == 1 && $info['jmapi_id'] > 0 && $info['jmapi_param_id'] > 0) {
            $jiema = M('jmapi_param jmp')
                ->join('jmapi jm', 'jm.id=jmp.jmapi_id')
                ->where(['jmp.id' => $info['jmapi_param_id'], 'jm.is_del' => 0])
                ->field('jmp.jmnum,jmp.desc,jm.name')->find();
            if ($jiema) {
                $info['jiema'] = $jiema;
            } else {
                $info['is_jiema'] = 0;
            }
        }
        return rjson(0, '查询成功', $info);
    }

    public static function computePrice($product_id, $user_id)
    {
        $user = M('Customer')->where(['id' => $user_id])->field("id,f_id,grade_id")->find();
        if (!$user) {
            return rjson(500, 'ok', ['price' => 0]);
        }
        if ($user['f_id'] && $fuser = M('Customer')->where(['id' => $user['f_id']])->field("id,f_id,grade_id,(select is_zdy_price from dyr_customer_grade where id=grade_id) as is_zdy_price")->find()) {
            //如果有上级
            if ($fuser && $user['grade_id'] < $fuser['grade_id'] && $fuser['is_zdy_price']) {
                //上级有效
                $hezuo = M('customer_hezuo_price')->where(['customer_id' => $user['f_id'], 'product_id' => $product_id])->find();
                if ($hezuo && $hezuo['ranges'] && floatval($hezuo['ranges']) > 0) {
                    //如果有自定价
                    $ranges = $hezuo['ranges'];
                    $resfd = self::computePrice($product_id, $user['f_id']);
                    $hezuo['price'] = floatval($ranges + ($resfd['errno'] == 0 ? $resfd['data']['price'] : 0));
                    return rjson(0, 'ok', $hezuo);
                } else {
                    //如果没有自定价，取等级浮动价格
                    !$hezuo && $hezuo = [];
                    $ranges = M('customer_grade_price')->where(['grade_id' => $user['grade_id'], 'product_id' => $product_id])->value('ranges');
                    $hezuo['price'] = floatval($ranges);
                    return rjson(0, 'ok', $hezuo);
                }
            } else {
                //上级无效，取等级浮动价格
                $ranges = M('customer_grade_price')->where(['grade_id' => $user['grade_id'], 'product_id' => $product_id])->value('ranges');
                return rjson(0, 'ok', ['price' => floatval($ranges)]);
            }
        } else {
            //没有上级，取等级浮动价格
            $ranges = M('customer_grade_price')->where(['grade_id' => $user['grade_id'], 'product_id' => $product_id])->value('ranges');
            return rjson(0, 'ok', ['price' => floatval($ranges)]);
        }
    }

    //获取代理自定义的分类配置
    public static function agentNCate($cates, $user_id)
    {
        $user = M('Customer')->where(['id' => $user_id])->field("id,f_id,grade_id")->find();
        if (!$user) {
            return rjson(0, 'ok', $cates);
        }
        if ($user['f_id'] && $fuser = M('Customer')->where(['id' => $user['f_id']])->field("id,f_id,grade_id,is_h5fx")->find()) {
            if ($fuser && $user['grade_id'] < $fuser['grade_id'] && $fuser['is_h5fx']) {
                //如果有上级且有效
                foreach ($cates as $ckey => &$cate) {
                    $acate = M('agent_product_cate')->where(['customer_id' => $fuser['id'], 'cate_id' => $cate['id']])->find();
                    if (!$acate) {
                        continue;
                    }
                    if ($acate['cate']) {
                        $cate['cate'] = $acate['cate'];
                    }
                    if ($acate['status'] == 0) {
                        unset($cates[$ckey]);
                    }
                }
            }
        }
        return rjson(0, 'ok', array_values($cates));
    }

    //初始化类型
    public static function initAgentProductType($customer_id)
    {
        $types = M('product_type')->select();
        foreach ($types as $type) {
            if (!M('agent_product_type')->where(['customer_id' => $customer_id, 'product_type_id' => $type['id']])->find()) {
                M('agent_product_type')->insertGetId([
                    'customer_id' => $customer_id,
                    'product_type_id' => $type['id'],
                    'tishidoc' => $type['tishidoc'],
                    'status' => $type['status']
                ]);
            }
        }
        return rjson(0, '初始化完成');
    }

    //初始化分类
    public static function initAgentProductCate($customer_id)
    {
        $cates = M('product_cate')->select();
        foreach ($cates as $cate) {
            if (!M('agent_product_cate')->where(['customer_id' => $customer_id, 'cate_id' => $cate['id']])->find()) {
                M('agent_product_cate')->insertGetId([
                    'customer_id' => $customer_id,
                    'cate' => '',
                    'cate_id' => $cate['id'],
                    'status' => 1
                ]);
            }
        }
        return rjson(0, '初始化完成');
    }


    //携号转网修正
    public static function Ispzhan($mobile, $customer_id, $guishu)
    {
        //未打开，不做修正
        if (C('ISP_ZHUANW_SW') != 2) {
            return $guishu;
        }
        $resc = Customer::canQueryIspz($customer_id);
        if ($resc['errno'] != 0) {
            $guishu['data']['ipsz_msg'] = $resc['errmsg'];
            return $guishu;
        }
        $res = Ispzw::isZhuanw(C('ISP_ZHUANW_CFG.apikey'), $mobile);
        if ($res['errno'] == 0) {
            //转网
            $guishu['data']['ispstr'] = $res['data']['result']['Now_isp'];
            $guishu['data']['isp'] = ispstrtoint($res['data']['result']['Now_isp']);
            $arr = explode("-", $res['data']['result']['Area']);
            $guishu['data']['prov'] = $arr[0];
            $guishu['data']['city'] = $arr[1];
            $guishu['data']['ipsz_msg'] = $res['errmsg'];
            return $guishu;
        } else {
            //未转网
            $guishu['data']['ipsz_msg'] = $res['errmsg'];
            return $guishu;
        }
    }

    public static function getTypec($typec_id)
    {
        $info = M('product_typec')->where(['id' => $typec_id])->find();
        if (!$info) {
            return false;
        }
        $ziduans = M('product_typec_ziduan')->where(['typec_id' => $typec_id])->order('sort asc,id asc')->select();
        foreach ($ziduans as &$zd) {
            if ($zd['input_type'] == 4) {
                $zd['select_items'] = parseMaoArr($zd['select_items']);
            }
        }
        $info['ziduan'] = $ziduans;
        return $info;
    }

}