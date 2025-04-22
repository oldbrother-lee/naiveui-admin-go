<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:79:"/www/wwwroot/115.126.57.143/public/../application/admin/view/daichongs/add.html";i:1728733973;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>代收账号管理</h5>
                    <div class="ibox-tools">
                    </div>
                </div>
                <div class="ibox-content">
                    <form method="post" class="form-horizontal" action="<?php echo U(''); ?>">


                        <div class="form-group">
                            <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
                            <div class="col-sm-4">
                                <label>appkey<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="user" value="<?php echo $info['user']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>ApSecret<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="pwd" value="<?php echo $info['pwd']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>账号id<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="child" value="<?php echo $info['child']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>渠道id<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="zhuanqu" value="<?php echo $info['zhuanqu']; ?>">
                            </div>
                            <div class="col-sm-4">
                                 <label>账号类型<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select name="type" class="form-control m-b">
                                <option value="1" <?php if(1 == $info['type']){ echo "selected"; } ?>>取单</option>
                                <option value="2" <?php if(2 == $info['type']){ echo "selected"; } ?>>推单</option>
                                </select>
                            </div>
                            <!--<div class="col-sm-4">-->
                            <!--    <label>代理商id<span style="margin-left: 8px;color: #aaa;"></span></label>-->
                            <!--    <input type="text" class="form-control" name="customer_id" value="">-->
                            <!--</div>-->
<!--                            <div class="col-sm-3">-->
<!--                                <label>配置4<span style="margin-left: 8px;color: #aaa;">依据参数定义填写</span></label>-->
<!--                                <input type="text" class="form-control" name="param4" value="">-->
<!--                            </div>-->
                        </div>


                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary ajax-post" target-form="form-horizontal">
                                    确定
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

</html>
