<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:78:"/www/wwwroot/115.126.57.143/public/../application/admin/view/porder/index.html";i:1704512464;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo C('WEB_SITE_TITLE'); ?></title>
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="/public/admin/css/bootstrap.min.css?v=3.3.61" rel="stylesheet">
    <link href="/public/admin/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link href="/public/admin/css/animate.css" rel="stylesheet">
    <link href="/public/admin/css/style.css?v91" rel="stylesheet">
    <link href="/public/admin/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="/public/admin/css/layx.min.css" rel="stylesheet"/>
    <link href="/public/admin/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/public/admin/css/animate.css" rel="stylesheet">
    <script src="/public/admin/js/jquery.min.js?v=2.1.4"></script>
    <script src="/public/admin/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/public/admin/js/plugins/layer/layer.min.js"></script>
    <script src="/public/admin/js/content.js"></script>
    <script src="/public/admin/js/plugins/toastr/toastr.min.js"></script>
    <script src="/public/admin/js/dayuanren.js?v89"></script>
    <script src="/public/admin/js/layx.js" type="text/javascript"></script>
    <script src="/public/admin/js/plugins/iCheck/icheck.min.js"></script>
    <script type="text/javascript" src="/public/admin/js/clipboard.min.js"></script>
    <script src="/public/admin/js/vue.min.js?v=3.3.6"></script>
    <script>
        console.log("<?php echo C('console_msg'); ?>");
        console.log("当前版本：<?php echo C('dtupdate.version'); ?>");
    </script>
</head>

<style>
    .table-bordered > tbody > tr:hover {
        background-color: #f5f5f5
    }
