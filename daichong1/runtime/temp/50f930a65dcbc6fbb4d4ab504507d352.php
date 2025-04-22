<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:65:"/var/www/html/public/../application/admin/view/daichong/mian.html";i:1678093984;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-md-8 form-inline text-left">


                            <div class="form-group">
                                <div class="input-group">
                                     <span class="input-group-btn">
                                            <button type="button"
                                                    class="btn btn-sm btn-info ">专区id</button> </span><input type="text" placeholder="请输入专区"
                                           class="input-sm form-control" id="zhuanqu" name="zhuanqu"
                                           value="<?php echo $user['zhuanqu']; ?>">

                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group">
                                   <span class="input-group-btn">
                                            <button type="button"
                                                    class="btn btn-sm btn-info ">账户</button> </span> <input type="text" placeholder="请输入账号"
                                           class="input-sm form-control" id="key" name="key"
                                           value="<?php echo $user['user']; ?>">

                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                     <input type="text" placeholder="请输入密码"
                                           class="input-sm form-control" id="pwd" name="pwd"
                                           value="<?php echo $user['pwd']; ?>">
                                    <span class="input-group-btn">
                                            <button type="button" id="baocun" url="<?php echo U('mian'); ?>"
                                                    class="btn btn-sm btn-info bc">登录账号</button> </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" placeholder="请输入超时时间"
                                           class="input-sm form-control" id="te" name="te"
                                           value="<?php echo $user['te']; ?>">
                                    <span class="input-group-btn">
                                            <button type="button"
                                                    class="btn btn-sm btn-info ">分</button> </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group">
                                       <span class="input-group-btn">
                                            <button type="button"
                                                    class="btn btn-sm btn-info ">接单金额</button> </span>
<!--                                    <input type="text" placeholder="请输入接单金额"-->
<!--                                           class="input-sm form-control" id="money" name="money"-->
<!--                                           value="<?php echo $user['money']; ?>">-->
<!--                                    <span class="input-group-btn">-->
<!--                                            <button type="button"-->
<!--                                                    class="btn btn-sm btn-info ">元</button> </span>-->



                                        <a href="javascript:setmoney(30)" class="btn btn-sm <?php if($user['money30'] == '1'): ?>btn-warning<?php else: ?>btn-primary<?php endif; ?>  "   title=""
                                           style="padding: 4px 12px;" type="button">30</a>


                                        <a href="javascript:setmoney(50)" class="btn btn-sm <?php if($user['money50'] == '1'): ?>btn-warning<?php else: ?>btn-primary<?php endif; ?> "   title=""
                                           style="padding: 4px 12px;" type="button">50</a>


                                        <a href="javascript:setmoney(100)" class="btn btn-sm <?php if($user['money100'] == '1'): ?>btn-warning<?php else: ?>btn-primary<?php endif; ?> "   title=""
                                           style="padding: 4px 12px;" type="button">100</a>

                                    <a href="javascript:setmoney(200)" class="btn btn-sm <?php if($user['money200'] == '1'): ?>btn-warning<?php else: ?>btn-primary<?php endif; ?> "   title=""
                                       style="padding: 4px 12px;" type="button">200</a>

                                    <a href="javascript:setmoney(300)" class="btn btn-sm <?php if($user['money300'] == '1'): ?>btn-warning<?php else: ?>btn-primary<?php endif; ?> "   title=""
                                    style="padding: 4px 12px;" type="button">300</a>
                                    <a href="javascript:setmoney(500)" class="btn btn-sm <?php if($user['money500'] == '1'): ?>btn-warning<?php else: ?>btn-primary<?php endif; ?> "   title=""
                                    style="padding: 4px 12px;" type="button">500</a>




                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                     <span class="input-group-btn">
                                            <button type="button"
                                                    class="btn btn-sm btn-info ">每次</button> </span>
                                    <input type="text" placeholder="请输入每次接单数量"
                                           class="input-sm form-control" id="count" name="count"
                                           value="<?php echo $user['count']; ?>">
                                    <span class="input-group-btn">
                                            <button type="button"
                                                    class="btn btn-sm btn-info ">单</button> </span>
                                </div>
                            </div>


                            <div class="form-group">
                                <a href="javascript:void(0)" class="btn btn-sm    <?php if($user['type'] == '1'): ?>btn-warning<?php else: ?>btn-primary<?php endif; ?> " id="qd" title="启动"
                                   style="padding: 4px 12px;" type="button"><?php if($user['type'] == '1'): ?>停止程序<?php else: ?>开始接单<?php endif; ?></a>
                            </div>
                            <div class="form-group">
                                <a href="javascript:void(0)" class="btn btn-sm btn-primary zdsx"   title="自动刷新"
                                style="padding: 4px 12px;" type="button">关闭自动刷新</a>
                            </div>
                            <div class="form-group">
                                <a href="javascript:void(0)" class="btn btn-sm btn-primary sjqingli"   title="清理数据"
                                   style="padding: 4px 12px;" type="button">清理数据</a>
                            </div>
                            <div class="form-group">
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                        </div>




                        <div class="col-md-4 m-b-xs form-inline text-right">
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
                                            <button type="button" id="search" url="<?php echo U('mian'); ?>"
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
                                <td
                                        style="
