<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:66:"/var/www/html/public/../application/admin/view/customer/grade.html";i:1655264242;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>用户等级</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups" style="margin-bottom: 10px;">
                        <div class="col-md-2 form-inline text-left">
                            <div class="form-group">
                                <a class="btn btn-sm btn-primary open-window" href="<?php echo U('grade_edit'); ?>" title="新增等级"><i
                                        class="fa fa-plus"></i> 新增</a>
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                        </div>
                        <div class="col-md-10 m-b-xs form-inline text-right">
                            <div class="form-group">
                                <?php $types=C('CUS_TYPE');?>
                                <label class="control-label">类型:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="grade_type"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($types) || $types instanceof \think\Collection || $types instanceof \think\Paginator): $i = 0; $__LIST__ = $types;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if(I('grade_type')==$key){ echo "selected='selected'"; } ?>
                                    ><?php echo $vo; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-btn"><button type="button" id="search"
                                                                          class="btn btn-sm btn-primary"
                                                                          url="<?php echo U('grade'); ?>"> 搜索</button></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <p style="color:#f00;">
                            1、当高等级开启自定价时，他们的下级(下级必须是非自定价的等级)将使用他们的自定义价格。该情况下返利是用户自己设置的浮动（利润）价格！<br/>
                            2、没有上级或者上级不是自定价等级的用户使用系统设置的价格。<br/>
                            3、如果下级是可自定价的等级，那么他不在受上级自定价的约束，直接使用系统给等级设置的价格。该情况下返利是两个等级差价，如果差价<=0将不返利！<br/>
                            4、零售普通（1号）和代理商基本（3号）等级不能打开自定价开关，默认的零售和代理商使用的此等级。<br/>
                            5、应该遵循等级越高，ID越大的标准设置。<br/>
                            6、支持两级返利，A<-B<-C,级别之间有差价就可以返利（差价来源代理自定价或自身等级差价）
                        </p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <td>ID</td>
                                <td>类型</td>
                                <th>等级名称</th>
                                <th>升级费用</th>
                                <th>返利金额</th>
                                <th>升级奖励</th>
                                <th>付费升级</th>
                                <th>分销</th>
                                <th>自定价</th>
                                <th>备注</th>
                                <th>排序</th>
                                <th>编辑</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><?php echo $vo['id']; ?></td>
                                <td><?php echo C('CUS_TYPE')[$vo['grade_type']]; ?></td>
                                <td><?php echo $vo['grade_name']; ?></td>
                                <td>
                                    <?php if($vo['is_payup'] == '1'): ?>
                                    <?php echo $vo['up_price']; else: ?>
                                    -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($vo['is_payup'] == '1'): ?>
                                    <?php echo $vo['rebate_price']; else: ?>
                                    -
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if($vo['is_payup'] == '1'): ?>
                                    <?php echo $vo['up_rewards']; else: ?>
                                    -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($vo['is_payup'] == '1'): ?>
                                    是
                                    <?php else: ?>
                                    否
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!in_array(($vo['id']), explode(',',"1,3"))): ?>
                                    <?php echo $vo['is_agent']==1?'是':'否'; else: ?>
                                    -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!in_array(($vo['id']), explode(',',"1,3"))): ?>
                                    <?php echo $vo['is_zdy_price']==1?'是':'否'; else: ?>
                                    -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $vo['remark']; ?></td>
                                <td><?php echo $vo['sort']; ?></td>
                                <td>
                                    <a class="open-window" href="<?php echo U('grade_edit?id='.$vo['id']); ?>" title="编辑">编辑</a>
                                    <a class="open-window no-refresh" href="<?php echo U('price?grade_id='.$vo['id']); ?>"
                                       title="<?php echo $vo['grade_name']; ?>价格设置">产品价格</a>
                                    <?php if(!in_array(($vo['id']), explode(',',"1,3"))): ?>
                                    <a class="text-danger ajax-get confirm" href="<?php echo U('grade_deletes?id='.$vo['id']); ?>"
                                       title="删除">删除</a>
                                    <?php endif; ?>
                                    <a class="text-success ajax-get confirm" href="<?php echo U('grade_copy?id='.$vo['id']); ?>"
                                       title="删除">复制一个</a>
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
</body>
</html>
