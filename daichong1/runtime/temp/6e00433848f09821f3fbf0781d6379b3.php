<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:70:"/var/www/html/public/../application/admin/view/daichongs/userlist.html";i:1728734110;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>用户管理</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-3 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i class="glyphicon glyphicon-repeat"></i></a>
                                <!--<a class="btn btn-sm btn-primary open-window" title="一键修改任务"-->
                                <!--   href="<?php echo U('alladdwork'); ?>"><i-->
                                <!--        class="fa fa-plus"></i> 一键修改任务</a>-->
                                        <a class="btn btn-sm btn-primary open-window" title="新增"
                                   href="<?php echo U('add'); ?>"><i
                                        class="fa fa-plus"></i> 新增</a>

                            </div>
                            异常账号：
                            <?php if(is_array($_listn)){ if(is_array($_listn) || $_listn instanceof \think\Collection || $_listn instanceof \think\Paginator): $i = 0; $__LIST__ = $_listn;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <?php echo $vo; ?><br/>
                            <?php endforeach; endif; else: echo "" ;endif; } else { ?>
                                无
                            <?php } ?>
                        </div>
                        <div class="col-md-10 m-b-xs form-inline text-right">
                        </div>

                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>账号id</th>
                                <th>appkey</th>
                                <th>ApSecret</th>
                                <th>渠道id</th>
                                <th>账号类型</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['child']; ?></td>
                                <td><?php echo $vo['user']; ?></td>
                                <td><?php echo $vo['pwd']; ?></td>
                                <td><?php echo $vo['zhuanqu']; ?></td>
                                <td><?php echo $vo['type']; ?></td>
                                <td>
                                    <a class="open-window"
                                       href="<?php echo U('add',['id'=>$vo['id']]); ?>"
                                       title="编辑">编辑</a>
                                    <a class="open-window"
                                       href="<?php echo U('worklist',['userid'=>$vo['id']]); ?>"
                                       title="<?php echo $vo['work']; ?>" ><?php echo $vo['work']; ?></a>
                                    <a class="ajax-get confirm text-danger" title="删除"
                                       href="<?php echo U('deletes_param?id='.$vo['id']); ?>">删除</a>
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
    $("#search,#excel").click(function () {
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
