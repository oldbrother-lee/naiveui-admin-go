<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:63:"/var/www/html/public/../application/admin/view/reapi/index.html";i:1655264244;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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

    .td {
        max-width: 100px;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }
</style>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>接口管理</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups" style="margin-bottom: 10px">
                        <div class="col-md-12 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i class="glyphicon glyphicon-repeat"></i></a>
                                <a class="btn btn-sm btn-primary open-window" title="新增-同平台的接口可以自己新增" href="<?php echo U('edit'); ?>"><i
                                        class="fa fa-plus"></i> 新增同平台接口</a>
                                <a class="btn btn-sm btn-success open-window" title="数据分析统计" href="<?php echo U('fenxi'); ?>"><i
                                        class="fa fa-calendar"></i> 数据分析</a>
                                <a class="btn btn-sm btn-primary open-window" title="接码api接口管理" href="<?php echo U('jmapi/index'); ?>"><i
                                        class="fa fa-book"></i> 接码API</a>
                            </div>
                        </div>
                        <div class="col-md-10 m-b-xs form-inline text-right">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>平台名称</th>
                                <th>配置1</th>
                                <th>配置2</th>
                                <th>配置3</th>
                                <th>配置4</th>
                                <th>配置5</th>
                                <th>说明</th>
                                <th>限制</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td width="100"><?php echo $vo['name']; ?></td>
                                <td class="tiptext td" data-text='<?php echo $vo['param1']; ?>'><?php echo $vo['param1']; ?></td>
                                <td class="tiptext td" data-text='<?php echo $vo['param2']; ?>'><?php echo $vo['param2']; ?></td>
                                <td class="tiptext td" data-text='<?php echo $vo['param3']; ?>'><?php echo $vo['param3']; ?></td>
                                <td class="tiptext td" data-text='<?php echo $vo['param4']; ?>'><?php echo $vo['param4']; ?></td>
                                <td class="tiptext td" data-text='<?php echo $vo['param5']; ?>'><?php echo $vo['param5']; ?></td>
                                <td class="tiptext td" data-text='<?php echo $vo['remark']; ?>'><?php echo $vo['remark']; ?></td>
                                <td class="tiptext td" data-text='每<?php echo $vo['mb_limit_day']; ?>天限制单号码<?php echo $vo['mb_limit_count']; ?>条/<?php echo $vo['mb_limit_price']; ?>元'><?php echo $vo['mb_limit_day']; ?>天/<?php echo $vo['mb_limit_count']; ?>条/<?php echo $vo['mb_limit_price']; ?>元</td>
                                <td>
                                    <a class="open-window" href="<?php echo U('edit?id='.$vo['id']); ?>" title="<?php echo $vo['name']; ?>">配置</a>
                                    <a class="open-window text-info" href="<?php echo U('param?id='.$vo['id']); ?>" title="<?php echo $vo['name']; ?>">套餐配置</a>
                                    <a class="ajax-get confirm text-danger" title="删除"
                                       href="<?php echo U('deletes?id='.$vo['id']); ?>">删除</a>
                                </td>
                            </tr>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
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
</body>
</html>
