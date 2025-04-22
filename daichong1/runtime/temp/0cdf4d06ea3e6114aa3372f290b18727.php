<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:64:"/var/www/html/public/../application/admin/view/tixian/index.html";i:1655264244;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>提现列表</h5>&nbsp;&nbsp;共<?php echo $_count; ?>条&nbsp;&nbsp;&nbsp;总金额：￥<?php echo $total_money; ?>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-4 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-sm btn-info ajax-post" style="background-color: #29c505"
                                        target-form="i-checks"
                                        href="<?php echo U('shenhe',['status'=>2,'style'=>1]); ?>">
                                    <i class="fa fa-wechat"></i> 微信处理
                                </button>
                                <button class="btn btn-sm btn-success ajax-post" target-form="i-checks"
                                        href="<?php echo U('shenhe',['status'=>2,'style'=>2]); ?>">
                                    <i class="fa fa-credit-card"></i> 支付宝处理
                                </button>
                                <button class="btn btn-sm btn-info ajax-post" target-form="i-checks"
                                        href="<?php echo U('shenhe',['status'=>2,'style'=>3]); ?>">
                                    <i class="fa fa-hand-paper-o"></i> 手动成功
                                </button>
                                <button class="btn btn-sm btn-danger prompt ajax-post" target-form="i-checks"
                                        href="<?php echo U('shenhe',['status'=>3]); ?>">
                                    <i class="fa fa-user-times"></i> 批量驳回
                                </button>
                            </div>
                            <div class="form-group">
                                <button type="button" id="excel"
                                        class="btn btn-sm btn-primary"
                                        url="<?php echo U('out_excel'); ?>"><i class="fa fa-table"></i> 导出
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8 m-b-xs form-inline text-right">
                            <div class="form-group">
                                <span class="control-label">日期:</span>
                                <input type="text" name="begin_time" id="begin_time" value="<?php echo I('begin_time'); ?>"
                                       class="input-sm input-s-sm form-control" autocomplete="off" placeholder="开始时间"/>
                                <span class="control-label">-</span>
                                <input type="text" name="end_time" id="end_time" value="<?php echo I('end_time'); ?>"
                                       class="input-sm input-s-sm form-control" autocomplete="off" placeholder="结束时间"/>
                            </div>
                            <div class="form-group">
                                <label class="control-label">状态:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="status"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <option value="1"
                                    <?php if(I('status')==1){ echo "selected='selected'"; } ?>>待审核</option>
                                    <option value="2"
                                    <?php if(I('status')==2){ echo "selected='selected'"; } ?>>提现成功</option>
                                    <option value="3"
                                    <?php if(I('status')==3){ echo "selected='selected'"; } ?>>审核不通过</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" name="key" placeholder="请输入备注昵称会员ID" value="<?php echo I('key'); ?>"
                                           class="input-sm form-control">
                                    <span class="input-group-btn"><button type="button" id="search"
                                                                          class="btn btn-sm btn-primary"
                                                                          url="<?php echo U('index'); ?>"> 搜索</button></span>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th><input type="checkbox" class="i-checks check-all"></th>
                                <th>客户端</th>
                                <th>申请人</th>
                                <th>账号</th>
                                <th>方式</th>
                                <th>金额</th>
                                <th>备注</th>
                                <th>提交时间</th>
                                <th>状态</th>
                                <th>处理时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td style="width: 70px;">
                                    <?php if($vo['status'] == '1'): ?>
                                    <input type="checkbox" class="i-checks ids" name="id[]" value="<?php echo $vo['id']; ?>">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $vo['weixin_name']; ?></td>
                                <td>[<a class="text-success open-window no-refresh" href="<?php echo U('customer/index',['id'=>$vo['customer_id']]); ?>">ID:<?php echo $vo['customer_id']; ?></a>]<?php echo $vo['username']; ?></td>
                                <td>姓名：<?php echo $vo['name']; ?><br/>账号：<?php echo $vo['acount']; ?></td>
                                <td><?php echo C('TIXIAN_STYLE')[$vo['style']]; ?></td>
                                <td><?php echo $vo['money']; ?></td>
                                <td><?php echo $vo['remark']; ?></td>
                                <td><?php echo time_format($vo['create_time']); ?></td>
                                <td>
                                    <?php switch($vo['status']): case "1": ?>
                                    <span class="label label-warning">
                                    <?php echo C('TIXIAN_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "2": ?>
                                    <span class="label label-info">
                                        <?php echo C('TIXIAN_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; case "3": ?>
                                    <span class="label label-default">
                                    <?php echo C('TIXIAN_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php break; default: ?>
                                    <span class="label label-default">
                                   <?php echo C('TIXIAN_STATUS')[$vo['status']]; ?>
                                    </span>
                                    <?php endswitch; ?>
                                </td>
                                <td><?php echo time_format($vo['deal_time']); ?></td>
                                <td>
                                    <a class="open-window text-info no-refresh"
                                       href="<?php echo U('customer/balance_log',['id'=>$vo['customer_id']]); ?>" title="余额日志">余额日志</a>
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
<script>
    $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
    });
</script>
<script src="/public/admin/js/laydate/laydate.js"></script>
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
</script>
</body>
</html>