<?php if($member['is_cs'] == 1){echo "color:#00c103";} if($member['is_cs'] == 2){echo "color:#e7c002";} if($member['is_cs'] == 3){echo "color:#c3061a";} ?>
"

                                >
                                    <?php echo $member['id']; ?>
                                </td>
                                <td>
                                    <?php echo $member['order_id']; ?>
                                </td>
                                <td><?php echo $member['yr_order_id']; ?></td>
                                <td><?php echo $member['account']; ?></td>
                                <td><?php echo $member['denom']; ?>
                                </td>
                                <td><?php echo $member['settlePrice']; ?></td>
                                <td><?php echo $member['createTime']; ?></td>
                                <td><?php echo $member['chargeTime']; ?></td>
                                <td><?php echo $member['uploadTime']; ?></td>
                                <td><?php if(($member['status'] == 5)): ?> 充值中

                                    <?php elseif($member['status'] == 6): ?>核实中
                                    <?php elseif($member['status'] == 7): ?>申请人工介入
                                    <?php elseif($member['status'] == 8): ?>待确认
                                    <?php elseif($member['status'] == 9): ?>弃单

                                    <?php else: ?>
                                    未知

                                    <?php endif; ?>/<?php echo $member['status']; ?></td>
                                <td style="">
                                    <?php if(($member['type'] == 0)): ?> 系统充值中

                                    <?php elseif($member['type'] == 1): ?>已上报失败
                                    <?php elseif($member['type'] == 2): ?>已上报成功

                                    <?php endif; ?>


                                </td>
                                <td>
                                    <?php if(($member['settleStatus'] == 0)): ?> 未结算

                                    <?php elseif($member['settleStatus'] == 1): ?>已结算
                                    <?php elseif($member['settleStatus'] == 3): ?>结算中

                                    <?php endif; ?>


                                </td>
                                <td>

                                    <a class="ajax-get text-warning" title="上报成功"
                                       href="<?php echo U('setstaus?id='.$member['order_id']); ?>">
                                        上报成功</a>
                                    <?php if($member['status'] == '1'): else: endif; ?>
                                    <a class="ajax-get text-danger" title="上报失败"
                                       href="<?php echo U('setstaus_w?id='.$member['order_id']); ?>">上报失败</a>


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

        setmoneysss(1);
        setInterval("startRequest()",10000);
    });
    //预览账号密码
    // $(function () {
    //     setTimeout(function () {
    //         myrefresh();
    //     }, 3000);
    //     setTimeout('myrefresh()',1000);
    // });

    function startRequest(){

        var sss = $("#sss").val();
        layer.msg(sss);
        if(sss>0){
            layer.msg('自动刷新启动成功！');
            location.reload();
        }
    }

    function setmoneysss(e){

        var url = "<?php echo url('daichong/zidonghua'); ?>";
        $.get(url+"?money=money"+e,function(data,status){


            // alert("数据: " + data + "\n状态: " + status);
        });



    }



    function setmoney(e){

        var url = "<?php echo url('daichong/moneyset'); ?>";
        $.get(url+"?money=money"+e,function(data,status){
            if(data.code==1){
                layer.msg(e+'金额设置成功！');
                location.reload();
            }else{
                layer.msg(e+'设置失败！');
                location.reload();
            }

            // alert("数据: " + data + "\n状态: " + status);
        });



    }

    $('.sjqingli').click(function () {

        var url = "<?php echo url('daichong/deall'); ?>";
                alert("操作后会清理完所有数据 注意这是危险操作！");
        $.get(url,function(data,status){
            if(data.code==1){
                layer.msg('清理成功！');
            }else{
                layer.msg('清理失败！');
            }

            // alert("数据: " + data + "\n状态: " + status);
        });
    });
    $('#baocun').click(function () {
       var key = $("#key").val();
       var pwd = $("#pwd").val();
       var te = $("#te").val();
        var zhuanqu  = $("#zhuanqu").val();
        if(zhuanqu.length<1){

            layer.msg('请输入专区号！');
            return ;
        }
       if(key.length<1){

           layer.msg('请输入用户名！');
           return ;
       }
       if(pwd.length<1){
           layer.msg('请输入密码！');
           return ;
       }
        var url = "<?php echo url('login/dclogin'); ?>";
        $.get(url+"?user="+key+"&pwd="+pwd+"&te="+te+"&zhuanqu="+zhuanqu,function(data,status){
                if(data.code==1){
                    layer.msg('保存账号成功！');
                }else{
                    layer.msg('保存账号失败！');
                }

           // alert("数据: " + data + "\n状态: " + status);
        });


        $('#baocun').addClass('btn-warning');
    });

    $('.zdsx').click(function () {
        layer.msg('自动刷新！');
        var txt =  $('.zdsx').text();
        console.log(txt);
        if(txt=='关闭自动刷新'){
            $('.zdsx').text('开启自动刷新');
            $('.zdsx').removeClass('btn-info');
            $('.zdsx').addClass('btn-warning');
            $("#sss").val(0);
        }else{
            $("#sss").val(1);
            $('.zdsx').text('关闭自动刷新');
            $('.zdsx').addClass('btn-info');
            $('.zdsx').removeClass('btn-warning');

        }

    });
    //ajax 加入S 缓存提示系统启动成功
    $('#qd').click(function () {
        var money = $("#money").val();
        var count = $("#count").val();

        var txt =  $('#qd').text();
        console.log(txt);

        if(txt=='停止程序'){
            $('#qd').text('启动程序');
            $('#qd').addClass('btn-info');
            $('#qd').removeClass('btn-warning');

            //停止操作
            var url = "<?php echo url('daichong/stop'); ?>";
            $.get(url+"?user="+key+"&pwd="+pwd,function(data,status){
                if(data.code==1){
                    layer.msg('系统停止成功！');
                }else{
                    layer.msg('系统停止失败！');
                }

                // alert("数据: " + data + "\n状态: " + status);
            });


        }else{
            $('#qd').text('停止程序');
            $('#qd').removeClass('btn-info');
            $('#qd').addClass('btn-warning');
            //启动操作
            var url = "<?php echo url('daichong/start'); ?>";
            $.get(url+"?&money="+money+'&count='+count,function(data,status){
                if(data.code==1){
                    layer.msg('系统启动成功！');
                }else{
                    layer.msg('系统启动失败！');
                }

                // alert("数据: " + data + "\n状态: " + status);
            });

        }



    });



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
