<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:64:"/var/www/html/public/../application/admin/view/weixin/index.html";i:1655264244;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
    .td {
        max-width: 100px;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }
</style>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>客户端管理</h5>
                </div>
                <div class="ibox-content">
                    <div class="tabs-container">
                        <?php $types=C('WEIXIN_TYPE');?>
                        <ul class="nav nav-tabs">
                            <?php if(is_array($types) || $types instanceof \think\Collection || $types instanceof \think\Paginator): $i = 0; $__LIST__ = $types;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <li class="<?php echo $type==$key?'active':''; ?> typetab" data-url="<?php echo U('index',['type'=>$key]); ?>">
                                <a data-toggle="tab" href="#tab-<?php echo $key; ?>"
                                   aria-expanded="false"><?php echo $vo; ?></a>
                            </li>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                    </div>
                    <div class="row input-groups" style="margin-top: 10px;">
                        <div class="col-md-2 form-inline text-left">
                            <div class="form-group">
                                <a href="<?php echo U('editw',['type'=>$type]); ?>" class="btn btn-sm btn-primary open-window" title="新增"
                                   style="padding: 4px 12px;" type="button"><i
                                        class="fa fa-plus"></i> 新增</a>
                            </div>
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                        </div>
                    </div>
                    <p style="color:#f00;">（1）可添加多个微信公众号授权登录，可以使用不同微信商户，各自独立收款。<br/>
                        （2）设置公众号菜单链接的时候，请复制列表中客户端链接填写，否则无法正常打开授权<br/>
                        （3）服务器填写到公众号服务器配置中并开启，位置：设置与开发->基本配置->服务器配置，切记消息加密方式选择“明文模式”<br/>
                        （4）如果要设置前端其他链接到公众号菜单，可点客户端链接进去复制！<br/>
                        （5）可以打开代理商的H5分销功能（用户->代理商->代理商->打开H5分销），在代理后台可以自己配置公众号信息，用他们自己的商户收款营销（代理的客户下单对应他成本扣除他的余额）<br/>
                        （6）代理端添加的公众号和H5不具备分销功能。<br/>
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>名称</th>
                                <th>会员</th>
                                <th>appid</th>
                                <th>appsecret</th>
                                <th>token</th>
                                <th>encodingaeskey</th>
                                <th>服务器地址(复制填写到公众号)</th>
                                <th>客户端地址(通过此链接打开前端)</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['name']; ?></td>
                                <td><?php if($vo['customer_id'] > '0'): ?>
                                    [<a class="text-success open-window no-refresh"
                                        href="<?php echo U('customer/index',['id'=>$vo['customer_id']]); ?>"><?php echo $vo['customer_id']; ?></a>]<?php echo $vo['username']; else: ?>
                                    -
                                    <?php endif; ?>
                                </td>
                                <td class="js-copy" data-clipboard-text="<?php echo $vo['appid']; ?>"><?php echo $vo['appid']; ?></td>
                                <td class="td js-copy" data-clipboard-text="<?php echo $vo['appsecret']; ?>"><?php echo $vo['appsecret']; ?></td>
                                <td class="td js-copy" data-clipboard-text="<?php echo $vo['token']; ?>"><?php echo $vo['token']; ?></td>
                                <td class="td js-copy" data-clipboard-text="<?php echo $vo['encodingaeskey']; ?>"><?php echo $vo['encodingaeskey']; ?>
                                </td>
                                <td class="td js-copy" data-clipboard-text="<?php echo $vo['apiurl']; ?>"><?php echo $vo['apiurl']; ?></td>
                                <td class="td js-copy"
                                    data-clipboard-text="<?php echo C('WEB_URL'); ?>#/pages/index/index?appid=<?php echo $vo['id']; ?>">
                                    <?php echo C('WEB_URL'); ?>#/pages/index/index?appid=<?php echo $vo['id']; ?>
                                </td>
                                <td><?php echo $vo['status']; ?></td>
                                <td>
                                    <a class="open-window text-info open-window" title="编辑"
                                       href="<?php echo U('editw?id='.$vo['id']); ?>">基本配置</a>
                                    <?php if($vo['type'] == '1'): ?>
                                    <a class="open-window text-info open-window" title="微信通知"
                                       href="<?php echo U('editw',['id'=>$vo['id'],'tmp'=>'editwx']); ?>">微信通知</a>
                                    <?php endif; ?>
                                    <a class="open-window text-info open-window" title="微信支付"
                                       href="<?php echo U('editw',['id'=>$vo['id'],'tmp'=>'editwxp']); ?>">微信支付</a>
                                    <?php if($vo['type'] == '2'): ?>
                                    <a class="open-window text-info open-window" title="支付宝支付"
                                       href="<?php echo U('editw',['id'=>$vo['id'],'tmp'=>'editalp']); ?>">支付宝支付</a>
                                    <?php endif; ?>
                                    <a class="open-window text-info open-window" title="内容设置"
                                       href="<?php echo U('editw',['id'=>$vo['id'],'tmp'=>'editc']); ?>">内容设置</a>
                                    <?php if($vo['type'] == '1'): ?>
                                    <a class="open-window text-info open-window no-refresh" title="菜单设置"
                                       href="<?php echo U('menu?id='.$vo['id']); ?>">菜单设置</a>
                                    <a class="open-window text-info open-window no-refresh" title="自动回复"
                                       href="<?php echo U('reply?id='.$vo['id']); ?>"> 自动回复</a>
                                    <a class="open-window text-info open-window no-refresh" title="模板消息配置"
                                       href="<?php echo U('templetmsg?id='.$vo['id']); ?>"> 模板消息配置</a>
                                    <a class="open-window text-info open-window no-refresh" title="素材列表"
                                       href="<?php echo U('material?id='.$vo['id']); ?>"> 素材列表</a>
                                    <?php endif; ?>
                                    <a class="open-window text-info open-window no-refresh" title="广告位"
                                       href="<?php echo U('ad/pindex?appid='.$vo['appid']); ?>">广告位</a>
                                    <a class="open-window text-info open-window no-refresh" title="帮助中心"
                                       href="<?php echo U('helptxt/index?appid='.$vo['appid']); ?>">帮助中心</a>
                                    <a class="open-window text-info open-window no-refresh" title="客户端链接"
                                       href="<?php echo U('url?id='.$vo['id']); ?>">客户端链接</a>
                                    <a class="ajax-get confirm text-danger" title="删除"
                                       href="<?php echo U('deletes?id='.$vo['id']); ?>">删除</a>
                                </td>
                            </tr>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var btn = document.getElementsByClassName("js-copy");
    var clipboard = new Clipboard(btn);//实例化
    //复制成功执行的回调，可选
    clipboard.on('success', function (e) {
        layer.msg('复制成功！');
    });

    //复制失败执行的回调，可选
    clipboard.on('error', function (e) {
        layer.msg('复制失败！');
    });

    $(".typetab").click(function () {
        var url = $(this).data('url');
        window.location.href = url;
    });
</script>
</body>
</html>
