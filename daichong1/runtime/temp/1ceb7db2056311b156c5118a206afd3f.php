<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:79:"/www/wwwroot/daichong1/public/../application/admin/view/daichongs/worklist.html";i:1728620919;s:64:"/www/wwwroot/daichong1/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                                <!--   href="<?php echo U('alladdwork',['userid'=>$userid]); ?>"><i-->
                                <!--        class="fa fa-plus"></i> 一键修改任务</a>-->
                                        <a class="btn btn-sm btn-primary open-window" title="新增"
                                   href="<?php echo U('addwork',['userid'=>$userid]); ?>"><i
                                        class="fa fa-plus"></i> 新增</a>

                            </div>

                        </div>
                        <div class="col-md-10 m-b-xs form-inline text-right">
                        </div>

                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>id</th>
                                <th>userid</th>
                                <th>运营商</th>
                                <th>地区</th>
                                <th>金额</th>
                                <th>每次接单数量</th>
                                <th>超时时间</th>
                                <th>任务状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['id']; ?></td>
                                <td><?php echo $vo['user_id']; ?></td>
                                <td><?php echo $vo['yunying']; ?></td>
                                <td><?php echo $vo['prov_select']; ?></td>
                                <td><?php echo $vo['money']; ?></td>
                                <td><?php echo $vo['count']; ?></td>
                                <td><?php echo $vo['te']; ?></td>
                                <td><?php echo $vo['status']; ?></td>
                                <td>
                                    <a class="open-window"
                                       href="<?php echo U('addwork',['id'=>$vo['id']]); ?>"
                                       title="编辑" >编辑</a>
                                        <a onclick="del(<?php echo $vo['id']; ?>)"
                                       title="删除" >删除</a>
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
    function del(id){
       
         var url = "<?php echo url('daichongs/delwork'); ?>";
        $.get(url+'?id='+id,function(data,status){
                layer.msg('删除成功');
                location.reload();
        })
            
    }
</script>
</body>
</html>
