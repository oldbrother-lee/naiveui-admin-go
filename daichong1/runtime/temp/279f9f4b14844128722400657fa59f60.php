<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:81:"/www/wwwroot/115.126.57.143/public/../application/admin/view/qudan/addconfig.html";i:1728091772;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
    * {
        margin: 0;
        padding: 0;
    }

    .main {
        background: #fff;
        padding: 20px;
        color: #404040;
    }

    h2 {
        display: block;
        font-size: 1.5em;
        -webkit-margin-before: 0.83em;
        -webkit-margin-after: 0.83em;
        -webkit-margin-start: 0px;
        -webkit-margin-end: 0px;
        font-weight: bold;
    }
</style>
<body class="gray-bg">
<div class="wrapper wrapper-content" id="page">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo isset($info['id'])?'编辑':'新增'; ?>取单配置</h5>
                </div>
                <div class="ibox-content">
                    <form role="form" action="<?php echo U(); ?>" method="post" class="menu_form form-horizontal">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>渠道ID</label>
                                <input type="text" placeholder="请输入渠道ID" class="form-control" name="vender_id"
                                       value="<?php echo (isset($info['vender_id']) && ($info['vender_id'] !== '')?$info['vender_id']:''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>运营商<span style="margin-left: 8px;color: #aaa;">可单选多选，单选移动，多选移动,联通使用,拼接</span></label>
                                <input type="text" placeholder="请输入运营商" class="form-control" name="operator_id"
                                       value="<?php echo (isset($info['operator_id']) && ($info['operator_id'] !== '')?$info['operator_id']:''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>省份<span style="margin-left: 8px;color: #aaa;">可单选多选，单选广东，多选广东,广西使用,拼接</span></label>
                                <input type="text" placeholder="请输入省份" class="form-control" name="prov_code"
                                       value="<?php echo (isset($info['prov_code']) && ($info['prov_code'] !== '')?$info['prov_code']:''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>面值<span style="margin-left: 8px;color: #aaa;">可单选5,多选5,10,区间5-10</span></label>
                                <input type="text" placeholder="请输入面值" class="form-control" name="amount"
                                       value="<?php echo (isset($info['amount']) && ($info['amount'] !== '')?$info['amount']:''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>获取订单数量</label>
                                <input type="text" placeholder="请输入获取订单数量" class="form-control" name="order_num"
                                       value="<?php echo (isset($info['order_num']) && ($info['order_num'] !== '')?$info['order_num']:''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>状态</label>
                                <div>
                                    <input type="radio" name="status"
                                           value="1" <?php if($info['status']==1){ echo "checked"; } ?>
                                    > <i></i> 正常</label>
                                    <input type="radio" name="status"
                                           value="2" <?php if($info['status']==2){ echo "checked"; } ?>
                                    > <i></i> 隐藏</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <input type="hidden" name="id" value="<?php echo (isset($info['id']) && ($info['id'] !== '')?$info['id']:''); ?>">
                                <button class="btn btn-primary ajax-post" type="submit" target-form="menu_form">
                                    <strong>确 定</strong></button>
                                <button class="btn btn-white" style="margin-left: 10px;"
                                        onclick="javascript:history.back(-1);return false;"><strong>返 回</strong>
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