<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:67:"/var/www/html/public/../application/admin/view/product/elecity.html";i:1655264244;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>电费地区</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-2 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                                <a class="btn btn-sm btn-primary open-window" title="新增地区"
                                   href="<?php echo U('elecity_edit'); ?>"><i class="fa fa-plus"></i><strong> 新增</strong></a>
                            </div>
                        </div>

                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>首字母</th>
                                <th>地区</th>
                                <th>排序</th>
                                <th>三要素？</th>
                                <th>选城市？</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['initial']; ?></td>
                                <td><?php echo $vo['city_name']; ?></td>
                                <td><?php echo $vo['sort']; ?></td>
                                <td>
                                    <?php if($vo['need_ytype'] == '1'): ?>
                                    是
                                    | <a class="text-danger ajax-get confirm"
                                          href="<?php echo U('elecity_need_ytype',array('id'=>$vo['id'],'need_ytype'=>0)); ?>">关闭</a>
                                    | <?php echo $vo['force_ytype']==1?'强制':'可选'; ?>
                                    <a class="text-danger ajax-get confirm"
                                       href="<?php echo U('elecity_force_ytype',array('id'=>$vo['id'],'force_ytype'=>$vo['force_ytype']==1?0:1)); ?>">切换</a>
                                    <?php else: ?>
                                    否
                                    | <a class="text-info ajax-get confirm"
                                         href="<?php echo U('elecity_need_ytype',array('id'=>$vo['id'],'need_ytype'=>1)); ?>">打开</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($vo['need_city'] == '1'): ?>
                                    <a class="open-window" href="<?php echo U('elecityc',['pid'=>$vo['id']]); ?>"><?php echo $vo['city_num']; ?></a>
                                    | <a class="text-danger ajax-get confirm"
                                         href="<?php echo U('elecity_need_city',array('id'=>$vo['id'],'need_city'=>0)); ?>">关闭</a>
                                    | <?php echo $vo['force_city']==1?'强制':'可选'; ?>
                                    <a class="text-danger ajax-get confirm"
                                       href="<?php echo U('elecity_force_city',array('id'=>$vo['id'],'force_city'=>$vo['force_city']==1?0:1)); ?>">切换</a>
                                    <?php else: ?>
                                    否
                                    | <a class="text-info ajax-get confirm"
                                         href="<?php echo U('elecity_need_city',array('id'=>$vo['id'],'need_city'=>1)); ?>">打开</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($vo['is_del'] == '1'): ?>
                                    <span class="text-danger">隐藏</span>
                                    <a class="text-info ajax-get confirm"
                                       href="<?php echo U('elecity_del',array('id'=>$vo['id'],'is_del'=>0)); ?>">设为显示
                                    </a>
                                    <?php else: ?>
                                    <span class="text-info">显示</span>
                                    <a class="text-danger ajax-get confirm"
                                       href="<?php echo U('elecity_del',array('id'=>$vo['id'],'is_del'=>1)); ?>">设为隐藏
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a class="text-navy open-window" title="编辑"
                                       href="<?php echo U('elecity_edit',array('id'=>$vo['id'])); ?>">编辑</a>
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
    $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
    });
</script>
</body>
</html>
