<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:71:"/www/wwwroot/daichong1/public/../application/admin/view/menu/index.html";i:1655264244;s:64:"/www/wwwroot/daichong1/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
    .list-group {
        padding-left: 0;
        margin-bottom: 0px;
    }
</style>

<link href="/public/admin/css/plugins/treeview/bootstrap-treeview.css" rel="stylesheet">

<body class="gray-bg">
<div class="row wrapper wrapper-content animated fadeIn">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <div class="col-sm-7 m-b-xs">
                    <a class="btn btn-primary open-window no-refresh btn-sm"
                       href="<?php echo U('add',array('pid'=>I('get.pid',0),'module'=>$module)); ?>"><i
                            class="fa fa-plus"></i> 新增</a>
                    <a class="btn btn-white open-reload btn-sm"><i class="glyphicon glyphicon-repeat"></i></a>
                </div>
                <div class="ibox-content">
                    <ul class="nav nav-tabs">
                        <?php $modules=C('MENU_MODULE');if(is_array($modules) || $modules instanceof \think\Collection || $modules instanceof \think\Paginator): $k = 0; $__LIST__ = $modules;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tvo): $mod = ($k % 2 );++$k;?>
                        <li class="<?php echo $tvo==$module?'active' : ''; ?>">
                            <a href="<?php echo U('index',['module'=>$tvo]); ?>"><?php echo $tvo; ?></a></li>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </ul>
                    <div id="treeview1" class="test treeview">
                        <ul class="list-group">
                            <?php echo $menu; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/public/admin/js/plugins/treeview/bootstrap-treeview.js"></script>

<script>
    $(".click-collapse").each(function () {
        $(this).click(function () {
            if ($(this).hasClass("glyphicon-minus")) {
                $(this).removeClass("glyphicon-minus").addClass("glyphicon-plus");
                $(this).parent().parent().next().css("display", "none");
            } else if ($(this).hasClass("glyphicon-plus")) {
                $(this).removeClass("glyphicon-plus").addClass("glyphicon-minus");
                $(this).parent().parent().next().css("display", "block");
            }
        })
    })
</script>
</body>

</html>
