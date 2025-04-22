<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:79:"/www/wwwroot/115.126.57.143/public/../application/admin/view/voucher/index.html";i:1655264244;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>凭证模板</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-2 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-primary open-window" href="<?php echo U('edit'); ?>" title="新增"><i
                                        class="fa fa-plus"></i> 新增</a>
                            </div>
                            <div class="form-group">
                                <a class="btn btn-white btn-sm open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                        </div>
                        <div class="col-md-10 m-b-xs form-inline text-right">

                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>产品类型</th>
                                <th>运营商</th>
                                <th>模板图</th>
                                <th>单号文字</th>
                                <th>手机文字</th>
                                <th>时间文字</th>
                                <th>价格文字</th>
                                <th>套餐文字</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['type_name']; ?></td>
                                <td><?php echo getISPText($vo['isp']); ?></td>
                                <td><img src="<?php echo $_prefix; ?><?php echo $vo['path']; ?>" style="height: 100px;"/></td>
                                <td><?php if($vo['is_no'] == '1'): ?>大小：<?php echo $vo['no_size']; ?>%<br/>左：<?php echo $vo['no_left']; ?>%<br/>顶：<?php echo $vo['no_top']; ?>%<br/>颜色：<?php echo $vo['no_color']; else: ?>否<?php endif; ?>
                                </td>
                                <td><?php if($vo['is_mobile'] == '1'): ?>大小：<?php echo $vo['mobile_size']; ?>像素<br/>左：<?php echo $vo['mobile_left']; ?>%<br/>顶：<?php echo $vo['mobile_top']; ?>%<br/>颜色：<?php echo $vo['mobile_color']; else: ?>否<?php endif; ?>
                                </td>
                                <td><?php if($vo['is_date'] == '1'): ?>大小：<?php echo $vo['date_size']; ?>像素<br/>左：<?php echo $vo['date_left']; ?>%<br/>顶：<?php echo $vo['date_top']; ?>%<br/>颜色：<?php echo $vo['date_color']; else: ?>否<?php endif; ?>
                                </td>
                                <td><?php if($vo['is_price'] == '1'): ?>大小：<?php echo $vo['price_size']; ?>像素<br/>左：<?php echo $vo['price_left']; ?>%<br/>顶：<?php echo $vo['price_top']; ?>%<br/>颜色：<?php echo $vo['price_color']; else: ?>否<?php endif; ?>
                                </td>
                                <td><?php if($vo['is_product'] == '1'): ?>大小：<?php echo $vo['product_size']; ?>像素<br/>左：<?php echo $vo['product_left']; ?>%<br/>顶：<?php echo $vo['product_top']; ?>%<br/>颜色：<?php echo $vo['product_color']; else: ?>否<?php endif; ?>
                                </td>
                                <td><?php echo $vo['status']==1?"上架":"下架"; ?></td>
                                <td>
                                    <a class="open-window" href="<?php echo U('edit?id='.$vo['id']); ?>" title="编辑">编辑</a>
<!--                                    <a class="text-danger ajax-get confirm" href="<?php echo U('deletes?id='.$vo['id']); ?>"-->
<!--                                       title="删除">删除</a>-->
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
