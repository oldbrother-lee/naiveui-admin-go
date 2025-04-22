<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:82:"/www/wwwroot/115.126.57.143/public/../application/admin/view/reapi/param_edit.html";i:1655264244;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>接口套餐编辑</h5>
                    <div class="ibox-tools">
                    </div>
                </div>
                <div class="ibox-content">
                    <form method="post" class="form-horizontal" action="<?php echo U(''); ?>">
                        <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
                        <input type="hidden" name="reapi_id" value="<?php echo I('reapi_id'); ?>">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>套餐名称<span style="margin-left: 8px;color: #f00;">自己定义接口套餐名称，方便识别;填写的时候带数字面值且只有面值的数字，比如“100元”,"快充100","四川100电费"</span></label>
                                <input type="text" class="form-control" name="desc" value="<?php echo $info['desc']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>成本<span style="margin-left: 8px;color: #aaa;">选填，填写以后便于计算利润</span></label>
                                <input type="text" class="form-control" name="cost" value="<?php echo $info['cost']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3">
                                <label>参数1<span style="margin-left: 8px;color: #aaa;">依据参数定义填写</span></label>
                                <input type="text" class="form-control" name="param1" value="<?php echo $info['param1']; ?>">
                            </div>
                            <div class="col-sm-3">
                                <label>参数2<span style="margin-left: 8px;color: #aaa;">依据参数定义填写</span></label>
                                <input type="text" class="form-control" name="param2" value="<?php echo $info['param2']; ?>">
                            </div>
                            <div class="col-sm-3">
                                <label>配置3<span style="margin-left: 8px;color: #aaa;">依据参数定义填写</span></label>
                                <input type="text" class="form-control" name="param3" value="<?php echo $info['param3']; ?>">
                            </div>
                            <div class="col-sm-3">
                                <label>配置4<span style="margin-left: 8px;color: #aaa;">依据参数定义填写</span></label>
                                <input type="text" class="form-control" name="param4" value="<?php echo $info['param4']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label>允许传单省份<span style="margin-left: 8px;color: #aaa;">话费/流量/电费生效</span></label>
                                <input type="text" class="form-control" name="allow_pro" value="<?php echo $info['allow_pro']; ?>"
                                       placeholder="多个用英文,隔开，如：广东,福建" autocomplete="off">
                            </div>
                            <div class="col-sm-6">
                                <label>允许传单城市<span style="margin-left: 8px;color: #aaa;">话费/流量/电费生效(电费带市，话费不带市)</span></label>
                                <input type="text" class="form-control" name="allow_city" value="<?php echo $info['allow_city']; ?>"
                                       placeholder="多个用英文,隔开，如：深圳,东莞" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label>禁止传单省份<span style="margin-left: 8px;color: #aaa;">话费/流量/电费生效</span></label>
                                <input type="text" class="form-control" name="forbid_pro" value="<?php echo $info['forbid_pro']; ?>"
                                       placeholder="多个用英文,隔开，如：广东,福建" autocomplete="off">
                            </div>
                            <div class="col-sm-6">
                                <label>禁止传单城市<span style="margin-left: 8px;color: #aaa;">话费/流量/电费生效(电费带市，话费不带市)</span></label>
                                <input type="text" class="form-control" name="forbid_city" value="<?php echo $info['forbid_city']; ?>"
                                       placeholder="多个用英文,隔开，如：深圳,东莞" autocomplete="off">
                            </div>
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