</style>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-sm-12">
        <div class="form-group">
            <div class="input-group">
                <input type="text" id="prefix_txt" placeholder="设置订单前n位" value="<?php echo $order_sn_prefix; ?>"
                       class="input-sm form-control">
                <span class="input-group-btn"><button type="button" id="search" class="btn btn-sm btn-primary" url="<?php echo U('index'); ?>"> 添加</button></span>
            </div>
        </div>
        </div>
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><span title="当前筛选订单结果总单数">订单列表（结果<?php echo $_total; ?>条）</span>&nbsp;&nbsp;
                        <span title="当前筛选订单结果总支付金额，不限制订单状态">结果总金额：￥<?php echo $total_price; ?></span>&nbsp;&nbsp;
                        <span title="成功订单的支付总金额">成功单总金额：￥<?php echo $sus_total_price; ?></span>&nbsp;
                        <span title="已经成功返利的金额">成功单返利金额：￥<?php echo $rebate_price; ?></span>&nbsp;
                        <span title="利润=成功订单总金额-提交接口的成本-分销返利">成功单利润：￥<?php echo sprintf("%.2f",$sus_total_price-$sus_cost-$rebate_price); ?></span>
                        <span title="部分成功单总金额"> 部分成功总金额：￥<?php echo $apsus_total_price; ?></span>
                        <span title="部分成功单返利金额"> 部分成功返利金额：￥<?php echo $aprebate_price; ?></span>
                        <span title="部分利润=部分成功订单总金额-提交接口的成本-分销返利">部分成功单利润：￥<?php echo sprintf("%.2f",$apsus_total_price-$apsus_cost-$aprebate_price); ?></span>
                        <span style="color: #f00" title="此统计仅供参考，受成本变化及具体订单操作的流程影响，统计可能不准确">此统计仅供参考</span>
                    </h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-12 m-b-xs form-inline text-left">
                            <div class="form-group">
                                <label class="control-label">时间:</label>
                                <select class="input-sm form-control input-s-sm inline" name="time_style"
                                        style="width: auto;">
                                    <option value="create_time"
                                    <?php if(I('time_style')=='create_time'){ echo "selected='selected'"; } ?>
                                    >下单时间</option>
                                    <option value="apireq_time"
                                    <?php if(I('time_style')=='apireq_time'){ echo "selected='selected'"; } ?>
                                    >提单时间</option>
                                    <option value="finish_time"
                                    <?php if(I('time_style')=='finish_time'){ echo "selected='selected'"; } ?>
                                    >完成时间</option>
                                    <option value="apifail_time"
                                    <?php if(I('time_style')=='apifail_time'){ echo "selected='selected'"; } ?>
                                    >失败时间</option>
                                    <option value="refund_time"
                                    <?php if(I('time_style')=='refund_time'){ echo "selected='selected'"; } ?>
                                    >退款时间</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <span class="control-label">日期:</span>
                                <input type="text" name="begin_time" id="begin_time" value="<?php echo I('begin_time'); ?>"
                                       class="input-sm input-s-sm form-control" autocomplete="off" placeholder="开始时间"/>
                                <span class="control-label">-</span>
                                <input type="text" name="end_time" id="end_time" value="<?php echo I('end_time'); ?>"
                                       class="input-sm input-s-sm form-control" autocomplete="off" placeholder="结束时间"/>
                            </div>
                            <div class="form-group">
                                <?php $client=C('CLIENT_TYPE'); ?>
                                <label class="control-label">渠道:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects"
                                        name="client"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($client) || $client instanceof \think\Collection || $client instanceof \think\Paginator): $i = 0; $__LIST__ = $client;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if(I('client')==$key){ echo "selected='selected'"; } ?>><?php echo $vo; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">类型:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="type"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($_types) || $_types instanceof \think\Collection || $_types instanceof \think\Paginator): $i = 0; $__LIST__ = $_types;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $vo['id']; ?>"
                                    <?php if(I('type')==$vo['id']){ echo "selected='selected'"; } ?>
                                    ><?php echo $vo['type_name']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">分类:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="cate"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(!(empty($cates) || (($cates instanceof \think\Collection || $cates instanceof \think\Paginator ) && $cates->isEmpty()))): if(is_array($cates) || $cates instanceof \think\Collection || $cates instanceof \think\Paginator): $i = 0; $__LIST__ = $cates;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $vo['id']; ?>"
                                    <?php if(I('cate')==$vo['id']){ echo "selected='selected'"; } ?>><?php echo $vo['cate']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">套餐:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="product_id"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(!(empty($products) || (($products instanceof \think\Collection || $products instanceof \think\Paginator ) && $products->isEmpty()))): if(is_array($products) || $products instanceof \think\Collection || $products instanceof \think\Paginator): $i = 0; $__LIST__ = $products;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $vo['id']; ?>"
                                    <?php if(I('product_id')==$vo['id']){ echo "selected='selected'"; } ?>
                                    ><?php echo $vo['name']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <?php $isps=C('ISP_TEXT');?>
                                <label class="control-label">运营商:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="isp"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($isps) || $isps instanceof \think\Collection || $isps instanceof \think\Paginator): $i = 0; $__LIST__ = $isps;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if(I('isp')==$key){ echo "selected='selected'"; } ?>><?php echo $vo; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <?php $payway=C('PAYWAY'); ?>
                                <label class="control-label">支付:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects"
                                        name="pay_way"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($payway) || $payway instanceof \think\Collection || $payway instanceof \think\Paginator): $i = 0; $__LIST__ = $payway;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if(I('pay_way')==$key){ echo "selected='selected'"; } ?>><?php echo $vo; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label" title="默认只显示主单，不会显示拆出子单">拆单:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="is_apart"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <option value="1"
                                    <?php if(I('is_apart')==1){ echo "selected='selected'"; } ?>>未被拆</option>
                                    <option value="2"
                                    <?php if(I('is_apart')==2){ echo "selected='selected'"; } ?>>子单</option>
                                    <option value="3"
                                    <?php if(I('is_apart')==3){ echo "selected='selected'"; } ?>>被拆</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label" title="提交了申请退款请求，还未退的可以查询">申请退单:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects"
                                        name="apply_refund" style="width: auto;">
                                    <option value="0">请选择</option>
                                    <option value="1"
                                    <?php if(I('apply_refund')==1){ echo "selected='selected'"; } ?>>否</option>
                                    <option value="2"
                                    <?php if(I('apply_refund')==2){ echo "selected='selected'"; } ?>>是</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <?php $statusarr=C('PORDER_STATUS');?>
                                <label class="control-label">状态:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="status"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($statusarr) || $statusarr instanceof \think\Collection || $statusarr instanceof \think\Paginator): $i = 0; $__LIST__ = $statusarr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if(I('status')==$key){ echo "selected='selected'"; } ?>><?php echo $vo; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">接口:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects"
                                        name="reapi_id"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($reapi) || $reapi instanceof \think\Collection || $reapi instanceof \think\Paginator): $i = 0; $__LIST__ = $reapi;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $vo['id']; ?>"
                                    <?php if(I('reapi_id')==$vo['id']){ echo "selected='selected'"; } ?>
                                    ><?php echo $vo['name']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label" title="回调状态">回调:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects"
                                        name="is_notification" style="width: auto;">
                                    <option value="0">请选择</option>
                                    <option value="1"
                                    <?php if(I('is_notification')==1){ echo "selected='selected'"; } ?>>否</option>
                                    <option value="2"
                                    <?php if(I('is_notification')==2){ echo "selected='selected'"; } ?>>是</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="control-label">批量手机:</label>
                                <textarea name="batch_mobile" class="form-control"
                                          placeholder="批量手机查询，多个回车分割"><?php echo I('batch_mobile'); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">批量单号:</label>
                                <textarea name="batch_order_number" class="form-control"
                                          placeholder="批量单号查询，多个回车分割"><?php echo I('batch_order_number'); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">批量api单号:</label>
                                <textarea name="batch_api_order_number" class="form-control"
                                          placeholder="批量单号查询，多个回车分割"><?php echo I('batch_api_order_number'); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">排序:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="sort"
                                        style="width: auto;">
                                    <option value="">默认</option>
                                    <option value="create_time desc"
                                    <?php if(I('sort')=='create_time desc'){ echo "selected='selected'"; } ?>
                                    >下单时间</option>
                                    <option value="finish_time desc"
                                    <?php if(I('sort')=='finish_time desc'){ echo "selected='selected'"; } ?>
                                    >完成时间</option>
                                    <option value="refund_time desc"
                                    <?php if(I('sort')=='refund_time desc'){ echo "selected='selected'"; } ?>
                                    >退款时间</option>
                                    <option value="notification_time desc"
                                    <?php if(I('sort')=='notification_time desc'){ echo "selected='selected'"; } ?>
                                    >回调时间</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">查询条件:</label>
                                <select class="input-sm form-control input-s-sm inline" name="query_name"
                                        style="width: auto;">
                                    <option value="">模糊</option>
                                    <option value="customer_id"
                                    <?php if(I('query_name')=='customer_id'){ echo "selected='selected'"; } ?>
                                    >用户ID</option>
                                    <option value="order_number"
                                    <?php if(I('query_name')=='order_number'){ echo "selected='selected'"; } ?>
                                    >订单号</option>
                                    <option value="apart_order_number"
                                    <?php if(I('query_name')=='apart_order_number'){ echo "selected='selected'"; } ?>
                                    >拆单原单号</option>
                                    <option value="mobile"
                                    <?php if(I('query_name')=='mobile'){ echo "selected='selected'"; } ?>>手机号</option>
                                    <option value="rebate_id"
                                    <?php if(I('query_name')=='rebate_id'){ echo "selected='selected'"; } ?>
                                    >返利ID</option>
                                    <option value="isp"
                                    <?php if(I('query_name')=='isp'){ echo "selected='selected'"; } ?>
                                    >运营商/地区</option>
                                    <option value="guishu"
                                    <?php if(I('query_name')=='guishu'){ echo "selected='selected'"; } ?>
                                    >归属地</option>
                                    <option value="lable"
                                    <?php if(I('query_name')=='lable'){ echo "selected='selected'"; } ?>
                                    >标签</option>
                                    <option value="customer.wx_openid.customer_id"
                                    <?php if(I('query_name')=='customer.wx_openid.customer_id'){ echo "selected='selected'"; } ?>
                                    >openid</option>
                                    <option value="customer.weixin_appid.customer_id"
                                    <?php if(I('query_name')=='customer.weixin_appid.customer_id'){ echo "selected='selected'"; } ?>
                                    >appid</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="hidden" name="customer_id" value="<?php echo I('customer_id'); ?>">
                                    <input type="hidden" name="apart_order_number" value="<?php echo I('apart_order_number'); ?>">
                                    <input type="hidden" id="prefix_txt2" name="prefix" value="<?php echo $order_sn_prefix; ?>">
                                    <input type="text" name="key" placeholder="请输入套餐/单号/手机号/归属地/备注" value="<?php echo I('key'); ?>"
                                           class="input-sm form-control">
                                    <span class="input-group-btn"><button type="button" id="search"
                                                                          class="btn btn-sm btn-primary"
                                                                          url="<?php echo U('index'); ?>"> 搜索</button></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="button" id="excel"
                                        class="btn btn-sm btn-primary"
                                        url="<?php echo U('out_excel'); ?>" title="筛选条件中的所有订单">
                                    <i class="fa fa-file-text-o"></i>
                                    导出
                                </button>
                            </div>
                            <div class="form-group">
                                <button type="button"
                                        class="btn btn-sm btn-primary batchsub"
                                        url="<?php echo U('batch_api'); ?>" title="筛选条件中的所有订单,会自动剔除非待充值/压单的状态">
                                    <i class="fa fa-sign-in"></i>
                                    批量提交接口
                                </button>
                            </div>
                            <div class="form-group">
                                <button type="button"
                                        class="btn btn-sm btn-primary batchsub"
                                        url="<?php echo U('batch_apart'); ?>" title="筛选条件中的所有订单,会自动剔除非待充值/部分充值/压单的状态，会自动剔除拆出来的单">
                                    <i class="fa fa-sign-in"></i>
                                    批量拆单
                                </button>
                            </div>
                            <div class="form-group">
                                <button type="button"
                                        class="btn btn-sm btn-primary batchsub"
                                        url="<?php echo U('batch_sms'); ?>" title="筛选条件中的所有订单,手机号充值的都可以发送">
                                    <i class="fa fa-sign-in"></i>
                                    批量短信
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12 m-b-xs form-inline text-left" style="color: #f00">
                            重要提示：导出、批量提交接口、批量拆单都是以条件筛选搜索的结果为准（多页），不是勾选的数据！<br/>
                            温馨提示：刚下的订单出现待付款是正常现象，正在排队支付，无需做任何操作！
                        </div>
                        <div class="col-md-12 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-warning ajax-post" target-form="i-checks"
                                        href="<?php echo U('set_czing'); ?>" title="多选框选中的订单，批量操作充值中，可操作的状态：待充值、异常、部分充值、压单">
                                    <i class="fa fa-check-square"></i>
                                    批量充值中
                                </button>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-info ajax-post" target-form="i-checks"
                                        href="<?php echo U('set_chenggong'); ?>"
                                        title="多选框选中的订单，批量操作成功，谨慎操作，可操作的状态：待充值、充值中、异常、部分充值、压单">
                                    <i class="fa fa-check-square"></i>
                                    批量成功
                                </button>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-warning ajax-post" target-form="i-checks"
                                        href="<?php echo U('set_shibai'); ?>"
                                        title="多选框选中的订单,批量操作订单失败，谨慎操作，可操作的状态：待充值、充值中、异常、部分充值、压单">
                                    <i class="fa fa-check-square"></i>
                                    批量失败
                                </button>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-danger ajax-post" target-form="i-checks"
                                        href="<?php echo U('refund'); ?>" title="多选框选中的订单，批量操作退款，可操作的状态：充值失败">
                                    <i class="fa fa-check-square"></i>
                                    批量退款
                                </button>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-warning ajax-post prompt" prompt-title="批量待充值备注"
                                        target-form="i-checks"
                                        href="<?php echo U('set_daicz'); ?>"
                                        title="多选框选中的订单，批量回到待充值状态，可操作的状态：充值成功、充值中、待付款、异常、部分充值、压单">
                                    <i class="fa fa-check-square"></i>
                                    批量待充值
                                </button>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-danger ajax-post" target-form="i-checks"
                                        href="<?php echo U('set_aprefund'); ?>"
                                        title="多选框选中的订单，批量申请订单直接失败，只适用已经提交api充值的订单，当api失败时立马失败，不再提交后面的接口，如果当前接口充值成功订单还是会成功，可操作的状态：充值中">
                                    <i class="fa fa-check-square"></i>
                                    批量申请退单
                                </button>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-success ajax-post" target-form="i-checks"
                                        href="<?php echo U('set_apbreak'); ?>"
                                        title="多选框选中的订单，批量申请订单直接失败，只适用已经提交api充值的订单，当api失败时变压单，不再提交后面的接口，如果当前接口充值成功订单还是会成功，可操作的状态：充值中">
                                    <i class="fa fa-check-square"></i>
                                    批量申请中断
                                </button>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-warning ajax-post" target-form="i-checks"
                                        href="<?php echo U('notification'); ?>"
                                        title="多选框选中的订单,订单自动检查需要回调的订单并进行回调">
                                    <i class="fa fa-check-square"></i>
                                    批量回调
                                </button>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-danger ajax-post" target-form="i-checks"
                                        href="<?php echo U('untlock'); ?>"
                                        title="多选框选中的订单,用于锁定订单出现问题时，解除锁定，以便再提api">
                                    <i class="fa fa-unlock"></i>
                                    解除锁定
                                </button>
                            </div>
                            <!--                            <div class="form-group">-->
                            <!--                                <a class="btn btn-sm btn-primary open-window" href="<?php echo U('porder_excel'); ?>" title="手动提单">-->
                            <!--                                    <i class="fa fa-file-excel-o"></i>-->
                            <!--                                    导入提单-->
                            <!--                                </a>-->
                            <!--                            </div>-->
                            <div class="form-group">
                                <a class="btn btn-sm btn-success open-window no-refresh" href="<?php echo U('rihuizong'); ?>"
                                   title="汇总统计">
                                    <i class="fa fa-calendar"></i>
                                    汇总统计
                                </a>
                            </div>
                            <div class="form-group">
                                <a class="btn btn-sm btn-default open-window no-refresh" href="<?php echo U('mobile_blacklist'); ?>"
                                   title="黑名单列表">
                                    <i class="fa fa-calendar"></i>
                                    黑名单列表
                                </a>
                            </div>
                            <div class="form-group">
                                <a class="btn btn-sm btn-success open-window no-refresh" href="<?php echo U('complaint'); ?>"
                                   title="投诉管理">
                                    <i class="fa fa-commenting"></i>
                                    投诉管理
                                </a>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-info prompt ajax-post" target-form="i-checks"
                                        prompt-title="填写自定义的标签内容"
                                        href="<?php echo U('lable'); ?>">
                                    <i class="fa fa-edit"></i>
                                    批量标签
                                </button>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-success prompt ajax-post" target-form="i-checks"
                                        prompt-title="填写自定义的备注内容"
                                        href="<?php echo U('remarks'); ?>">
                                    <i class="fa fa-edit"></i>
                                    批量备注
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th><input type="checkbox" class="i-checks check-all">全选</th>
                                <th>单号</th>
                                <th>客户</th>
                                <th>套餐</th>
                                <th>充值账号</th>
                                <th>渠道/支付</th>
                                <th>状态</th>
                                <th>时间</th>
                                <th style="text-align:center">耗时</th>
                                <th>金额</th>
                                <th>返利</th>
                                <th style="min-width: 150px">接口充值</th>
                                <th>备注</th>
                                <th>返回</th>
                                <th>标签</th>
                                <th>回调/次数</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td style="width: 70px;">
                                    <input type="checkbox" class="i-checks ids" name="id[]" value="<?php echo $vo['id']; ?>">
                                </td>
                                <td style="font-size: 12px;">
                                    <span class="js-copy"
                                          data-clipboard-text="<?php echo $vo['order_number']; ?>"><?php echo $vo['order_number']; ?></span><br/>
                                    <?php if(!(empty($vo['out_trade_num']) || (($vo['out_trade_num'] instanceof \think\Collection || $vo['out_trade_num'] instanceof \think\Paginator ) && $vo['out_trade_num']->isEmpty()))): ?>
                                    <span class="js-copy" data-clipboard-text="<?php echo $vo['out_trade_num']; ?>">商户单号：<?php echo $vo['out_trade_num']; ?></span><br/>
                                    <?php endif; if($vo['is_apart'] == '1'): ?>
                                    <span class="js-copy" data-clipboard-text="<?php echo $vo['apart_order_number']; ?>">来自拆单：<?php echo $vo['apart_order_number']; ?></span><br/>
                                    <?php endif; if($vo['is_apart'] == '2'): ?>
                                    子单：<a class="text-success open-window no-refresh"
                                          href="<?php echo U('index',['apart_order_number'=>$vo['order_number'],'is_apart'=>2]); ?>"><?php echo getApartOrderNum($vo['order_number']); ?></a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="max-width: 100px;overflow-x: scroll;font-size: 12px;white-space:nowrap;">
                                        [<a class="text-success open-window no-refresh"
                                            href="<?php echo U('customer/index',['id'=>$vo['customer_id']]); ?>">ID:<?php echo $vo['customer_id']; ?></a>]&nbsp;<?php echo $vo['username']; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo $vo['type_name']; ?><br/>
                                    [<a class="text-success open-window no-refresh"
                                        href="<?php echo U('product/index',['id'=>$vo['product_id'],'type'=>$vo['type']]); ?>">ID:<?php echo $vo['product_id']; ?></a>]&nbsp;<?php echo $vo['product_name']; ?>
                                </td>
                                <td class="js-copy" data-clipboard-text="<?php echo $vo['mobile']; ?>">
                                    <span title="充值账号，手机号，户号等"><?php echo $vo['mobile']; ?></span><br/>
                                    <?php echo $vo['guishu']; ?><?php echo $vo['isp']; ?><br/>
                                    <?php if($vo['param1'] || $vo['param2'] || $vo['param3']): ?>
                                    <span title="此栏目的值分别代表param1,param2,param3"><?php echo $vo['param1']; ?>/<?php echo $vo['param2']; ?>/<?php echo $vo['param3']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    渠道：<?php echo C('CLIENT_TYPE')[$vo['client']]; ?><br/>
                                    支付：<?php echo C('PAYWAY')[$vo['pay_way']]; ?><br/>
                                </td>
                                <td class="open-window" href="<?php echo U('upstatus',['id'=>$vo['id']]); ?>" title="修改状态">
                                    <?php switch($vo['status']): case "1": ?>
                                    <span class="label label-default">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "2": ?>
                                    <span class="label label-success">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "3": ?>
                                    <span class="label label-warning">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "4": ?>
                                    <span class="label label-primary">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "5": ?>
                                    <span class="label label-danger">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "6": ?>
                                    <span class="label label-default">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "8": ?>
                                    <span class="label label-danger">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "9": ?>
                                    <span class="label label-warning">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "10": ?>
                                    <span class="label label-success" title="压单订单可人工再次提交api">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "11": ?>
                                    <span class="label label-warning"
                                          title="拆单订单不能再提交API,可以将拆出的订单提交api，全部订单都成功或者失败的情况下回自动改变状态并回调，如果是部分成功或者失败的会保留状态人工处理">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "12": ?>
                                    <span class="label label-primary" title="压单订单可人工再次提交api">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "13": ?>
                                    <span class="label label-default" title="压单订单可人工再次提交api">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; default: ?>
                                    <span class="label label-default">
                                    <?php echo C('PORDER_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php endswitch; if($vo['apply_refund'] == '1'): ?>
                                    <br/><br/><span title="申请退单的状态并不一定会退单，如果当前接口回调充值成功，订单也会变成充值成功">申请退单中..</span>
                                    <?php endif; if($vo['apply_break'] == '1'): ?>
                                    <br/><br/><span
                                        title="申请中断的状态，如果当前api回调充值成功，订单会变成充值成功，如果api充值失败，订单回到压单状态">申请中断..</span>
                                    <?php endif; if($vo['tlocking'] > '0'): ?>
                                    <br/><br/><span title="订单在执行耗时任务，待执行完成后方可进行其他操作">锁定<?php echo $vo['tlocking']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td align=center>
                                    <span class="label label-info"><?php echo elapsed_time($vo['create_time'],$vo['finish_time']); ?></span>
                                </td>
                                <td>
                                    <div style="max-height: 100px;overflow-y: scroll;font-size: 12px;">
                                        下单：<?php echo time_format($vo['create_time']); ?><br/>
                                        支付：<?php echo time_format($vo['pay_time']); ?><br/>
                                        api上单：<?php echo time_format($vo['apireq_time']); ?><br/>
                                        api失败：<?php echo time_format($vo['apifail_time']); ?><br/>
                                        完成：<?php echo time_format($vo['finish_time']); ?><br/>
                                        退款：[￥<?php echo $vo['refund_price']; ?>]&nbsp;<?php echo time_format($vo['refund_time']); ?><br/>
                                        回调：<?php echo time_format($vo['notification_time']); ?><br/>
                                        耗时：<?php echo elapsed_time($vo['create_time'],$vo['finish_time']); ?>
                                    </div>
                                </td>
                                <td><?php echo $vo['total_price']; ?></td>
                                <td style="font-size: 12px;">
                                    <?php if($vo['rebate_id'] > '0'): ?>
                                    [1]代理：<?php echo get_name($vo['rebate_id']); ?><br/>金额：<?php echo $vo['rebate_price']; ?><br/>状态：<?php echo !empty($vo['is_rebate'])?'已返利':'未返利'; ?><br/>
                                    <?php endif; if($vo['rebate_id2'] > '0'): ?>
                                    [2]代理：<?php echo get_name($vo['rebate_id2']); ?><br/>金额：<?php echo $vo['rebate_price2']; ?><br/>状态：<?php echo !empty($vo['is_rebate2'])?'已返利':'未返利'; endif; ?>
                                </td>
                                <td>
                                    <div style="max-height: 100px;overflow-y: scroll;font-size: 12px;">
                                        <?php if($vo['api_open'] == '1'): $apiarr = json_decode($vo['api_arr'],true);if(is_array($apiarr) || $apiarr instanceof \think\Collection || $apiarr instanceof \think\Paginator): $key = 0; $__LIST__ = $apiarr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ap): $mod = ($key % 2 );++$key;?>
                                        <?php echo $key; ?>.
                                        <?php echo $vo['api_cur_index']==$key-1?'[当前]':''; ?><?php echo getReapiName($ap['reapi_id']); ?>-<?php echo getReapiParamName($ap['param_id']); ?><br/>
                                        <?php endforeach; endif; else: echo "" ;endif; if(!(empty($apireq_time) || (($apireq_time instanceof \think\Collection || $apireq_time instanceof \think\Paginator ) && $apireq_time->isEmpty()))): ?>
                                        提交时间：<?php echo time_format($vo['apireq_time']); endif; else: ?>
                                        <?php echo !empty($vo['api_open'])?'打开':'关闭'; endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-height: 100px;overflow-y: scroll;font-size: 12px;width: 80px">
                                        <?php echo $vo['remark']; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-height: 100px;overflow-y: scroll;font-size: 12px;width: 50px">
                                        卡密/流水：<?php echo $vo['charge_kami']; ?><br/>
                                        已充：<?php if($vo['charge_amount'] > '0'): ?>
                                        <?php echo $vo['charge_amount']; else: ?>
                                        -
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><p style="color:#e00;"><?php echo $vo['lable']; ?></p>
                                    <a class="open-window" href="<?php echo U('lable?id='.$vo['id']); ?>"
                                       title="详情"><i class="fa fa-edit"></i></a>
                                </td>
                                <td>
                                    <?php if($vo['is_apart'] == '0'): $jdid = C('JDCONFIG.userid');$ksid = C('KSCONFIG.userid');if($vo['customer_id']==$jdid || $vo['customer_id']==$ksid || $vo['client']==4): ?>
                                    <?php echo !empty($vo['is_notification'])?'已回调':'未回调'; ?>/<?php echo $vo['notification_num']; ?><br/>
                                    <span class="tiptext text-success" data-text='<?php echo $vo['notify_url']; ?>'>地址</span><br/>
                                    <a class="text-danger ajax-get confirm" href="<?php echo U('notification?id='.$vo['id']); ?>"
                                       title="手动回调">手动回调</a>
                                    <?php endif; endif; ?>
                                </td>
                                <td>
                                    <a class="open-window no-refresh" href="<?php echo U('log?id='.$vo['id']); ?>"
                                       title="详情">日志</a>
                                    <?php if(in_array(($vo['status']), explode(',',"1"))): ?>
                                    <a class="text-danger ajax-get confirm" href="<?php echo U('deletes?id='.$vo['id']); ?>"
                                       title="删除">删除</a>
                                    <?php endif; if(in_array(($vo['status']), explode(',',"2,8,10"))): ?>
                                    <a class="text-warning ajax-get confirm" href="<?php echo U('set_czing?id='.$vo['id']); ?>"
                                       title="将订单设置为充值中，一般手工单才这样做">设为充值中</a>
                                    <?php endif; if(in_array(($vo['status']), explode(',',"2,3,8,9,10,11"))): ?>
                                    <a class="ajax-get confirm" href="<?php echo U('set_chenggong?id='.$vo['id']); ?>"
                                       title="将订单设置为充值成功">充值成功</a>
                                    <a class="text-danger ajax-get confirm" href="<?php echo U('set_shibai?id='.$vo['id']); ?>"
                                       title="将订单设置为充值失败，谨慎操作">充值失败</a>

                                    <?php endif; if(in_array(($vo['is_apart']), explode(',',"0,2"))): if(in_array(($vo['status']), explode(',',"2,3,8,9,10,11"))): ?>
                                    <a class="text-warning ajax-get confirm prompt"
                                       prompt-title="填写充值完成的面值(0<充值面值<订单总面值)" href="<?php echo U('set_partsus?id='.$vo['id']); ?>"
                                       title="操作部分充值完成，输入充值的面值">部分完成</a>
                                    <?php endif; endif; if(in_array(($vo['status']), explode(',',"4"))): ?>
                                    <a class="text-warning open-window no-refresh"
                                       href="<?php echo C('WEB_URL'); ?>yrapi.php/open/voucher/id/<?php echo $vo['id']; ?>.html"
                                       title="凭证">凭证</a>
                                    <?php endif; if(in_array(($vo['status']), explode(',',"5,12"))): ?>
                                    <a class="text-danger ajax-get confirm" href="<?php echo U('refund?id='.$vo['id']); ?>"
                                       title="操作退款">操作退款</a>
                                    <?php endif; ?>
                                    <a class="open-window no-refresh" style="color: #000"
                                       href="<?php echo U('edit_moible_blacklist?mobile='.$vo['mobile']); ?>"
                                       title="加入黑名单">拉黑</a>
                                </td>
                            </tr>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="page">
                        <?php echo $_list->render(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/public/admin/js/laydate/laydate.js"></script>
<script>
    $('#prefix_txt').change(function() {
    var value = $(this).val();
    $("#prefix_txt2").val(value);
  });  
</script>
<script>
    //执行一个laydate实例
    laydate.render({
        elem: '#begin_time',
        type: 'datetime',
        done: function (value, date, endDate) {
            $('#begin_time').val(value);
        }
    });
    laydate.render({
        elem: '#end_time',
        type: 'datetime',
        done: function (value, date, endDate) {
            $('#end_time').val(value);
        }
    });
    $(".tiptext").click(function () {
        var text = $(this).data('text');
        layer.alert(text, {
            skin: 'layui-layer-molv' //样式类名
            , closeBtn: 0
        }, function () {
            layer.closeAll();
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
    });
</script>
<script>
    var btn = document.getElementsByClassName("js-copy");
    var clipboard = new Clipboard(btn);//实例化
    //复制成功执行的回调，可选
    clipboard.on('success', function (e) {
        layer.msg('复制成功！');
    });

    //复制失败执行的回调，可选
    clipboard.on('error', function (e) {
        layer.msg('复制失败！');
    });

</script>
</body>
</html>
