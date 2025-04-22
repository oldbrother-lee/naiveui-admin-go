<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:80:"/www/wwwroot/115.126.57.143/public/../application/agent/view/applybla/index.html";i:1655264246;s:69:"/www/wwwroot/115.126.57.143/application/agent/view/public/header.html";i:1655264248;}*/ ?>
<!-- lq      -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo C('WEB_SITE_TITLE'); ?></title>
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="/public/agent/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/public/agent/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link href="/public/agent/css/animate.css" rel="stylesheet">
    <link href="/public/agent/css/style.css?v8" rel="stylesheet">
    <link href="/public/agent/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="/public/agent/css/layx.min.css" rel="stylesheet"/>
    <link href="/public/agent/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/public/agent/css/animate.css" rel="stylesheet">
    <script src="/public/agent/js/jquery.min.js?v=2.1.4"></script>
    <script src="/public/agent/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/public/agent/js/plugins/layer/layer.min.js"></script>
    <script src="/public/agent/js/content.js"></script>
    <script src="/public/agent/js/plugins/toastr/toastr.min.js"></script>
    <script src="/public/agent/js/dayuanren.js?v121"></script>
    <script src="/public/agent/js/layx.js" type="text/javascript"></script>
    <script src="/public/agent/js/plugins/iCheck/icheck.min.js"></script>
    <script src="/public/agent/js/clipboard.min.js"></script>
    <script src="/public/agent/js/vue.min.js?v=3.3.6"></script>
    <script src="/public/agent/js/util.js?V=1"></script>
    <script src="/public/agent/js/laydate/laydate.js" type="text/javascript"></script>
    <script src="/public/agent/js/ajaxfileupload.js?v1" type="text/javascript"></script>
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
                    <h5>打款申请记录</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups" style="margin-bottom: 10px">
                        <div class="col-md-2 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                            <div class="form-group">
                                <a class="btn btn-sm btn-primary open-window" href="<?php echo U('edit'); ?>" title="提交申请"><i class="fa fa-plus"></i> 提交申请</a>
                            </div>
                        </div>
                        <div class="col-md-10 m-b-xs form-inline text-right">

                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>申请时间</th>
                                <th>转入账户</th>
                                <th>打款户名</th>
                                <th>金额</th>
                                <th>备注(订单号/流水号/凭证)</th>
                                <th>状态</th>
                                <th>处理时间</th>
                                <th>处理备注</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo time_format($vo['create_time']); ?></td>
                                <td><?php echo $vo['platform_account']; ?></td>
                                <td><?php echo $vo['account']; ?></td>
                                <td><?php echo $vo['money']; ?></td>
                                <td><?php echo $vo['remark']; ?></td>
                                <td><?php echo C('APPLYBLA_STATUS')[$vo['status']]; ?></td>
                                <td><?php echo time_format($vo['deal_time']); ?></td>
                                <td><?php echo $vo['deal_remark']; ?></td>
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

</body>
</html>
