<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:72:"/www/wwwroot/daichong1/public/../application/admin/view/product/api.html";i:1655264244;s:64:"/www/wwwroot/daichong1/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
    .table-bordered > tbody > tr:hover {
        background-color: #f5f5f5
    }
</style>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>套餐API</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-2 form-inline text-left">
                            <a class="btn btn-sm btn-primary open-window"
                               href="<?php echo U('edit_api',['product_id'=>I('id')]); ?>" title="选择提交接口"><i
                                    class="fa fa-plus"></i> 选择</a>
                            <a class="btn btn-sm btn-white open-reload"><i
                                    class="glyphicon glyphicon-repeat"></i></a>
                        </div>

                        <div class="col-md-10 m-b-xs form-inline text-right"></div>
                    </div>
                    <div class="row input-groups">
                        <div class="col-md-12">
                            <h4>可以添加多个接口API，如果接口回调“充值失败”会按照顺序依次提交接口，如果所有的接口都使用了还未充值成功则“充值失败”，其中一个接口“充值成功”将停止往下执行。</h4>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>渠道</th>
                                <th>套餐</th>
                                <th>提交次数</th>
                                <th>运营商</th>
                                <th>地区限制</th>
                                <th>排序</th>
                                <th>接口状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['name']; ?></td>
                                <td><?php echo $vo['desc']; ?></td>
                                <td><?php echo $vo['num']; ?></td>
                                <td><?php echo getISPText($vo['isp']); ?></td>
                                <td>
                                    允许：<?php echo $vo['allow_pro']; ?>-<?php echo $vo['allow_city']; ?><br/>
                                    禁止：<?php echo $vo['forbid_pro']; ?>-<?php echo $vo['forbid_city']; ?>
                                </td>
                                <td><?php echo $vo['sort']; ?></td>
                                <td><?php echo !empty($vo['status'])?'打开':'关闭'; ?></td>
                                <td>
                                    <?php if($vo['status'] == '1'): ?>
                                    <a class="text-warning ajax-get"
                                       href="<?php echo U('api_status_cg?id='.$vo['id'].'&status=0'); ?>" title="关闭">关闭</a>
                                    <?php else: ?>
                                    <a class="text-info ajax-get" href="<?php echo U('api_status_cg?id='.$vo['id'].'&status=1'); ?>"
                                       title="打开">打开</a>
                                    <?php endif; ?>
                                    <a class="open-window" href="<?php echo U('edit_api',['id'=>$vo['id'],'product_id'=>I('id')]); ?>"
                                       title="编辑">编辑</a>
                                    <a class="text-danger ajax-get confirm" href="<?php echo U('api_del?id='.$vo['id']); ?>"
                                       title="删除">删除</a>
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
</body>
</html>
