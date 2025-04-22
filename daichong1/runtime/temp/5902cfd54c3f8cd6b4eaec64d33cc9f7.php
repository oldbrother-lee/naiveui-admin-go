<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:76:"/www/wwwroot/115.126.57.143/public/../application/admin/view/reapi/edit.html";i:1669643650;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>接口编辑</h5>
                    <div class="ibox-tools">
                    </div>
                </div>
                <div class="ibox-content">
                    <form method="post" class="form-horizontal" action="<?php echo U('edit'); ?>">
                        <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label>配置说明<span style="margin-left: 8px;color: #aaa;">非开发人员勿修改此栏目</span></label>
                                <textarea class="form-control" style="min-height: 100px"
                                          name="remark"><?php echo (isset($info['remark']) && ($info['remark'] !== '')?$info['remark']:'配置1：商户ID，配置2：秘钥，配置3：回调地址；配置4：接口地址'); ?></textarea>
                            </div>
                            <div class="col-sm-6">
                                <label>套餐参数说明<span style="margin-left: 8px;color: #aaa;">非开发人员勿修改此栏目</span></label>
                                <textarea class="form-control" style="min-height: 100px" name="api_remark"><?php echo (isset($info['api_remark']) && ($info['api_remark'] !== '')?$info['api_remark']:'参数1：产品ID；参数2：面值(非必填)；参数3：价格(非必填)'); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>平台名称<span style="margin-left: 8px;color: #aaa;">一般是用渠道的名字，便于自己区分</span></label>
                                <input type="text" class="form-control" name="name" value="<?php echo $info['name']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>配置1<span style="margin-left: 8px;color: #aaa;">根据配置说明填写</span></label>
                                <input type="text" class="form-control" name="param1" value="<?php echo $info['param1']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>配置2<span style="margin-left: 8px;color: #aaa;">根据配置说明填写</span></label>
                                <input type="text" class="form-control" name="param2" value="<?php echo $info['param2']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>配置3<span style="margin-left: 8px;color: #aaa;">根据配置说明填写</span></label>
                                <input type="text" class="form-control" name="param3" value="<?php echo $info['param3']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>配置4<span style="margin-left: 8px;color: #aaa;">根据配置说明填写</span></label>
                                <input type="text" class="form-control" name="param4" value="<?php echo $info['param4']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>配置5<span style="margin-left: 8px;color: #aaa;">根据配置说明填写</span></label>
                                <input type="text" class="form-control" name="param5" value="<?php echo $info['param5']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <label>天<span style="margin-left: 8px;color: #aaa;">每N天限制单个号码，填0代表不限制</span></label>
                                <input type="text" class="form-control" name="mb_limit_day"
                                       value="<?php echo $info['mb_limit_day']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>条数<span style="margin-left: 8px;color: #aaa;">限制单个号码进单，填0代表不限制</span></label>
                                <input type="text" class="form-control" name="mb_limit_count"
                                       value="<?php echo $info['mb_limit_count']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>金额<span style="margin-left: 8px;color: #aaa;">限制单个号码进单，填0代表不限制</span></label>
                                <input type="text" class="form-control" name="mb_limit_price"
                                       value="<?php echo $info['mb_limit_price']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <label>天<span style="margin-left: 8px;color: #aaa;">每N天限制所有，填0代表不限制</span></label>
                                <input type="text" class="form-control" name="mb_alllimit_day"
                                       value="<?php echo $info['mb_alllimit_day']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>条数<span style="margin-left: 8px;color: #aaa;">限制所有进单，填0代表不限制</span></label>
                                <input type="text" class="form-control" name="mb_alllimit_count"
                                       value="<?php echo $info['mb_alllimit_count']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>金额<span style="margin-left: 8px;color: #aaa;">限制所有进单，填0代表不限制</span></label>
                                <input type="text" class="form-control" name="mb_alllimit_price"
                                       value="<?php echo $info['mb_alllimit_price']; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-4">
                                <label>通知后再做订单失败处理<span style="margin-left: 8px;color: #aaa;">先要跟通道沟通好后再打开这里</span></label>
                                <select class="form-control m-b" name="is_nfystau">
                                    <option value="1"
                                    <?php if($info['is_nfystau']==1){ echo "selected='selected'"; } ?>>打开</option>
                                    <option value="0"
                                    <?php if($info['is_nfystau']==0){ echo "selected='selected'"; } ?>>关闭</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label>自动查单<span style="margin-left: 8px;color: #aaa;">先要配置好自动查单才能打开</span></label>
                                <select class="form-control m-b" name="is_self_check">
                                    <option value="1"
                                    <?php if($info['is_self_check']==1){ echo "selected='selected'"; } ?>>打开</option>
                                    <option value="0"
                                    <?php if($info['is_self_check']==0){ echo "selected='selected'"; } ?>>关闭</option>
                                </select>
                            </div>

                            <div class="col-sm-4">
                                <label>转网退回<span style="margin-left: 8px;color: #aaa;">先去网站设置-转网功能-调至api拦截</span></label>
                                <select class="form-control m-b" name="is_zwback">
                                    <option value="1"
                                    <?php if($info['is_zwback']==1){ echo "selected='selected'"; } ?>>打开</option>
                                    <option value="0"
                                    <?php if($info['is_zwback']==0){ echo "selected='selected'"; } ?>>关闭</option>
                                </select>
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
