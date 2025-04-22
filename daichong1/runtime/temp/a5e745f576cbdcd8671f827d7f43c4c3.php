<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:80:"/www/wwwroot/115.126.57.143/public/../application/admin/view/customer/edita.html";i:1655264242;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>代理商资料</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                        <a class="close-link">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <form method="post" class="form-horizontal" action="<?php echo U('edit'); ?>">
                        <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>商户资料<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <div>
                                    后台地址：<a href="<?php echo C('WEB_URL'); ?>agent.php" target="_blank"><?php echo C('WEB_URL'); ?>agent.php</a><br/>
                                    登录账号：<?php echo $info['username']; ?><br/>
                                    提单表格：<a href="<?php echo C('PORDER_EXCEL_IN_DOC'); ?>" target="_blank"><?php echo C('PORDER_EXCEL_IN_DOC'); ?></a><br/><br/>
                                </div>
                                <label>api资料<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <div>
                                    接口地址：<?php echo C('WEB_URL'); ?>yrapi.php/<br/>
                                    商户ID：<?php echo $info['id']; ?><br/>
                                    ApiKey：<?php echo $info['apikey']; ?>&nbsp;<a class="text-warning ajax-get confirm"
                                                            href="<?php echo U('reset_apikey?id='.$info['id']); ?>"
                                                            title="重置秘钥">重置秘钥</a><br/>
                                    接口文档：<a href="<?php echo C('API_DOC_URL'); ?>" target="_blank"><?php echo C('API_DOC_URL'); ?></a><br/>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>打开代理端分销<span style="margin-left: 8px;color: #aaa;">打开分销以后代理端给下面代理添加账号</span></label>
                                <select class="form-control m-b" name="is_agfx">
                                    <option value="0" <?php if($info && $info['is_agfx']==0){ echo "selected='selected'"; } ?> >关闭</option>
                                    <option value="1" <?php if($info && $info['is_agfx']==1){ echo "selected='selected'"; } ?> >打开</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>打开API提单<span style="margin-left: 8px;color: #aaa;">打开api提单的账号可以通过接口传单</span></label>
                                <select class="form-control m-b" name="is_openapi">
                                    <option value="0" <?php if($info && $info['is_openapi']==0){ echo "selected='selected'"; } ?> >关闭</option>
                                    <option value="1" <?php if($info && $info['is_openapi']==1){ echo "selected='selected'"; } ?> >打开</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>打开H5分销<span style="margin-left: 8px;color: #aaa;">打开分销以后代理可以用自己的公众号给客户进行分销</span></label>
                                <select class="form-control m-b" name="is_h5fx">
                                    <option value="0" <?php if($info && $info['is_h5fx']==0){ echo "selected='selected'"; } ?> >关闭</option>
                                    <option value="1" <?php if($info && $info['is_h5fx']==1){ echo "selected='selected'"; } ?> >打开</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>IP白名单<span
                                        style="margin-left: 8px;color: #aaa;">多个ip用,号隔开；未设置代表不限制</span></label>
                                <textarea class="form-control" name="ip_white_list"><?php echo $info['ip_white_list']; ?></textarea>
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
