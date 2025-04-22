<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:77:"/www/wwwroot/115.126.57.143/public/../application/admin/view/reapi/fenxi.html";i:1655264244;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>供应商订单数据分析</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups" style="margin-bottom: 10px">
                        <div class="col-md-2 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                        </div>
                        <div class="col-md-10 m-b-xs form-inline text-right">
                            <div class="form-group">
                                <label class="control-label">接口:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects"
                                        name="reapi_id"
                                        style="width: auto;">
                                    <option value="">请选择</option>
                                    <?php if(is_array($apis) || $apis instanceof \think\Collection || $apis instanceof \think\Paginator): $i = 0; $__LIST__ = $apis;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $vo['id']; ?>"
                                    <?php if(I('reapi_id')==$vo['id']){ echo "selected='selected'"; } ?>
                                    ><?php echo $vo['name']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <span class="control-label">时间：</span>
                                <input type="text" name="begin_time" id="begin_time" value="<?php echo I('begin_time'); ?>"
                                       class="input-sm input-s-sm serach_selects" style="border: 1px solid #e5e6e7;"
                                       placeholder="开始日期" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <span class="control-label">-</span>
                                <input type="text" name="end_time" id="end_time" value="<?php echo I('end_time'); ?>"
                                       style="border: 1px solid #e5e6e7;"
                                       class="input-sm input-s-sm serach_selects" placeholder="结束日期"
                                       autocomplete="off">
                            </div>

                            <div class="form-group">
                                <div class="input-group">
                                    <input type="hidden" value="<?php echo I('reapi_id'); ?>" name="reapi_id"/>
                                    <span class="input-group-btn"><button type="button" id="search"
                                                                          class="btn btn-sm btn-primary"
                                                                          url="<?php echo U('fenxi'); ?>"> 搜索</button></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>接口</th>
                                <th>统计时段</th>
                                <th>提交api次数（一单可能多次）</th>
                                <th>充值中订单</th>
                                <th>成功订单(含部分充值)</th>
                                <th>失败订单</th>
                                <th>成功金额</th>
                                <th>成本</th>
                                <th>利润</th>
                                <th>成功率</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['name']; ?></td>
                                <td><?php echo $vo['date_start']; ?>-<?php echo $vo['date_end']; ?></td>
                                <td><?php echo $vo['all_count']; ?></td>
                                <td><?php echo $vo['ing_count']; ?></td>
                                <td><?php echo $vo['sus_count']; ?></td>
                                <td><?php echo $vo['all_count']-$vo['ing_count']-$vo['sus_count']; ?></td>
                                <td><?php echo $vo['sus_price']+$vo['pasus_price']; ?></td>
                                <td><?php echo $vo['sus_cost']+$vo['pasus_cost']; ?></td>
                                <td><?php echo $vo['sus_price']+$vo['pasus_price']-$vo['sus_cost']-$vo['pasus_cost']; ?></td>
                                <td><?php echo $vo['sus_ratio']; ?>%</td>
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
<script src="/public/admin/js/laydate/laydate.js"></script>
<script>
    //执行一个laydate实例
    laydate.render({
        elem: '#begin_time',
        type: 'date',
        done: function (value, date, endDate) {
            $('#begin_time').val(value);
        }
    });
    laydate.render({
        elem: '#end_time',
        type: 'date',
        done: function (value, date, endDate) {
            $('#end_time').val(value);
        }
    });
</script>
</body>
</html>
