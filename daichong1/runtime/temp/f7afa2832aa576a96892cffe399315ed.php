<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:78:"/www/wwwroot/115.126.57.143/public/../application/admin/view/member/index.html";i:1655264244;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>管理员</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-2 form-inline text-left">
                            <div class="form-group">
                                <a href="<?php echo U('edit'); ?>" class="btn btn-sm btn-primary open-window" title="新增"
                                   style="padding: 4px 12px;" type="button"><i
                                        class="fa fa-plus"></i> 新增</a>
                            </div>
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                        </div>
                        <div class="col-md-10 m-b-xs form-inline text-right">
                            <div class="form-group">
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="status"
                                        style="width: auto;">
                                    <option value="0"
                                    <?php if(I('status')== 0){ echo "selected='selected'"; } ?>>全部</option>
                                    <option value="1"
                                    <?php if(I('status')== 1){ echo "selected='selected'"; } ?>>禁用</option>
                                    <option value="2"
                                    <?php if(I('status')== 2){ echo "selected='selected'"; } ?>>启用</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" placeholder="请输入关键词(ID或昵称)"
                                           class="input-sm form-control" name="key"
                                           value="<?php echo I('key'); ?>">
                                    <span class="input-group-btn">
                                            <button type="button" id="search" url="<?php echo U('index'); ?>"
                                                    class="btn btn-sm btn-primary">搜索</button> </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>头像</th>
                                <th>登录名</th>
                                <th>性别</th>
                                <th>登录次数</th>
                                <th>注册IP</th>
                                <th>注册时间</th>
                                <th>最后登录IP</th>
                                <th>最后登录时间</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$member): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td>
                                    <?php echo $member['id']; ?>
                                </td>
                                <td><img style="width: 30px;height:30px; border-radius: 50%;"
                                         src="<?php echo $member['headimg']; ?>"></td>
                                <td><?php echo $member['nickname']; ?></td>
                                <td><?php if($member['sex'] == '1'): ?>
                                    <span>男</span>
                                    <?php else: ?>
                                    <span>女</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $member['login']; ?></td>
                                <td><?php echo $member['reg_ip']; ?></td>
                                <td><?php echo time_format($member['reg_time']); ?></td>
                                <td><?php echo $member['last_login_ip']; ?></td>
                                <td><?php echo time_format($member['last_login_time']); ?></td>
                                <td><?php if($member['status'] == '1'): ?>
                                    <span class="text-navy">已启用</span>
                                    <?php else: ?>
                                    <span class="text-danger">已禁用</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($member['status'] == '1'): ?>
                                    <a class="ajax-get text-warning" title="禁用"
                                       href="<?php echo U('Member/forbidden?id='.$member['id']); ?>">
                                        禁用</a>
                                    <?php else: ?>
                                    <a class="ajax-get text-info" title="启用"
                                       href="<?php echo U('Member/forbidden?id='.$member['id']); ?>">启用</a>
                                    <?php endif; ?>
                                    <a class="open-window text-info open-window" title="编辑"
                                       href="<?php echo U('Member/edit?id='.$member['id']); ?>">编辑</a>
                                    <a class="text-warn resetpwd" title="重置密码" data-id="<?php echo $member['id']; ?>">重置密码</a>
                                    <a class="ajax-get confirm text-danger" title="删除"
                                       href="<?php echo U('Member/deletes?id='.$member['id']); ?>">删除</a>
                                    <a class="text-warn resetgoogle" title="重置密码" data-id="<?php echo $member['id']; ?>">清除谷歌验证码</a>
                                </td>
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


<div class="modal inmodal" id="uppwdModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form method="post" action="<?php echo U('uppassword'); ?>" class="appidModal">
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

<div class="modal inmodal" id="googleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form method="post" action="<?php echo U('reset_google_auth_secret'); ?>" class="googleModal">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-body">
                    <p><strong>清除管理员谷歌验证码</strong></p>
                    <input type="hidden" value="" name="id" id="googleid">
                    <div class="form-group" style="color: #f00;">
                        清除以后，管理员用户登录时会再次弹出绑定谷歌验证码
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
    $(".resetpwd").click(function () {
        $("#uppwdid").val($(this).data("id"));
        $("#uppwdModal").show();
    });

    function closeuppwdModal() {
        $("#uppwdModal").hide();
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
