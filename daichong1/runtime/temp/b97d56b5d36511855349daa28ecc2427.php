<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:79:"/www/wwwroot/115.126.57.143/public/../application/agent/view/porder/excels.html";i:1655264246;s:69:"/www/wwwroot/115.126.57.143/application/agent/view/public/header.html";i:1655264248;}*/ ?>
<!-- lq      -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo C('WEB_SITE_TITLE'); ?></title>
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="/public/agent/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/public/agent/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link href="/public/agent/css/animate.css" rel="stylesheet">
    <link href="/public/agent/css/style.css?v8" rel="stylesheet">
    <link href="/public/agent/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="/public/agent/css/layx.min.css" rel="stylesheet"/>
    <link href="/public/agent/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/public/agent/css/animate.css" rel="stylesheet">
    <script src="/public/agent/js/jquery.min.js?v=2.1.4"></script>
    <script src="/public/agent/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/public/agent/js/plugins/layer/layer.min.js"></script>
    <script src="/public/agent/js/content.js"></script>
    <script src="/public/agent/js/plugins/toastr/toastr.min.js"></script>
    <script src="/public/agent/js/dayuanren.js?v121"></script>
    <script src="/public/agent/js/layx.js" type="text/javascript"></script>
    <script src="/public/agent/js/plugins/iCheck/icheck.min.js"></script>
    <script src="/public/agent/js/clipboard.min.js"></script>
    <script src="/public/agent/js/vue.min.js?v=3.3.6"></script>
    <script src="/public/agent/js/util.js?V=1"></script>
    <script src="/public/agent/js/laydate/laydate.js" type="text/javascript"></script>
    <script src="/public/agent/js/ajaxfileupload.js?v1" type="text/javascript"></script>
    <script>
        console.log("<?php echo C('console_msg'); ?>");
        console.log("当前版本：<?php echo C('dtupdate.version'); ?>");
    </script>
</head>

<style>
    .table-bordered > tbody > tr:hover {
        background-color: #f5f5f5
    }

    .tjnum {
        color: #f00;
        margin-right: 20px;
    }
