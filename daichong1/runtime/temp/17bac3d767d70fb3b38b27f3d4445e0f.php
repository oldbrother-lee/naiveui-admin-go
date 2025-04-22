<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:81:"/www/wwwroot/115.126.57.143/public/../application/admin/view/porder/upstatus.html";i:1655264244;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
    .lables {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
    }

    .lables span {
        background-color: #f1f1f1;
        border-radius: 4px;
        padding: 5px 10px 5px 10px;
        margin-right: 10px;
        margin-bottom: 10px;
    }
</style>
<body class="gray-bg">
<div class="wrapper wrapper-content">

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>强制修改状态</h5>
                    <div class="ibox-tools">

                    </div>
                </div>
                <div class="ibox-content">
                    <form method="post" class="form-horizontal" action="<?php echo U('upstatus'); ?>">
                        <input type="hidden" name="id" value="<?php echo (isset($info['id']) && ($info['id'] !== '')?$info['id']:''); ?>">
                        <div class="form-group">
                            <div class="col-sm-12">
                            直接修改状态不会自动触发提交API、退款操作!
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <?php $statusarr=C('PORDER_STATUS');?>
                                <label>状态:</label>
                                <select class="form-control" name="status">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($statusarr) || $statusarr instanceof \think\Collection || $statusarr instanceof \think\Paginator): $i = 0; $__LIST__ = $statusarr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if($info && $info['status']==$key){ echo "selected='selected'"; } ?>
                                    ><?php echo $vo; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>修改备注<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="remark" value="" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary ajax-post" target-form="form-horizontal">
                                    保存
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
