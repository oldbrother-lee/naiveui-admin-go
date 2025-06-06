<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:72:"/var/www/html/public/../application/admin/view/customer/balance_log.html";i:1655264242;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>用户账单</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-2 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-white open-reload btn-sm"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                                <input type="hidden" name="id" value="<?php echo I('id'); ?>"/>
                            </div>
                        </div>
                        <div class="col-md-10 m-b-xs form-inline text-right">
                            <div class="form-group">
                                <span class="control-label">开始：</span>
                                <input type="text" name="begin_time" id="begin_time" value="<?php echo I('begin_time'); ?>"
                                       class="input-sm input-s-sm serach_selects" style="border: 1px solid #e5e6e7;"
                                       placeholder="账单开始日期" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <span class="control-label">&nbsp;结束：</span>
                                <input type="text" name="end_time" id="end_time" value="<?php echo I('end_time'); ?>"
                                       style="border: 1px solid #e5e6e7;"
                                       class="input-sm input-s-sm serach_selects" placeholder="账单结束日期"
                                       autocomplete="off">
                            </div>

                            <div class="form-group">
                                <?php $style= C('BALANCE_STYLE'); ?>
                                <label class="control-label">类型:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="style"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($style) || $style instanceof \think\Collection || $style instanceof \think\Paginator): $i = 0; $__LIST__ = $style;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if(I('style')==$key){ echo "selected='selected'"; } ?>><?php echo $vo; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <div class="input-group">
                                    <input type="hidden" name="id" value="<?php echo I('id'); ?>">
                                    <input type="text" name="key" placeholder="请输入交易明细查询" value="<?php echo I('key'); ?>"
                                           class="input-sm form-control">
                                    <span class="input-group-btn"><button type="button" id="search"
                                                                          class="btn btn-sm btn-primary"
                                                                          url="<?php echo U('balance_log'); ?>"> 搜索</button></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="button" id="excel"
                                        class="btn btn-sm btn-primary"
                                        url="<?php echo U('balance_out_excel'); ?>" title="导出">
                                    <i class="fa fa-file-text-o"></i>
                                    导出
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>用户</th>
                                <th>交易方式</th>
                                <th>交易金额</th>
                                <th>交易明细</th>
                                <th>交易类型</th>
                                <th>交易时间</th>
                                <th>操作前/操作后余额</th>
                                <th>校验</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td>[<?php echo $vo['customer_id']; ?>]&nbsp;<?php echo $vo['username']; ?></td>
                                <td><?php echo $vo['type']==1?'收入':'支出'; ?></td>
                                <td><?php echo $vo['type']==1?'+':'-'; ?><?php echo $vo['money']; ?></td>
                                <td><?php echo $vo['remark']; ?></td>
                                <td><?php echo C('BALANCE_STYLE')[$vo['style']]; ?></td>
                                <td><?php echo time_format($vo['create_time']); ?></td>
                                <td><?php echo $vo['balance_pr']; ?>/<?php echo $vo['balance']; ?></td>
                                <td>
                                    <?php if($vo['check'] == '1'): ?>
                                    <span class="text-info">通过</span>
                                    <?php endif; if($vo['check'] == '0'): ?>
                                    <span class="text-danger">不通过</span>
                                    <?php endif; ?>
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
<script src="/public/admin/js/laydate/laydate.js"></script>
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

</body>
</html>
