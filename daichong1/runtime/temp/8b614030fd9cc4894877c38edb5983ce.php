<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:72:"/www/wwwroot/daichong1/public/../application/admin/view/reapi/param.html";i:1680876022;s:64:"/www/wwwroot/daichong1/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>接口套餐管理</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-2 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i class="glyphicon glyphicon-repeat"></i></a>
                                <a class="btn btn-sm btn-primary open-window" title="新增"
                                   href="<?php echo U('param_edit',['reapi_id'=>I('id')]); ?>"><i
                                        class="fa fa-plus"></i> 新增</a>

                            </div>
                        </div>
                        <div class="col-md-10 m-b-xs form-inline text-right">
                        </div>
                        <div class="col-sm-12 m-b-xs">
                            <p style="color: #f00">（重要）添加时，名称和成本根据自己习惯填写，其他参数按照“参数定义”说明填写，“参数定义”中有说明了的参数才填，其他不填。
                                参数如果是产品ID、商品ID、编码等，一般在供应商开的后台都能找到！
                                如果参数是面值，直接填写充值的金额！
                                如果参数是其他数字或者字母，根据提示填写即可。
                                参数复杂的接口技术都会添加一个示例，可以结合示例和参数说明填写。</p>
                            <p style="color: #00f">（重要）参数定义：<?php echo $api['api_remark']; ?></p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>名称</th>
                                <th>成本</th>
                                <th>参数1</th>
                                <th>参数2</th>
                                <th>参数3</th>
                                <th>参数4</th>
                                <th>地区限制</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['desc']; ?></td>
                                <td><?php echo $vo['cost']; ?></td>
                                <td><?php echo $vo['param1']; ?></td>
                                <td><?php echo $vo['param2']; ?></td>
                                <td><?php echo $vo['param3']; ?></td>
                                <td><?php echo $vo['param4']; ?></td>
                                <td>允许：<?php echo $vo['allow_pro']; ?>-<?php echo $vo['allow_city']; ?><br/>
                                    禁止：<?php echo $vo['forbid_pro']; ?>-<?php echo $vo['forbid_city']; ?>
                                </td>
                                <td>
                                    <a class="open-window"
                                       href="<?php echo U('param_edit',['id'=>$vo['id'],'reapi_id'=>I('id')]); ?>"
                                       title="编辑">编辑套餐</a>
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
