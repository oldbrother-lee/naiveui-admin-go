<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:71:"/www/wwwroot/daichong1/public/../application/admin/view/auth/index.html";i:1655264242;s:64:"/www/wwwroot/daichong1/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>权限组列表</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-2 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-primary btn-sm open-window" href="<?php echo U('edit'); ?>"><i
                                        class="fa fa-plus"></i> 新增</a>
                            </div>
                            <div class="form-group">
                                <a class="btn btn-white btn-sm open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                        </div>

                    </div>
                    <ul class="nav nav-tabs">
                        <?php $modules=C('MENU_MODULE');if(is_array($modules) || $modules instanceof \think\Collection || $modules instanceof \think\Paginator): $k = 0; $__LIST__ = $modules;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tvo): $mod = ($k % 2 );++$k;?>
                        <li class="<?php echo $tvo==$module?'active' : ''; ?>">
                            <a href="<?php echo U('index',['module'=>$tvo]); ?>"><?php echo $tvo; ?></a></li>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </ul>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>组</th>
                                <th>描述</th>
                                <th>权限数</th>
                                <th>成员</th>
                                <th>超级组</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['title']; ?></td>
                                <td><?php echo $vo['description']; ?></td>
                                <td><?php echo $vo['access_num']; ?>/<?php echo $vo['menu_num']; ?></td>
                                <td><?php echo $vo['mem_count']; ?>名</td>
                                <td><?php echo $vo['is_admin']==1?'是':'否'; ?></td>
                                <td><?php if($vo['status'] == '0'): ?><span
                                        class="text-danger">禁用</span><?php else: ?><span
                                        class="text-info">启用</span><?php endif; ?>
                                </td>
                                <td>
                                    <?php if($vo['status'] == '1'): ?>
                                    <a class="text-warning ajax-get" href="<?php echo U('change_status?id='.$vo['id'].'&value=0'); ?>"
                                       title="禁用">禁用</a>
                                    <?php else: ?>
                                    <a class="text-info ajax-get" href="<?php echo U('change_status?id='.$vo['id'].'&value=1'); ?>"
                                       title="启用">启用</a>
                                    <?php endif; ?>
                                    <a class="text-info open-window" title="访问授权"
                                       href="<?php echo U('access',array('group_id'=>$vo['id'],'module'=>$module)); ?>">访问授权</a>
                                    <a class="text-success open-window" title="成员管理"
                                       href="<?php echo U('Auth/user',array('group_id'=>$vo['id'],'module'=>$module)); ?>">成员管理</a>
                                    <a class="open-window" href="<?php echo U('edit?id='.$vo['id']); ?>" title="编辑">编辑</a>
                                    <a class="text-danger ajax-get confirm" href="<?php echo U('deleteauth?id='.$vo['id']); ?>"
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
