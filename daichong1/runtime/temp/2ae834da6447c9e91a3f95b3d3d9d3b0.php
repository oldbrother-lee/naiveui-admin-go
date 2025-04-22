<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:72:"/www/wwwroot/daichong1/public/../application/admin/view/auth/access.html";i:1655264242;s:64:"/www/wwwroot/daichong1/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
    .btns {
        margin: 0 10px 0 50px;
        width: 130px;
        height: 40px;
    }

    .backa {
        margin-left: 20px;
        padding: 5px 10px;
        border-radius: 5px;
    }
</style>


<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">

        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo $group['title']; ?>——访问授权</h5>
                </div>
                <div class="ibox-content">
                    后台： 以下这几个权限请务必勾选上，{主界面、主页、首页、用户资料、修改密码}；权限生效大约需要5分钟，请等几分钟再登录查看；
                    <div class="panel-body">
                        <div class="panel-group" id="accordion">
                            <?php echo $access_html; ?>
                        </div>
                    </div>
                </div>
                <div class="ibox-content">
                    <form action="<?php echo U('Auth/edit_access'); ?>" method="post" class="accessModal">
                        <input type="hidden" name="gid" value="<?php echo I('group_id'); ?>"/>
                        <textarea name="ids" id="ids" style="display: none"></textarea>
                        <button class="btn btns btn-sm btn-primary ajax-post" type="submit" target-form="accessModal">
                            <strong>确
                                定</strong></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        idsRefresh();
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
        $("input[type='checkbox']").on('ifChanged', function (event) {
            if ($(this).is(":checked")) {
                $("input[data-pid =" + $(this).val() + " ]").iCheck('check');
            } else {
                $("input[data-pid =" + $(this).val() + " ]").iCheck('uncheck');
            }
            idsRefresh();
        });

        function idsRefresh() {
            var ids = '';
            $("input[type='checkbox']").each(function () {
                if ($(this).is(":checked")) {
                    if (ids == '') {
                        ids = $(this).val();
                    } else {
                        ids = ids + ',' + $(this).val();
                    }
                }
            });
            $("#ids").val(ids);
        }
    });
</script>

</body>

</html>
