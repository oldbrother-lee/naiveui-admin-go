<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:76:"/www/wwwroot/115.126.57.143/public/../application/admin/view/porder/log.html";i:1655264244;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>订单日志</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-sm-4 m-b-xs">
                            <a class="btn btn-white open-reload"><i class="glyphicon glyphicon-repeat"></i></a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>时间</th>
                                <th>内容</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo time_format($vo['create_time']); ?></td>
                                <td class="js-copy" data-clipboard-text="<?php echo time_format($vo['create_time']); ?>-<?php echo htmlspecialchars($vo['log']); ?>"><?php echo htmlspecialchars($vo['log']); ?></td>
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
    $("#search,#excel").click(function () {
        var url = $(this).attr('url');
        var query = $('.input-groups').find('input').serialize();
        var select = $('.input-groups').find('select').serialize();
        query = query.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
        query = query.replace(/^&/g, '');
        if (url.indexOf('?') > 0) {
            url += '&' + query;
        } else {
            url += '?' + query;
        }
        if (url.indexOf('?') > 0) {
            url += '&' + select;
        } else {
            url += '?' + select;
        }
        window.location.href = url;
    });
    //回车搜索
    $(".input-sm").keyup(function (e) {
        if (e.keyCode === 13) {
            $("#search").click();
            return false;
        }
    });
    $(".serach_selects").change(function () {
        $("#search").click();
        return false;
    });
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
