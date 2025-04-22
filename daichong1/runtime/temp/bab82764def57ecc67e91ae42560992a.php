<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:76:"/www/wwwroot/115.126.57.143/public/../application/admin/view/member/log.html";i:1655264244;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
<div id="page">
    <div class="wrapper wrapper-content animated fadeIn">
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>操作日志</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="row input-groups">
                            <div class="col-md-2 form-inline text-left">
                                <a class="btn btn-white open-reload"><i class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                            <div class="col-md-10 m-b-xs form-inline text-right">
                                <div class="form-group">
                                    <label class="control-label">请求:</label>
                                    <select class="input-sm form-control input-s-sm inline serach_selects"
                                            name="method"
                                            style="width: auto;">
                                        <option value="">请选择</option>
                                        <?php if(is_array($methods) || $methods instanceof \think\Collection || $methods instanceof \think\Paginator): $i = 0; $__LIST__ = $methods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                        <option value="<?php echo $vo['method']; ?>"
                                        <?php if(I('method')==$vo['method']){ echo "selected='selected'"; } ?>
                                        ><?php echo $vo['method']; ?></option>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">管理员:</label>
                                    <select class="input-sm form-control input-s-sm inline serach_selects"
                                            name="member_id"
                                            style="width: auto;">
                                        <option value="">请选择</option>
                                        <?php if(is_array($member) || $member instanceof \think\Collection || $member instanceof \think\Paginator): $i = 0; $__LIST__ = $member;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                        <option value="<?php echo $vo['id']; ?>"
                                        <?php if(I('member_id')==$vo['id']){ echo "selected='selected'"; } ?>
                                        ><?php echo $vo['nickname']; ?></option>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" name="key" placeholder="请输入关键词搜索"
                                               value="<?php echo I('key'); ?>"
                                               class="input-sm form-control">
                                        <span class="input-group-btn"><button type="button" id="search"
                                                                              class="btn btn-sm btn-primary"
                                                                              url="<?php echo U('log'); ?>"> 搜索</button></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>操作</th>
                                    <th>地址</th>
                                    <th>数据</th>
                                    <th>用户</th>
                                    <th>ip</th>
                                    <th>时间</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <tr>
                                    <td><?php echo $vo['title']; ?></td>
                                    <td>
                                        <div style="max-width: 400px;overflow-y: scroll;overflow-y: hidden;">
                                            [<?php echo $vo['method']; ?>]&nbsp;<?php echo $vo['url']; ?>
                                        </div>
                                    </td>
                                    <td class="kecopy" datastr='<?php echo $vo['param']; ?>'
                                        data-title="<?php echo $vo['title']; ?>-<?php echo $vo['method']; ?>(<?php echo $vo['url']; ?>)-<?php echo $vo['ip']; ?>-<?php echo $vo['name']; ?>">详情
                                    </td>
                                    <td><?php echo $vo['name']; ?></td>
                                    <td><?php echo $vo['ip']; ?></td>
                                    <td><?php echo time_format($vo['create_time']); ?></td>
                                </tr>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php echo $list->render(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(".kecopy").click(function () {
        var str = $(this).attr("datastr");
        var title = $(this).data('title');
        var json = JSON.parse(str);//将json字符串格式化为json对象
        str = JSON.stringify(json, null, "\t");
        layer.open({
            type: 1,
            skin: '', //加上边框
            title: title,
            area: ['520px', '440px'], //宽高
            content: '<textarea style="width: 100%;height: 100%;">' + str + '</textarea>'
        });
    });
    //    function reconvert(str) {
    //        str = str.replace(/(\\u)(\w{1,4})/gi, function ($0) {
    //            return (String.fromCharCode(parseInt((escape($0).replace(/(%5Cu)(\w{1,4})/g, "$2")), 16)));
    //        });
    //        str = str.replace(/(&#x)(\w{1,4});/gi, function ($0) {
    //            return String.fromCharCode(parseInt(escape($0).replace(/(%26%23x)(\w{1,4})(%3B)/g, "$2"), 16));
    //        });
    //        str = str.replace(/(&#)(\d{1,6});/gi, function ($0) {
    //            return String.fromCharCode(parseInt(escape($0).replace(/(%26%23)(\d{1,6})(%3B)/g, "$2")));
    //        });
    //        return str;
    //    }
</script>
</body>
</html>
