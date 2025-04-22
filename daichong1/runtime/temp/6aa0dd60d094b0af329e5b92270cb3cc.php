<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:66:"/var/www/html/public/../application/admin/view/customer/index.html";i:1655264242;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>会员列表</h5>
                    <h5>&nbsp;<?php echo $_count; ?>条</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-12 m-b-xs form-inline text-left">
                            <div class="form-group">
                                <label class="control-label">排序:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="sort"
                                        style="width: auto;">
                                    <option value="c.id desc"
                                    <?php if(I('sort')=='c.id desc'){ echo "selected='selected'"; } ?>
                                    >注册时间</option>
                                    <option value="c.balance desc"
                                    <?php if(I('sort')=='c.balance desc'){ echo "selected='selected'"; } ?>
                                    >余额降序</option>
                                    <option value="c.shouxin_e desc"
                                    <?php if(I('sort')=='c.shouxin_e desc'){ echo "selected='selected'"; } ?>
                                    >授信降序</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">类型:</label>
                                <?php $types=C('CUS_TYPE');?>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="type">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($types) || $types instanceof \think\Collection || $types instanceof \think\Paginator): $i = 0; $__LIST__ = $types;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if(I('type')==$key){ echo "selected='selected'"; } ?>
                                    ><?php echo $to; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">等级:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="grade_id"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($grades) || $grades instanceof \think\Collection || $grades instanceof \think\Paginator): $i = 0; $__LIST__ = $grades;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $vo['id']; ?>"
                                    <?php if(I('grade_id')==$vo['id']){ echo "selected='selected'"; } ?>
                                    ><?php echo $vo['grade_name']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <?php $client=C('CLIENT_TYPE'); ?>
                                <label class="control-label">渠道:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects"
                                        name="client"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($client) || $client instanceof \think\Collection || $client instanceof \think\Paginator): $i = 0; $__LIST__ = $client;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if(I('client')==$key){ echo "selected='selected'"; } ?>><?php echo $vo; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">关注:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects"
                                        name="is_subscribe"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <option value="2"
                                    <?php if(I('is_subscribe')==2){ echo "selected='selected'"; } ?>>是</option>
                                    <option value="1"
                                    <?php if(I('is_subscribe')==1){ echo "selected='selected'"; } ?>>否</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">状态:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="status"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <option value="2"
                                    <?php if(I('status')==2){ echo "selected='selected'"; } ?>>启用</option>
                                    <option value="1"
                                    <?php if(I('status')==1){ echo "selected='selected'"; } ?>>禁用</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">查询条件:</label>
                                <select class="input-sm form-control input-s-sm inline" name="query_name"
                                        style="width: auto;">
                                    <option value="">模糊</option>
                                    <option value="c.id"
                                    <?php if(I('query_name')=='c.id'){ echo "selected='selected'"; } ?>>ID</option>
                                    <option value="c.username"
                                    <?php if(I('query_name')=='c.username'){ echo "selected='selected'"; } ?>
                                    >名称</option>
                                    <option value="c.wx_openid"
                                    <?php if(I('query_name')=='c.wx_openid'){ echo "selected='selected'"; } ?>
                                    >openid</option>
                                    <option value="c.mobile"
                                    <?php if(I('query_name')=='c.mobile'){ echo "selected='selected'"; } ?>>手机号</option>
                                    <option value="c.weixin_appid"
                                    <?php if(I('query_name')=='c.weixin_appid'){ echo "selected='selected'"; } ?>
                                    >appid</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="hidden" name="id" value="<?php echo I('id'); ?>"/>
                                    <input type="text" name="key" placeholder="请输入昵称或者手机号" value="<?php echo I('key'); ?>"
                                           class="input-sm form-control">
                                    <span class="input-group-btn"><button type="button" id="search"
                                                                          class="btn btn-sm btn-primary"
                                                                          url="<?php echo U('index'); ?>"> 搜索</button></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 form-inline text-left">
                            <div class="form-group">
                                <a class="btn-sm btn-white open-reload"><i class="glyphicon glyphicon-repeat"></i></a>
                                <a type="button" class="btn btn-sm btn-primary open-window" href="<?php echo U('edit'); ?>">
                                    <i class="fa fa-plus"></i> 新增</a>
                                <a href="<?php echo U('poster/index'); ?>" class="btn btn-sm btn-info open-window no-refresh"
                                   title="海报配置"><i class="fa fa-cog"></i> 海报设置</a>
                                <a href="<?php echo U('Applybla/index'); ?>" class="btn btn-sm btn-info open-window no-refresh"
                                   title="打款申请"><i class="fa fa-cny"></i> 打款申请</a>
                                <a type="button" id="excel" class="btn btn-sm btn-primary" url="<?php echo U('customer_excel'); ?>">
                                    <i class="fa fa-table"></i> 导出</a>
                                <a class="btn btn-sm btn-danger ajax-get confirm" href="<?php echo U('del_poster'); ?>"
                                   title="清空已生成海报"><i class="fa fa-recycle"></i> 清空推广码</a>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th></th>
                                <th>ID</th>
                                <th>会员</th>
                                <th>等级</th>
                                <th>上级</th>
                                <th>下级</th>
                                <th>渠道</th>
                                <th>余额</th>
                                <th>授信额度</th>
                                <th>消费</th>
                                <th>公众号</th>
                                <th>注册时间</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td><img src="<?php echo $vo['headimg']; ?>" alt="" style="width: 30px;height: 30px;border-radius: 50%;"></td>
                                <td><?php echo $vo['id']; ?></td>
                                <td>
                                    昵称：<?php echo $vo['username']; ?><br/>
                                    手机：<?php echo $vo['mobile']; ?>
                                </td>
                                <td>[<?php echo C('CUS_TYPE')[$vo['type']]; ?>]<?php echo $vo['grade_name']; ?><br/>
                                    <?php if($vo['type'] == '2'): ?>
                                    h5分销：<?php echo $vo['is_h5fx']==1?"是":'否'; endif; ?>
                                </td>
                                <td>
                                    <?php if($vo['f_id'] > '0'): ?>
                                    <a class="open-window no-refresh" title="推荐人"
                                       href="<?php echo U('index',['id'=>$vo['f_id']]); ?>">
                                        [<?php echo get_user_grade_name($vo['f_id']); ?>]
                                        [<?php echo $vo['f_id']; ?>] <?php echo $vo['usernames']; ?>
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <td><a class="open-window no-refresh" title="会员"
                                       href="<?php echo U('index',['f_id'=>$vo['id']]); ?>"><?php echo $vo['child_num']; ?></a>
                                </td>
                                <td><?php echo C('CLIENT_TYPE')[$vo['client']]; ?></td>
                                <td>
                                    余额：<a class="open-window no-refresh" title="用户账单" href="<?php echo U('balance_log',['id'=>$vo['id']]); ?>"><?php echo $vo['balance']; ?></a><br/>
                                    <a class="text-info recharge" title="充值" data-id="<?php echo $vo['id']; ?>">充值</a><br/>
                                    <a class="text-danger ajax-get no-refresh" href="<?php echo U('balance_check?id='.$vo['id']); ?>"
                                       title="余额校验">校验</a>
                                </td>
                                <td><?php echo $vo['shouxin_e']; ?></td>
                                <td>订单：<?php echo $vo['porder_num']; ?><br/>
                                    消费：<a class="open-window" title="订单"
                                          href="<?php echo U('porder/index',['customer_id'=>$vo['id']]); ?>"><?php echo $vo['total_price']; ?></a></td>
                                <td>
                                    appid：<?php echo $vo['weixin_appid']; ?><br/>
                                    openid：<?php echo $vo['wx_openid']; ?><br/>
                                    关注：<?php echo !empty($vo['is_subscribe'])?'是':'否'; ?>
                                </td>
                                <td><?php echo time_format($vo['create_time']); ?></td>
                                <td><?php if($vo['status'] == '0'): ?><span
                                        class="text-danger">禁用</span><?php else: ?><span
                                        class="text-info">启用</span><?php endif; ?>
                                </td>
                                <td>
                                    <?php if($vo['status'] == '1'): ?>
                                    <a class="text-warning ajax-get"
                                       href="<?php echo U('qi_jin?id='.$vo['id'].'&status=0'); ?>" title="禁用">禁用</a>
                                    <?php else: ?>
                                    <a class="text-info ajax-get" href="<?php echo U('qi_jin?id='.$vo['id'].'&status=1'); ?>"
                                       title="启用">启用</a>
                                    <?php endif; if($vo['type'] == '2'): ?>
                                    <a class="text-warn resetpwd" title="重置密码" data-id="<?php echo $vo['id']; ?>">重置密码</a>
                                    <a class="open-window" href="<?php echo U('edita?id='.$vo['id']); ?>" title="资料">资料</a>
                                    <a class="text-warn resetgoogle" title="重置密码" data-id="<?php echo $vo['id']; ?>">清除谷歌验证码</a>
                                    <a class="open-window" href="<?php echo U('month_bill?id='.$vo['id']); ?>" title="月账单">月账单</a>
                                    <?php endif; if($vo['is_zdy_price'] == '1'): ?>
                                    <a class="open-window" href="<?php echo U('hz_price?customer_id='.$vo['id']); ?>" title="编辑">自定义销售价格</a>
                                    <?php endif; ?>
                                    <a class="open-window" href="<?php echo U('edit?id='.$vo['id']); ?>" title="编辑">编辑</a>
                                    <a class="text-danger ajax-get confirm" href="<?php echo U('deletes?id='.$vo['id']); ?>"
                                       title="删除">删除</a>
                                    <a class="open-window no-refresh" href="<?php echo U('customer_log?id='.$vo['id']); ?>"
                                       title="会员日志">日志</a>
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
<div class="modal inmodal" id="uppwdModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form method="post" action="<?php echo U('up_password'); ?>" class="appidModal">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-body">
                    <p><strong>密码</strong></p>
                    <div class="form-group">
                        <input type="hidden" value="" name="id" id="uppwdid">
                        <input type="text" placeholder="请输入新的密码" value=""
                               class="form-control" name="password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" onclick="closeuppwdModal()">关闭
                    </button>
                    <button type="submit" class="btn btn-primary ajax-post" target-form="appidModal">保存</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal inmodal" id="rechargeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form method="post" action="<?php echo U('balance_add'); ?>" class="rechargeModal">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-body">
                    <input type="hidden" name="id" id="rechargeid">
                    <p><strong>充值金额</strong></p>
                    <div class="form-group">
                        <input type="text" placeholder="请输入要充值的金额（正数代表增加，负数代表扣除）" value=""
                               class="form-control" name="money" autocomplete="off" maxlength="8">
                    </div>
                    <p><strong>备注</strong></p>
                    <div class="form-group">
                        <input type="text" placeholder="请输入充值备注，如：付款单号、时间" value="线下充值，支付方式：，付款单号：，付款时间："
                               class="form-control" name="remark">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" onclick="closerechargeModal()">关闭
                    </button>
                    <button type="submit" class="btn btn-primary ajax-post" target-form="rechargeModal">保存</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal inmodal" id="googleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form method="post" action="<?php echo U('reset_google_auth_secret'); ?>" class="googleModal">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-body">
                    <p><strong>清除代理谷歌验证码</strong></p>
                    <input type="hidden" value="" name="id" id="googleid">
                    <div class="form-group" style="color: #f00;">
                        清除以后，代理用户登录时会再次弹出绑定谷歌验证码
                    </div>
                    <div class="form-group">
                        <input type="text" placeholder="请输入您当前管理员账号谷歌身份验证码，未设置可为空" value=""
                               class="form-control" name="verifycode" maxlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" onclick="closegoogleModal()">关闭
                    </button>
                    <button type="submit" class="btn btn-primary ajax-post" target-form="googleModal">清除</button>
                </div>
            </div>
        </div>
    </form>
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
    $(".resetpwd").click(function () {
        $("#uppwdid").val($(this).data("id"));
        $("#uppwdModal").show();
    });

    function closeuppwdModal() {
        $("#uppwdModal").hide();
    }

    $(".recharge").click(function () {
        $("#rechargeid").val($(this).data("id"));
        $("#rechargeModal").show();
    });

    function closerechargeModal() {
        $("#rechargeModal").hide();
    }

    $(".resetgoogle").click(function () {
        $("#googleid").val($(this).data("id"));
        $("#googleModal").show();
    });

    function closegoogleModal() {
        $("#googleModal").hide();
    }
</script>
</body>
</html>
