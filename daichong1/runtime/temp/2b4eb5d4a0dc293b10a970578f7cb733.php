<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:74:"/www/wwwroot/daichong1/public/../application/admin/view/webcfg/config.html";i:1655264244;s:64:"/www/wwwroot/daichong1/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>配置管理-非开发人员勿动此栏目内容</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-2 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-primary open-window btn-sm" title="新增配置"
                                   href="<?php echo U('add',array('pid'=>I('get.pid',0))); ?>"><i class="fa fa-plus"></i> 新增</a>
                            </div>
                            <div class="form-group">
                                <a class="btn btn-outline btn-warning btn-sm" href="<?php echo U('sort'); ?>"><i
                                        class="fa fa-sort-alpha-asc"></i> 排序</a>
                            </div>
                            <div class="form-group">
                                <a class="btn btn-white open-reload btn-sm"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                        </div>
                        <div class="col-md-10 m-b-xs form-inline text-right">
                            <div class="form-group">
                                <label class="control-label">分组:</label>
                                <select id="group_type" class="input-sm form-control input-s-sm inline serach_selects"
                                        name="group_type">
                                    <option value="-1">请选择分组</option>
                                    <option value="0"
                                    <?php if(0==I('group_type')&&I('group_type')!=null){ echo "selected='selected'"; } ?>
                                    >未分组</option>
                                    <?php if(is_array($group) || $group instanceof \think\Collection || $group instanceof \think\Paginator): $i = 0; $__LIST__ = $group;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$group): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if($key==I('group_type')){ echo "selected='selected'"; } ?>
                                    ><?php echo $group; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" placeholder="请输入关键词" class="input-sm form-control search-input" name="key"
                                           value="<?php echo I('key'); ?>"> <span class="input-group-btn">
                                        <button type="submit" class="btn btn-sm btn-primary" id="search"
                                                url="<?php echo U('config'); ?>"> 搜索</button> </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>标题</th>
                                <th>名称</th>
                                <th>分组</th>
                                <th>类型</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(!(empty($list) || (($list instanceof \think\Collection || $list instanceof \think\Paginator ) && $list->isEmpty()))): if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$config): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $config['title']; ?></td>
                                <td>
                                    <?php if(in_array(($config['sys']), explode(',',"0"))): ?>
                                    <a href="<?php echo U('add?id='.$config['id']); ?>"><?php echo $config['name']; ?></a>
                                    <?php else: ?>
                                    <a><?php echo $config['name']; ?></a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(in_array(($config['group']), explode(',',"0"))): else: ?>
                                    <?php echo C('CONFIG_GROUP_LIST')[$config['group']]; endif; ?>
                                </td>
                                <td><?php echo C('CONFIG_TYPE_LIST')[$config['type']]; ?></td>
                                <td><?php echo $config['status']==1?'显示':'隐藏'; ?></td>
                                <td>
                                    <?php if($config['status'] == '1'): ?>
                                    <a class="text-warning ajax-get"
                                       href="<?php echo U('set_status?id='.$config['id'].'&status=0'); ?>" title="禁用">隐藏</a>
                                    <?php else: ?>
                                    <a class="text-info ajax-get"
                                       href="<?php echo U('set_status?id='.$config['id'].'&status=1'); ?>"
                                       title="启用">显示</a>
                                    <?php endif; ?>

                                    <a class="open-window" title="编辑"
                                       href="<?php echo U('add',array('id'=>$config['id'])); ?>"><span
                                            class="text-navy">编辑</span></a>
                                    <?php if(in_array(($config['sys']), explode(',',"0"))): ?>
                                    <a class="ajax-get confirm"
                                       href="<?php echo U('del',array('id'=>$config['id'])); ?>"><span
                                            style="color: #b31a2f">删除</span></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; endif; else: echo "" ;endif; else: ?>
                            <td colspan="12" style="text-align:center">aOh! 暂时还没有内容!</td>
                            <?php endif; ?>
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
            radioClass: 'iradio_square-green',
        });
    });
</script>
</body>
</html>