</style>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>提单记录（表格、批量）</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-4 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                            <div class="form-group">
                                <a href="<?php echo U('in_excel'); ?>" class="btn btn-sm btn-info open-window"
                                   title="excel表格方式提交订单">表格提单</a>
                            </div>
                            <div class="form-group">
                                <a href="<?php echo U('order_batch'); ?>" class="btn btn-sm btn-success open-window"
                                   title="批量录入账号提交订单">批量提单</a>
                            </div>
                        </div>
                        <div class="col-md-8 m-b-xs form-inline text-right">
                            <div class="form-group">
                                <span class="control-label">开始：</span>
                                <input type="text" name="begin_time" id="begin_time" value="<?php echo I('begin_time'); ?>"
                                       class="input-sm input-s-sm serach_selects" style="border: 1px solid #e5e6e7;"
                                       placeholder="开始日期" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <span class="control-label">&nbsp;结束：</span>
                                <input type="text" name="end_time" id="end_time" value="<?php echo I('end_time'); ?>"
                                       style="border: 1px solid #e5e6e7;"
                                       class="input-sm input-s-sm serach_selects" placeholder="结束日期"
                                       autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label class="control-label">类型:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="type"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <option value="1"
                                    <?php if(1==I('type')){ echo "selected='selected'"; } ?>>批量提单</option>
                                    <option value="2"
                                    <?php if(2==I('type')){ echo "selected='selected'"; } ?>>表格提单</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" name="key" placeholder="请输入文件名" value="<?php echo I('key'); ?>"
                                           class="input-sm form-control">
                                    <span class="input-group-btn"><button type="button" id="search"
                                                                          class="btn btn-sm btn-primary"
                                                                          url="<?php echo U('excels'); ?>"> 搜索</button></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row input-groups">
                        <div class="col-md-12 form-inline text-left" style="color: red;">
                            1、想要导出不同状态的订单，请点击不同状态的数量，打开列表导出
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>文件</th>
                                <th>备注</th>
                                <th>类型</th>
                                <th>上传时间</th>
                                <th>总数(行)</th>
                                <th>未提交(行)</th>
                                <th>下单中(行)</th>
                                <th>下单成功/失败(行)</th>
                                <th>已下单(单)</th>
                                <th>充值中(单)</th>
                                <th>成功(单)</th>
                                <th>失败(单)</th>
                                <th>订单金额</th>
                                <th>实际花费</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['name']; ?></td>
                                <td><?php echo $vo['remark']; ?> <a class="text-success upremark" data-id="<?php echo $vo['id']; ?>" data-value="<?php echo $vo['remark']; ?>">编辑</a></td>
                                <td><?php echo $vo['type']==1?'批量提单':'表格提单'; ?></td>
                                <td><?php echo time_format($vo['create_time']); ?></td>
                                <td><a class="text-primary open-window no-refresh"
                                       href="<?php echo U('porder_excel',['excel_id'=>$vo['id']]); ?>"><?php echo $vo['all_count']; ?></a></td>
                                <td><a class="text-danger open-window no-refresh"
                                       href="<?php echo U('porder_excel',['excel_id'=>$vo['id'],'status'=>'1']); ?>"><?php echo $vo['weidao_count']; ?></a>
                                </td>
                                <td><a class="text-warning open-window no-refresh"
                                       href="<?php echo U('porder_excel',['excel_id'=>$vo['id'],'status2'=>'2,3']); ?>"><?php echo $vo['daoruing_count']; ?></a>
                                </td>
                                <td><a class="text-info open-window no-refresh"
                                       href="<?php echo U('porder_excel',['excel_id'=>$vo['id'],'status2'=>'4']); ?>"><?php echo $vo['daorus_sus_count']; ?></a>/
                                    <a class="text-warning open-window no-refresh"
                                       href="<?php echo U('porder_excel',['excel_id'=>$vo['id'],'status2'=>'5']); ?>"><?php echo $vo['daorus_fail_count']; ?></a>
                                </td>
                                <td><a class="text-primary open-window no-refresh"
                                       href="<?php echo U('index',['excel_id'=>$vo['id'],'status2'=>'2,3,4,5,6,7,8,9,10,11,12,13']); ?>"><?php echo $vo['daoru_count']; ?></a>
                                </td>
                                <td>
                                    <a class="text-warning open-window no-refresh"
                                       href="<?php echo U('index',['excel_id'=>$vo['id'],'status2'=>'2,3,8,9,10,11']); ?>"><?php echo $vo['ing_count']; ?></a>
                                </td>
                                <td>
                                    <a class="text-success open-window no-refresh"
                                       href="<?php echo U('index',['excel_id'=>$vo['id'],'status2'=>'4,12,13']); ?>"><?php echo $vo['sus_count']; ?></a>
                                </td>
                                <td>
                                    <a class="text-danger open-window no-refresh"
                                       href="<?php echo U('index',['excel_id'=>$vo['id'],'status2'=>'5,6,7']); ?>"><?php echo $vo['fail_count']; ?></a>
                                </td>
                                <td>￥<?php echo $vo['total_price']; ?></td>
                                <td>￥<?php echo $vo['total_price']-$vo['refund_price']; ?></td>
                                <td>
                                    <a class="text-success" href="<?php echo U('excels_out',['id'=>$vo['id']]); ?>">导出订单</a>
                                </td>
                            </tr>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="page">
                        <?php echo $_list->render(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal inmodal" id="upremarkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form method="post" action="<?php echo U('up_agent_excel'); ?>" class="upremarkModal">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-body">
                    <input type="hidden" name="id" id="excelid">
                    <p><strong>备注</strong></p>
                    <div class="form-group">
                        <input type="text" placeholder="请输入导入表格的备注信息" value=""
                               class="form-control" name="remark" id="excel_remark">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" onclick="closereupremarkModal()">关闭
                    </button>
                    <button type="submit" class="btn btn-primary ajax-post" target-form="upremarkModal">保存</button>
                </div>
            </div>
        </div>
    </form>
</div>
<script src="/public/agent/js/laydate/laydate.js"></script>
<script>
    //执行一个laydate实例
    laydate.render({
        elem: '#begin_time',
        type: 'datetime',
        done: function (value, date, endDate) {
            $('#begin_time').val(value);
        }
    });
    laydate.render({
        elem: '#end_time',
        type: 'datetime',
        done: function (value, date, endDate) {
            $('#end_time').val(value);
        }
    });

</script>
<script>
    $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
    });
    $(".upremark").click(function () {
        $("#excelid").val($(this).data("id"));
        $("#excel_remark").val($(this).data("value"));
        $("#upremarkModal").show();
    });

    function closereupremarkModal() {
        $("#upremarkModal").hide();
    }

</script>
</body>
</html>
