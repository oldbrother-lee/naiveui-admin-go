<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:67:"/var/www/html/public/../application/admin/view/article/conlist.html";i:1655264242;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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

    .text-navys {
        color: #b31a2f;
    }
</style>

<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>文章列表</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-sm-5 m-b-xs">
                            <?php if(!(empty(I('typeid')) || ((I('typeid') instanceof \think\Collection || I('typeid') instanceof \think\Paginator ) && I('typeid')->isEmpty()))): ?>
                            <a href="<?php echo U('conedit'); ?>?typeid=<?php echo I('typeid'); ?>&typeid2=<?php echo I('typeid2'); ?>" class="btn btn-sm btn-primary open-window"
                               title="新增文档"
                               style="padding: 4px 12px;"
                               type="button"><i class="fa fa-plus"></i> 新增</a>
                            <?php endif; ?>
                            <a class="btn btn-sm btn-white open-reload"><i class="glyphicon glyphicon-repeat"></i></a>
                        </div>
                        <div class="col-sm-3" style="float: right;">
                            <div class="input-group">
                                <input type="text" name="key" placeholder="请输入标题、关键词" value="<?php echo I('key'); ?>"
                                       class="input-sm form-control">
                                <span class="input-group-btn"><button type="button" id="search"
                                                                      class="btn btn-sm btn-primary"
                                                                      url="<?php echo U('conlist'); ?>"> 搜索</button></span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>标题</th>
                                <th>分类</th>
                                <th>模型</th>
                                <th>作者/来源</th>
                                <th>发布时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['title']; ?></td>
                                <td><?php echo $vo['typename']; ?><?php echo !empty($vo['typename2'])?'>'.$vo['typename2']:$vo['typename2']; ?></td>
                                <td><?php echo $vo['channelname']; ?></td>
                                <td><?php echo $vo['writer']; ?>/<?php echo $vo['source']; ?></td>
                                <td><?php echo time_format($vo['pubdate']); ?></td>
                                <td>
                                    <a href="<?php echo U('conedit?id='.$vo['id']); ?>" class="open-window text-info no-refresh"
                                       title="编辑">编辑</a>
                                    <a class="text-danger ajax-get confirm"
                                       href="<?php echo U('delcon?&id='.$vo['id']); ?>" title="删除">删除</a>
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

<!-- 全局js -->
<script>
    $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
    });
    $("#search").click(function () {
        var url = $(this).attr('url');
        var query = $('.input-groups').find('input').serialize();
        var select = $('.input-groups').find('select').serialize();
        query = query.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
        query = query.replace(/^&/g, '');
        if (url.indexOf('?') > 0) {
            url += '&' + query;
        } else {
            url += '?' + query;
        }
        if (url.indexOf('?') > 0) {
            url += '&' + select;
        } else {
            url += '?' + select;
        }
        window.location.href = url;
    });
    //回车搜索
    $(".input-sm").keyup(function (e) {
        if (e.keyCode === 13) {
            $("#search").click();
            return false;
        }
    });
    $(".serach_selects").change(function () {
        $("#search").click();
        return false;
    });
</script>
</body>
</html>
