<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:78:"/www/wwwroot/115.126.57.143/public/../application/agent/view/porder/index.html";i:1686271876;s:65:"/www/wwwroot/115.126.57.143/application/agent/view/public/he.html";i:1686832572;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo C('WEB_SITE_TITLE'); ?></title>
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="/public/agent/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/public/agent/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link href="/public/agent/css/animate.css" rel="stylesheet">
    <link href="/public/agent/css/style.css?v8" rel="stylesheet">
    <link href="/public/agent/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="/public/agent/css/layx.min.css" rel="stylesheet"/>
    <link href="/public/agent/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/public/agent/css/animate.css" rel="stylesheet">
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
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>订单列表(当前结果：<?php echo $_total; ?>条)</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-12 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                                <span>&nbsp;总金额：￥<?php echo $total_price; ?></span>
                            </div>
                        </div>
                        <div class="col-md-12 m-b-xs form-inline text-left">
                            <div class="form-group">
                                <span class="control-label">开始：</span>
                                <input type="text" name="begin_time" id="begin_time" value="<?php echo I('begin_time'); ?>"
                                       class="input-sm input-s-sm serach_selects" style="border: 1px solid #e5e6e7;"
                                       placeholder="下单开始日期" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <span class="control-label">&nbsp;结束：</span>
                                <input type="text" name="end_time" id="end_time" value="<?php echo I('end_time'); ?>"
                                       style="border: 1px solid #e5e6e7;"
                                       class="input-sm input-s-sm serach_selects" placeholder="下单结束日期"
                                       autocomplete="off">
                            </div>
                            <div class="form-group">
                                <a class="btn btn-sm btn-white kuaijie_time"
                                   data-strat="<?php echo date('Y-m-d 00:00:00', time());?>"
                                   data-end="<?php echo date('Y-m-d 23:59:59', time());?>">今日</a>
                                <a class="btn btn-sm btn-white kuaijie_time"
                                   data-strat="<?php echo date('Y-m-01 00:00:00', time());?>"
                                   data-end="<?php echo date('Y-m-d 23:59:59', time());?>">本月</a>
                                <a class="btn btn-sm btn-white kuaijie_time"
                                   data-strat="<?php echo date('Y-01-01 00:00:00', time());?>"
                                   data-end="<?php echo date('Y-m-d 23:59:59', time());?>">本年</a>
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
                                <?php $statusarr=C('ORDER_STUTAS');?>
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
                                <label class="control-label" title="提交了申请退款请求，还未退的可以查询">申请退单:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="apply_refund" style="width: auto;">
                                    <option value="0">请选择</option>
                                    <option value="1" <?php if(I('apply_refund')==1){ echo "selected='selected'"; } ?>>否</option>
                                    <option value="2" <?php if(I('apply_refund')==2){ echo "selected='selected'"; } ?>>是</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">回调状态:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects"
                                        name="is_notification"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <option value="1"
                                    <?php if(I('is_notification')==1){ echo "selected='selected'"; } ?>>未回调</option>
                                    <option value="2"
                                    <?php if(I('is_notification')==2){ echo "selected='selected'"; } ?>
                                    >回调成功</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">批量手机:</label>
                                <textarea name="batch_mobile" class="form-control"
                                          placeholder="批量手机查询，多个回车分割"><?php echo I('batch_mobile'); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">批量系统单号:</label>
                                <textarea name="batch_order_number" class="form-control"
                                          placeholder="批量系统单号查询，多个回车分割"><?php echo I('batch_order_number'); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">批量商户单号:</label>
                                <textarea name="batch_out_trade_num" class="form-control"
                                          placeholder="批量商户单号查询，多个回车分割"><?php echo I('batch_out_trade_num'); ?></textarea>
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
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">查询条件:</label>
                                <select class="input-sm form-control input-s-sm inline" name="query_name"
                                        style="width: auto;">
                                    <option value="">模糊</option>
                                    <option value="order_number"
                                    <?php if(I('query_name')=='order_number'){ echo "selected='selected'"; } ?>
                                    >订单号</option>
                                    <option value="out_trade_num"
                                    <?php if(I('query_name')=='out_trade_num'){ echo "selected='selected'"; } ?>
                                    >商户单号</option>
                                    <option value="mobile"
                                    <?php if(I('query_name')=='mobile'){ echo "selected='selected'"; } ?>>手机号</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="hidden" value="<?php echo I('excel_id'); ?>" name="excel_id"/>
                                    <input type="hidden" value="<?php echo I('status2'); ?>" name="status2"/>
                                    <input type="text" name="key" placeholder="请输入套餐/单号/手机号" value="<?php echo I('key'); ?>"
                                           class="input-sm form-control">
                                    <span class="input-group-btn"><button type="button" id="search"
                                                                          class="btn btn-sm btn-primary"
                                                                          url="<?php echo U('index'); ?>"> 搜索</button></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="button" id="excel"
                                        class="btn btn-sm btn-primary"
                                        url="<?php echo U('out_excel'); ?>"> 导出
                                </button>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-danger ajax-post" target-form="i-checks"
                                        href="<?php echo U('set_aprefunds'); ?>"
                                        title="多选框选中的订单，批量申请订单直接失败，只适用已经提交api充值的订单，当api失败时立马失败，不再提交后面的接口，如果当前接口充值成功订单还是会成功，可操作的状态：充值中">
                                    <i class="fa fa-check-square"></i>
                                    批量申请退单
                                </button>
                            </div>

                            <div class="form-group">
                                <button class="btn btn-sm btn-info prompt ajax-post " target-form="i-checks"
                                        prompt-title="填写自定义的标签内容"
                                        href="<?php echo U('lables'); ?>">
                                    <i class="fa fa-edit"></i>
                                    批量标签
                                </button>
                            </div>

                        </div>
                        <div class="col-md-12 m-b-xs form-inline text-left" style="color: #f00">
                            重要提示：批量申请退单/批量标记只会操作勾选框的订单，不是搜索列表内的订单，不勾选不会操作！<br/>
                        </div>

                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th style="text-align:center"><input type="checkbox" class="i-checks check-all">全选</th>
                                <th style="text-align:center">系统单号</th>
                                <th style="text-align:center">商户单号</th>
                                <th style="text-align:center">类型</th>
                                <th style="text-align:center">订单金额</th>
                                <th style="text-align:center">套餐</th>
                                <th style="text-align:center">充值账号</th>
                                <th style="text-align:center">归属地</th>
                                <th style="text-align:center">下单渠道</th>
                                <th style="text-align:center">运营商/地区</th>
                                <th style="text-align:center">扩展</th>
                                <th style="text-align:center">状态</th>
                                <th style="text-align:center">下单时间</th>
                                <th style="text-align:center">完成时间</th>
                                <th style="text-align:center">耗时</th>
                                <th style="text-align:center">备注</th>
                                <th style="text-align:center">返回</th>
                                <th style="text-align:center">用户标记</th>
                                <th style="text-align:center">回调</th>
                                <th style="text-align:center">凭证</th>
                                <th style="text-align:center">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td align=center style="width: 70px;">
                                    <input type="checkbox" class="i-checks ids" name="id[]" value="<?php echo $vo['id']; ?>">
                                </td>
                                <td  align=center style="font-size: 12px;">
                                    <span class="js-copy"
                                          data-clipboard-text="<?php echo $vo['order_number']; ?>"><?php echo $vo['order_number']; ?></span>
                                </td>
                                <td align=center><?php echo $vo['out_trade_num']; ?></td>
                                <td align=center><?php echo $vo['type_name']; ?></td>
                                <td align=center><?php echo $vo['total_price']; ?></td>
                                <td align=center><?php echo $vo['product_name']; ?></td>
                                <td  align=center class="js-copy" data-clipboard-text="<?php echo $vo['mobile']; ?>">
                                    <span title="充值账号，手机号，户号等"><?php echo $vo['mobile']; ?></span>
                                </td>
                                <td align=center><?php echo $vo['guishu']; ?></td>
                                <td align=center><?php echo C('CLIENT_TYPE')[$vo['client']]; ?></td>
                                <td align=center><?php echo $vo['isp']; ?></td>
                                <td align=center>
                                    <?php if($vo['param1'] || $vo['param2'] || $vo['param3']): ?>
                                    <span title="此栏目的值分别代表param1,param2,param3"><?php echo $vo['param1']; ?>/<?php echo $vo['param2']; ?>/<?php echo $vo['param3']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td align=center>
                                    <span class="label label-<?php echo $vo['status_text_color']; ?>"><?php echo C('ORDER_STUTAS')[$vo['status']]; ?></span>
                                    <?php if($vo['apply_refund'] == '1'): ?>
                                    <br/><br/><span title="申请退单的状态并不一定会退单，如果当前接口回调充值成功，订单也会变成充值成功">申请退单中..</span>
                                    <?php endif; ?>
                                </td>
                                <td align=center><?php echo time_format($vo['pay_time']); ?></td>
                                <td align=center><?php echo time_format($vo['finish_time']); ?></td>
                                <td align=center>
                                    <span class="label label-info"><?php echo elapsed_time($vo['create_time'],$vo['finish_time']); ?></span>
                                </td>
                                <td align=center>
                                    <div style="max-height: 100px;overflow-y: scroll;font-size: 12px;width: 80px;color: #f00;">
                                        <?php echo C('IS_SHOW_CLIENT_REMARK')==1?$vo['remark']:''; ?>
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
                                <td align=center><p style="color:#e00;"><?php echo $vo['user_lable']; ?></p>
                                    <a class="open-window" href="<?php echo U('lable?id='.$vo['id']); ?>"
                                       title="详情"><i class="fa fa-edit"></i></a>
                                </td>
                                <td>
                                    <?php if($vo['is_apart'] == '0'): if($vo['client'] == '4'): ?>
                                    状态：<?php echo !empty($vo['is_notification'])?'回调成功':'未回调'; ?>/<?php echo $vo['notification_num']; ?><br/>
                                    时间：<?php echo time_format($vo['notification_time']); ?><br/>
                                    <span class="tiptext text-success" data-text='<?php echo $vo['notify_url']; ?>'>回调地址</span><br/>
                                    <a class="text-danger ajax-get confirm" href="<?php echo U('notification?id='.$vo['id']); ?>"
                                       title="手动回调">手动回调</a>
                                    <?php endif; endif; ?>
                                </td>
                                <td align=center>
                                    <?php if($vo['status'] == '4'): ?>
                                    <a class="text-warning open-window no-refresh"
                                       href="<?php echo C('WEB_URL'); ?>yrapi.php/open/voucher/id/<?php echo $vo['id']; ?>.html"
                                       title="凭证">凭证</a>
                                    <?php endif; ?>
                                </td>
                                <td align=center>
                                    <a class="open-window" href="<?php echo U('complaint?porder_id='.$vo['id']); ?>" title="投诉">投诉</a>
                                    <?php if($agent_cancel_sw == '1'): if($vo['apply_refund'] == '0'): if(in_array(($vo['status']), explode(',',"2,3,10"))): ?>
                                    <a class="text-danger ajax-get confirm" href="<?php echo U('apply_cancel_order?id='.$vo['id']); ?>"
                                       title="申请退单">申请退单</a>
                                    <?php endif; endif; endif; ?>
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
<script src="/public/agent/js/laydate/laydate.js"></script>
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
    $(".kuaijie_time").click(function () {
        var strat = $(this).data('strat');
        var end = $(this).data('end');
        $("#begin_time").val(strat);
        $("#end_time").val(end);
        $("#search").trigger('click');
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
