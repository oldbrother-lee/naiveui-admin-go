<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:77:"/www/wwwroot/115.126.57.143/public/../application/admin/view/member/edit.html";i:1655264244;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5> 编辑管理员</h5>
                </div>
                <div class="ibox-content">
                    <form method="post" class="form-horizontal" action="<?php echo U('Member/edit'); ?>">
                        <input type="hidden" name="id" value="<?php echo $info['id']; ?>">

                        <div class="form-group ">
                            <div class="col-sm-12">
                                <label>登录名</label>
                                <input type="text" class="form-control" name="nickname" value="<?php echo $info['nickname']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>性别</label>
                            </div>
                            <div class="col-sm-12">

                                <input type="radio" name="sex" value="1" <?php if($info['sex']==1){ echo "checked"; } ?>
                                > <i></i> 男</label>
                                <input type="radio" name="sex" value="0" <?php if($info['sex']==0){ echo "checked"; } ?>
                                > <i></i> 女</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>禁用&启用</label>
                            </div>
                            <div class="col-sm-12">
                                <input type="radio" name="status"
                                       value="0" <?php if($info['status']==0){ echo "checked"; } ?>> <i></i> 禁用</label>
                                <input type="radio" name="status"
                                       value="1" <?php if($info['status']==1){ echo "checked"; } ?>> <i></i> 启用</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>管理员头像</label>
                                <img id="headimg" src="<?php echo $info['headimg']; ?>"
                                     style="display: block;margin-bottom: 10px; border-radius:50%;width: 100px;height: auto;">
                                <button class="btn btn-success open-img-window" style="width:100px;"
                                        data-url="<?php echo U('widget/images'); ?>" type="button" data-max="1" data-name="headimg">
                                    <i class="fa fa-upload"></i>&nbsp;&nbsp;<span class="bold">上传</span></button>
                                <input type="hidden" name="headimg" value="<?php echo $info['headimg']; ?>"/>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary ajax-post" target-form="form-horizontal">确定
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.changeIMG = function (name, res) {
        $("#" + name).attr("src", res);
        $("[name=" + name + "]").val(res);
    }
</script>

</body>

</html>
