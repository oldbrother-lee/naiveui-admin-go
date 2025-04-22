<?php

//decode by http://chiran.taobao.com/
namespace app\agent\controller;

use app\common\model\CustomerHezuoPrice;
use app\common\model\Product as ProductModel;
use think\Db;
use think\Exception;

class Product extends Admin
{
    public function index()
    {
        $map['p.is_del'] = 0;
        $map['p.added'] = 1;
        $map['p.show_style'] = array('in', '1,3');
        if (I('type')) {
            $map['p.type'] = I('type');
        }
        if (I('key')) {
            $map['p.name|p.desc'] = array('like', '%' . I('key') . '%');
        }
        if (I('id')) {
            $map['p.id'] = I('id');
        }
        $resdata = ProductModel::getProducts($map, $this->user['id']);
        $lists = $resdata['data'];
        $grade = M('customer_grade')->where(array('id' => $this->user['grade_id']))->find();
        if ($lists && $grade['is_zdy_price'] == 1) {
            foreach ($lists as &$cate) {
                foreach ($cate['products'] as &$item) {
                    $hzprice = M('customer_hezuo_price')->where(array('customer_id' => $this->user['id'], 'product_id' => $item['id']))->field('id as rangesid,ranges,ys_tag,show_style,name')->find();
                    $item['allow_isp'] = '';
                    if (!$hzprice) {
                        $item['ys_tag'] = '';
                        $item['rangesid'] = 0;
                        $item['ranges'] = 0;
                        $item['zdyname'] = '';
                    } else {
                        $item['ys_tag'] = $hzprice['ys_tag'];
                        $item['rangesid'] = $hzprice['rangesid'];
                        $item['ranges'] = $hzprice['ranges'];
                        $item['show_style'] = $hzprice['show_style'];
                        $item['zdyname'] = $hzprice['name'];
                    }
                }
            }
        }
        $this->assign('_list', $lists);
        $this->assign('is_zdy_price', $grade['is_zdy_price']);
        $this->assign('_types', M('product_type')->where(array('status' => 1))->order('sort asc,id asc')->select());
        return view();
    }

// 批量修改代理浮动利润
    public function hz_price_edit()
    {
        if (request()->isPost()) {
            try {
                $customer = M('customer')->where(array('id' => $this->user['id']))->find();
                $product_ids = I('product_id');

                if(empty($product_ids)) {
                    return $this->error('未选择数据，无法设置');
                }

                $products = explode(',', $product_ids);
                $map['p.is_del'] = 0;
                Db::startTrans();

                $ranges = I('ranges');
                if(empty($ranges)){
                    throw new Exception("未输入数值，无法设置");
                }

                foreach ($products as $product_id) {
                    $map['p.id'] = $product_id;
                    $resdata = ProductModel::getProduct($map, $customer['id']);
                    if ($resdata['errno'] != 0) {
                        return $this->error($resdata['errmsg']);
                    }
                    $product = $resdata['data'];
                    if (count($products) > 1) {
                        preg_match('/\d+/', $product['name'], $matches); // 提取套餐名称中的数值
                        $ranges = floatval($matches[0]) / 100 * floatval(I('ranges')); // 将提取出的数值除以100，再与输入框中的数值相乘
                    } else {
                        $ranges = floatval(I('ranges'));
                    }
                    if ($ranges < 0) {
                        return $this->error('浮动金额不能小于0');
                    }
                    if (floatval($product['max_price']) > 0 && $product['price'] + $ranges > $product['max_price']) {
                        return $this->error('不能设置高于封顶价格');
                    }
                    $res = CustomerHezuoPrice::saveValue($this->user['id'], $product_id, array('ranges' => $ranges));
                    if ($res['errno'] != 0) {
                        throw new Exception();
                    }
                }
                Db::commit();
                return $this->success('保存成功');
            } catch (Exception $e) {
                Db::rollback();
                return $this->error('编辑失败：'.$e->getMessage());
            }
        } else {
            $info = M('customer_hezuo_price')->where(array('id' => I('id')))->find();
            $this->assign('info', $info);
            return view();
        }
    }



    public function hz_price_ystag_edit()
    {
        $res = CustomerHezuoPrice::saveValues(I('id'), $this->user['id'], I('product_id'), array('ys_tag' => I('ys_tag')));
        if ($res['errno'] == 0) {
            return $this->success('保存成功');
        } else {
            return $this->error('编辑失败');
        }
    }
    public function hz_price_zdyname_edit()
    {
        $res = CustomerHezuoPrice::saveValues(I('id'), $this->user['id'], I('product_id'), array('name' => I('name')));
        if ($res['errno'] == 0) {
            return $this->success('保存成功');
        } else {
            return $this->error('编辑失败');
        }
    }
    public function type()
    {
        ProductModel::initAgentProductType($this->user['id']);
        $list = M('product_type p')->join('agent_product_type ap', 'ap.product_type_id=p.id')->where(array('p.status' => 1, 'ap.customer_id' => $this->user['id']))->order('p.sort asc,p.id asc')->field('ap.*,p.type_name,p.sort')->select();
        $this->assign('_list', $list);
        return view();
    }
    public function type_edit()
    {
        if (request()->isPost()) {
            $arr = I('post.');
            $arr['tishidoc'] = isset($_POST['tishidoc']) ? $_POST['tishidoc'] : '';
            unset($arr['id']);
            if (I('id')) {
                $data = M('agent_product_type')->where(array('id' => I('id'), 'customer_id' => $this->user['id']))->setField($arr);
                if ($data) {
                    return $this->success('更新成功');
                } else {
                    return $this->error('更新失败');
                }
            }
        } else {
            $info = M('agent_product_type')->where(array('id' => I('id'), 'customer_id' => $this->user['id']))->field("*,(select type_name from dyr_product_type where id=product_type_id) as type_name")->find();
            $this->assign("info", $info);
        }
        return view();
    }
    public function edit()
    {
        $id = I('id');
        $product_id = I('product_id');
        if (request()->isPost()) {
            $res = CustomerHezuoPrice::saveValues($id, $this->user['id'], I('product_id'), array('show_style' => I('show_style')));
            if ($res['errno'] == 0) {
                return $this->success('保存成功');
            } else {
                return $this->error('编辑失败');
            }
        } else {
            $this->assign("product", M('product')->where(array('id' => $product_id))->find());
            $info = M('customer_hezuo_price')->where(array('id' => $id, 'customer_id' => $this->user['id']))->find();
            $this->assign("info", $info);
        }
        return view();
    }
    public function cate()
    {
        ProductModel::initAgentProductCate($this->user['id']);
        $list = M('product_cate p')->join('agent_product_cate ap', 'ap.cate_id=p.id')->where(array('ap.customer_id' => $this->user['id']))->order('p.sort asc,p.id asc')->field('ap.*,p.cate as ycate,p.sort,(select type_name from dyr_product_type where id=p.type) as type_name')->select();
        $this->assign('_list', $list);
        return view();
    }
    public function cate_edit()
    {
        if (request()->isPost()) {
            $arr = I('post.');
            unset($arr['id']);
            if (I('id')) {
                $data = M('agent_product_cate')->where(array('id' => I('id'), 'customer_id' => $this->user['id']))->setField($arr);
                if ($data) {
                    return $this->success('更新成功');
                } else {
                    return $this->error('更新失败');
                }
            }
        } else {
            $info = M('agent_product_cate')->where(array('id' => I('id'), 'customer_id' => $this->user['id']))->field("*,(select cate from dyr_product_cate where id=cate_id) as ycate")->find();
            $this->assign("info", $info);
        }
        return view();
    }
}