<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:78:"/www/wwwroot/115.126.57.143/public/../application/admin/view/webcfg/clear.html";i:1728516285;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
<div class="wrapper wrapper-content">

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>清除指定时间以前的订单数据（谨慎操作，不可恢复）（清除前务必先进行数据备份）</h5>
                    <div class="ibox-tools">

                    </div>
                </div>
                <div class="ibox-content">
                    <form method="post" class="form-horizontal" action="<?php echo U('doclear'); ?>">
                        <input type="hidden" name="id" value="<?php echo (isset($info['id']) && ($info['id'] !== '')?$info['id']:''); ?>">
                        <div class="form-group">
                            <div class="col-sm-12" style="color: #f00">
                                1、清除的数据包括：订单、订单日志、导入表格、投诉记录；不会影响会员，余额，接口，套餐等信息，请放心清理！<br/>
                                2、谨慎操作，不可恢复，清除前务必先进行数据备份(宝塔面板->数据库->备份；不会操作的联系技术处理；)
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>指定时间点以前的数据(不输入时间节点则直接清空数据表,请谨慎操作)<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" name="time" id="time"
                                       class="form-control" autocomplete="off" placeholder="时间点"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>谷歌身份验证码<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="verifycode" autocomplete="off" placeholder="输入谷歌身份验证码" maxlength="6">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary ajax-post" target-form="form-horizontal">
                                    <i class="fa fa-recycle"></i>  立即清除
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/public/admin/js/laydate/laydate.js"></script>
<script>
    //执行一个laydate实例
    laydate.render({
        elem: '#time',
        type: 'datetime',
        done: function (value, date, endDate) {
            $('#time').val(value);
        }
    });
</script>
</body>

</html>
