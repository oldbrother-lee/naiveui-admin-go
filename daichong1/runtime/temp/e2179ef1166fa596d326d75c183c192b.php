<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:79:"/www/wwwroot/115.126.57.143/public/../application/admin/view/product/phone.html";i:1655264244;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>归属地列表</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-2 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                                <a class="btn btn-sm btn-primary open-window" title="新增归属地"
                                   href="<?php echo U('phone_edit'); ?>"><i class="fa fa-plus"></i><strong> 新增</strong></a>
                            </div>
                        </div>
                        <div class="col-md-10 m-b-xs form-inline text-right">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="hidden" name="customer_id" value="<?php echo I('customer_id'); ?>">
                                    <input type="text" name="key" placeholder="请输入号段/号码/地区/运营商" value="<?php echo I('key'); ?>"
                                           class="input-sm form-control">
                                    <span class="input-group-btn"><button type="button" id="search"
                                                                          class="btn btn-sm btn-primary"
                                                                          url="<?php echo U('phone'); ?>"> 搜索</button></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>号段</th>
                                <th>号码</th>
                                <th>地区</th>
                                <th>运营商</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['prefix']; ?></td>
                                <td><?php echo $vo['phone']; ?></td>
                                <td><?php echo $vo['province']; ?>-<?php echo $vo['city']; ?></td>
                                <td><?php echo $vo['isp']; ?></td>
                                <td>
                                    <a class="text-navy open-window" title="编辑"
                                       href="<?php echo U('phone_edit',array('phone'=>$vo['phone'])); ?>">编辑</a>
                                    <a class="text-danger ajax-get confirm"
                                       href="<?php echo U('phone_del',array('phone'=>$vo['phone'])); ?>">删除
                                    </a>
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
</body>
</html>
