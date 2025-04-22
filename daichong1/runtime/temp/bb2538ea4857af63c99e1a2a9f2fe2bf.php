<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:76:"/www/wwwroot/115.126.57.143/public/../application/admin/view/user/infos.html";i:1655264244;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>我的资料</h5>
                </div>
                <div class="ibox-content">
                    <form method="post" action="<?php echo U('user/infos'); ?>" class="form-horizontal saveinfo">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">用户名</label>
                            <div class="col-sm-10">
                                <input type="text" disabled="" placeholder="" class="form-control"
                                       value="<?php echo $info['nickname']; ?>">
                                <input type="hidden" name="id" value="<?php echo $info['id']; ?>"/>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">密码</label>
                            <div class="col-sm-10">
                                <a data-toggle="modal" data-target="#passwordModal">修改密码</a>
                            </div>
                        </div>
                        <?php if(!$info['google_auth_secret']){?>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">谷歌验证码</label>
                            <div class="col-sm-10">
                                <a target="_blank" href="<?php echo U('index/bind_google_auth'); ?>">去设置</a>
                            </div>
                        </div>
                        <?php  }?>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">头像</label>
                            <div class="col-sm-10">
                                <img alt="image" class="img-circle" src="<?php echo $info['headimg']; ?>" id="headimg"
                                     style="width: 60px;height: 60px;">
                                <button class="btn btn-success open-img-window" type="button"
                                        data-url="<?php echo U('widget/images'); ?>" data-max="1" data-name="headimg"><i
                                        class="fa fa-upload"></i>&nbsp;&nbsp;<span class="bold">上传</span>
                                </button>
                                <input type="hidden" name="headimg" value="<?php echo $info['headimg']; ?>"/>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">性别
                            </label>
                            <div class="col-sm-10">
                                <?php if($info['sex'] == '0'): ?>
                                <div class="radio i-checks">
                                    <label>
                                        <input type="radio" checked="" value="0" name="sex"> <i></i> 男</label>
                                </div>
                                <?php else: ?>
                                <div class="radio i-checks">
                                    <label>
                                        <input type="radio" value="0" name="sex"> <i></i> 男</label>
                                </div>
                                <?php endif; if($info['sex'] == '1'): ?>
                                <div class="radio i-checks">
                                    <label>
                                        <input type="radio" checked="" value="1" name="sex"> <i></i> 女</label>
                                </div>
                                <?php else: ?>
                                <div class="radio i-checks">
                                    <label>
                                        <input type="radio" value="1" name="sex"> <i></i> 女</label>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class="btn btn-primary ajax-post" type="submit" target-form="saveinfo">保存资料
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="passwordModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form method="post" action="<?php echo U('uppwd'); ?>" class="pwdModal">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-body">
                    <p><strong>原密码</strong></p>
                    <div class="form-group">
                        <input type="hidden" value="<?php echo $info['id']; ?>" name="id">
                        <input type="password" placeholder="请输入您的原密码" value=""
                               class="form-control" name="ypwd">
                    </div>
                    <p><strong>新密码</strong></p>
                    <div class="form-group">
                        <input type="hidden" value="<?php echo $info['id']; ?>" name="id">
                        <input type="password" placeholder="请输入您的新密码" value=""
                               class="form-control" name="npwd">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
                    <button type="submit" class="btn btn-primary ajax-post" target-form="pwdModal">保存</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
    });
    window.changeIMG = function (name, res) {
        $("#" + name).attr("src", res);
        $("[name=" + name + "]").val(res);
    }
</script>
</body>
</html>
