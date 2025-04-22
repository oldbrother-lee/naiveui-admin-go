<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:74:"/www/wwwroot/daichong1/public/../application/admin/view/customer/edit.html";i:1669454442;s:64:"/www/wwwroot/daichong1/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
<div class="wrapper wrapper-content">

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>用户编辑</h5>
                    <div class="ibox-tools">

                    </div>
                </div>
                <div class="ibox-content">
                    <form method="post" class="form-horizontal" action="<?php echo U('edit'); ?>">
                        <input type="hidden" name="id" value="<?php echo (isset($info['id']) && ($info['id'] !== '')?$info['id']:''); ?>">
                        <div class="form-group">
                            <div class="col-sm-3">
                                <label>用户名<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="username"
                                       value="<?php echo (isset($info['username']) && ($info['username'] !== '')?$info['username']:''); ?>"
                                       autocomplete="off">
                            </div>
                            <div class="col-sm-3">
                                <label>上级ID<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="f_id" value="<?php echo (isset($info['f_id']) && ($info['f_id'] !== '')?$info['f_id']:'0'); ?>"
                                       autocomplete="off">
                            </div>
                            <div class="col-sm-3">
                                <label>手机号<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="number" class="form-control" name="mobile"
                                       value="<?php echo (isset($info['mobile']) && ($info['mobile'] !== '')?$info['mobile']:''); ?>"
                                       autocomplete="off" maxlength="11">
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="col-sm-3">
                                <?php $types=C('CUS_TYPE');?>
                                <label>类型<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="type">
                                    <?php if(is_array($types) || $types instanceof \think\Collection || $types instanceof \think\Paginator): $i = 0; $__LIST__ = $types;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if($info &&  $info['type']==$key){ echo "selected='selected'"; } ?>
                                    ><?php echo $to; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label>用户等级<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="grade_id">
                                    <?php if(is_array($grades) || $grades instanceof \think\Collection || $grades instanceof \think\Paginator): $i = 0; $__LIST__ = $grades;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $to['id']; ?>" ><?php echo $to['grade_name']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label>授信额度<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="shouxin_e" value="<?php echo $info['shouxin_e']; ?>"
                                       autocomplete="off">
                            </div>
                        </div>

                        <?php if(!(empty($info) || (($info instanceof \think\Collection || $info instanceof \think\Paginator ) && $info->isEmpty()))): ?>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>用户二维码<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <img src="/<?php echo (isset($info['qrurl']) && ($info['qrurl'] !== '')?$info['qrurl']:''); ?>"
                                     style="display: block;margin-bottom: 10px;width: 100px;height: auto;" alt=""/>
                                <div><?php echo time_format($info['share_img_time']); ?></div>
                                <a class="text-info ajax-get" href="<?php echo U('del_poster',['id'=>$info['id']]); ?>">重新生成</a>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>用户头像<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <img id="headimg" src="<?php echo (isset($info['headimg']) && ($info['headimg'] !== '')?$info['headimg']:''); ?>"
                                     style="display: block;margin-bottom: 10px;width: 100px;height: auto;" alt=""/>
                                <button class="btn btn-success open-img-window" style="width:100px;" type="button"
                                        data-url="<?php echo U('widget/images'); ?>" data-max="1" data-name="headimg"><i
                                        class="fa fa-upload"></i>&nbsp;&nbsp;<span class="bold">上传</span></button>
                                <input type="hidden" name="headimg" value="<?php echo (isset($info['headimg']) && ($info['headimg'] !== '')?$info['headimg']:''); ?>"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary ajax-post" target-form="form-horizontal">
                                    确定
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/public/admin/js/ajaxfileupload.js" type="text/javascript"></script>
<script>
    window.changeIMG = function (name, res) {
        $("#" + name).attr("src", res);
        $("[name=" + name + "]").val(res);
    }
    $(function () {
        var type = $("[name=type]").children('option:selected').val();
        $.post("<?php echo U('get_grades'); ?>", {grade_type: type}, function (result) {
            console.log(result)
            $("[name=grade_id]").empty();
            for (var i = 0; i < result.data.length; i++) {
                var item = result.data[i];
                $("[name=grade_id]").append("<option  value=" + item.id + " " + (item.id == parseInt("<?php echo (isset($info['grade_id']) && ($info['grade_id'] !== '')?$info['grade_id']:''); ?>") ? "selected='selected'" : "") + " >" + item.grade_name + "</option>");
            }
        });
    });
    $("[name=type]").change(function () {
        var type = $(this).children('option:selected').val();
        $.post("<?php echo U('get_grades'); ?>", {grade_type: type}, function (result) {
            console.log(result)
            $("[name=grade_id]").empty();
            for (var i = 0; i < result.data.length; i++) {
                var item = result.data[i];
                $("[name=grade_id]").append("<option  value=" + item.id + ">" + item.grade_name + "</option>");
            }
        });
    });
</script>
</body>

</html>
