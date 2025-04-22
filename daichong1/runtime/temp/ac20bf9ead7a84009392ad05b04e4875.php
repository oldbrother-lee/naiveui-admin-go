<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:74:"/www/wwwroot/daichong1/public/../application/admin/view/article/index.html";i:1655264242;s:64:"/www/wwwroot/daichong1/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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

<body class="gray-bg">
<div class="row wrapper wrapper-content animated fadeIn">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <div class="col-sm-7 m-b-xs">
                    <a class="btn btn-primary btn-sm open-window" href="<?php echo U('add',array('pid'=>I('get.pid',0))); ?>" title="新增"><i class="fa fa-plus"></i> 新增</a>
                    <a class="btn btn-sm btn-white open-reload"><i class="glyphicon glyphicon-repeat"></i></a>
                </div>
                <div class="ibox-content">
                    <div id="treeview1"  class="test treeview">
                        <ul class="list-group">
                            <?php echo $arctype; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

</html>
