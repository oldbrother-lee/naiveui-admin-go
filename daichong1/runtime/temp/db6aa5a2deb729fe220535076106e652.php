<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:76:"/www/wwwroot/115.126.57.143/public/../application/admin/view/qudan/plan.html";i:1728208662;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>代付中控</h5>
                </div>
                <?php if($zdhq == 1): ?>
                <div class="form-group">
                    <a href="javascript:void(0)" class="btn btn-sm btn-primary" onclick="kq(2)"   title="关闭自动取单"
                       style="padding: 4px 12px;" type="button">关闭自动取单</a>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <a href="javascript:void(0)" class="btn btn-sm btn-primary" onclick="kq(1)"   title="开启自动取单"
                       style="padding: 4px 12px;" type="button">开启自动取单</a>
                </div>
                <?php endif; ?>
                 <div class="form-group">
                    <a class="btn btn-sm btn-primary" onclick="pf()">派发测试</a>
                </div>
                
                <div class="ibox-content">

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>上游单号</th>
                                <th>本地单号</th>
                                <th>充值账户</th>
                                <th>面值</th>
                                <th>结算价格</th>
                                <th>创建时间</th>
                                <th>接单时间</th>
                                <th>上报完成时间</th>
                                <th>上游订单状态</th>
                                <th>系统订单状态</th>
                                <th>结算状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$member): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td>
                                    <?php echo $member['id']; ?>
                                </td>
                                <td>
                                    <?php echo $member['order_id']; ?>
                                </td>
                                <td><?php echo $member['order_sn']; ?></td>
                                <td><?php echo $member['account']; ?></td>
                                <td><?php echo $member['denom']; ?>
                                </td>
                                <td><?php echo $member['settlePrice']; ?></td>
                                <td><?php echo $member['createTime']; ?></td>
                                <td><?php echo $member['chargeTime']; ?></td>
                                <td><?php echo $member['uploadTime']; ?></td>
                                 <td><?php if(($member['status'] == 1)): ?> 待支付

                                    <?php elseif($member['status'] == 2): ?>已支付
                                    <?php elseif($member['status'] == 3): ?>已上报

                                    <?php else: ?>
                                    未知

                                    <?php endif; ?>/<?php echo $member['status']; ?></td>
                                <td style="">



                                </td>
                                <td>



                                </td>
                                <td>
                                    <a class="ajax-get confirm text-danger" title="删除"
                                       href="<?php echo U('deletes?id='.$member['id']); ?>">删除</a>

                                </td>
                            </tr>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                            </tbody>
                        </table>

                    </div>
                    <?php echo $page; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<input id="sss" type="hidden" value="1">

<script>
    $(document).ready(function () {

        // setmoneysss(1);
        
    });
    function kq(val) {
        var url = 'zdhq';
        if(val == 1){
            var url = url+'?way=1';
            $.get(url,function(data,status){
                console.log(data);
                // window.timer = setInterval("startRequest()",10000);
                // location.reload();
                
            });
        }else{
            var url = url + '?way=2'
            $.get(url,function(data,status){
                console.log(data);
                // location.reload();
                // clearTimeout(window.timer);

            });        }
    }
    function startRequest() {
        var url="zdhq?way=1";
        $.get(url,function(data,status){
            console.log(data);
            location.reload();

        });
    }
    function pf() {
        var url = "paifa";
        $.get(url,function (data,status) {
            console.log(data);
             var msg = data.errmsg;
            layer.msg(msg);
        })
    }
</script>
</body>
</html>
