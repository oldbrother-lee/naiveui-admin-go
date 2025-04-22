<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:64:"/var/www/html/public/../application/admin/view/webcfg/index.html";i:1655264244;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
    .zifu {
        width:500px;
    }

    .shuzi {
        width: 180px;
    }

    .wenben {
        width: 500px;
        height: 120px !important;
        resize: none
    }

    .shuzu {
        width: 500px;
        height: 120px !important;
        resize: none
    }

    .meiju {
        width: auto;
        max-width: 200px;
    }

    .btns {
        width: 120px;
        height: 40px;
        margin-right: 10px;
    }

    .dzimg {
        height: 100px;
        max-width: 80%;
        background-position: left top;
        background-size: 100px auto;
    }
</style>


<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeIn">
    <div class="row">
        <div class="col-sm-12">
            <div class="tabs-container">
                <ul class="nav nav-tabs">
                    <?php if(is_array($typelist) || $typelist instanceof \think\Collection || $typelist instanceof \think\Paginator): $k = 0; $__LIST__ = $typelist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tvo): $mod = ($k % 2 );++$k;?>
                    <li class="<?php echo $k==1?'active' : ''; ?>"><a data-toggle="tab" href="#tab-<?php echo $k; ?>" aria-expanded="false">
                        <?php echo $tvo['type']; ?>配置</a></li>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </ul>
                <div class="tab-content">
                    <?php if(is_array($typelist) || $typelist instanceof \think\Collection || $typelist instanceof \think\Paginator): $k = 0; $__LIST__ = $typelist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tvo): $mod = ($k % 2 );++$k;?>
                    <div id="tab-<?php echo $k; ?>" class="tab-pane <?php echo $k==1?'active' : ''; ?>">
                        <div class="col-sm-12 b-r panel-body">
                            <form action="<?php echo U('webcfg/edit'); ?>" method="post" class="peizhiModal">
                                <!--<input type="hidden" name="group" value="<?php echo $key; ?>">-->
                                <?php if(is_array($tvo['item']) || $tvo['item'] instanceof \think\Collection || $tvo['item'] instanceof \think\Paginator): $i = 0; $__LIST__ = $tvo['item'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$gvo): $mod = ($i % 2 );++$i;if($gvo['type'] == 0): ?>
                                <div class="form-group">
                                    <label class="js-copy" data-clipboard-text="<?php echo $gvo['name']; ?>"><?php echo $gvo['title']; ?></label>&nbsp;&nbsp;&nbsp;&nbsp;（<?php echo $gvo['remark']; ?>）
                                    <input type="number" onmousewheel="return false;" name="<?php echo $gvo['name']; ?>" class="form-control shuzi"
                                           value="<?php echo $gvo['value']; ?>">
                                </div>
                                <?php elseif($gvo['type'] == 1): ?>
                                <div class="form-group">
                                    <label class="js-copy" data-clipboard-text="<?php echo $gvo['name']; ?>"><?php echo $gvo['title']; ?></label>&nbsp;&nbsp;&nbsp;&nbsp;（<?php echo $gvo['remark']; ?>）
                                    <input type="text" name="<?php echo $gvo['name']; ?>" class="form-control zifu"
                                           value="<?php echo $gvo['value']; ?>">
                                </div>
                                <?php elseif($gvo['type'] == 2): ?>
                                <div class="form-group">
                                    <label class="js-copy" data-clipboard-text="<?php echo $gvo['name']; ?>"><?php echo $gvo['title']; ?></label>&nbsp;&nbsp;&nbsp;&nbsp;（<?php echo $gvo['remark']; ?>）
                                    <textarea type="text" name="<?php echo $gvo['name']; ?>"
                                              class="form-control wenben"><?php echo $gvo['value']; ?></textarea>
                                </div>
                                <?php elseif($gvo['type'] == 3): ?>
                                <div class="form-group">
                                    <label class="js-copy" data-clipboard-text="<?php echo $gvo['name']; ?>"><?php echo $gvo['title']; ?></label>&nbsp;&nbsp;&nbsp;&nbsp;（<?php echo $gvo['remark']; ?>）
                                    <textarea type="text" name="<?php echo $gvo['name']; ?>"
                                              onkeyup="this.value = this.value.replace(/[：]+$/,'')"
                                              class="form-control shuzu"><?php echo $gvo['value']; ?></textarea>
                                </div>
                                <?php elseif($gvo['type'] == 4): ?>
                                <div class="form-group">
                                    <label class="js-copy" data-clipboard-text="<?php echo $gvo['name']; ?>"><?php echo $gvo['title']; ?></label>&nbsp;&nbsp;&nbsp;&nbsp;（<?php echo $gvo['remark']; ?>）
                                    <select class="form-control m-b meiju" name="<?php echo $gvo['name']; ?>">
                                        <?php $_result=parse_config_attr($gvo['extra']);if(is_array($_result) || $_result instanceof \think\Collection || $_result instanceof \think\Paginator): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$evo): $mod = ($i % 2 );++$i;if($key == $gvo['value']): ?>
                                        <option value="<?php echo $key; ?>" selected="selected"><?php echo $evo; ?></option>
                                        <?php else: ?>
                                        <option value="<?php echo $key; ?>"><?php echo $evo; ?></option>
                                        <?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <?php elseif($gvo['type'] == 5): ?>
                                <div class="form-group">
                                    <label class="js-copy" data-clipboard-text="<?php echo $gvo['name']; ?>"><?php echo $gvo['title']; ?></label>&nbsp;&nbsp;&nbsp;&nbsp;（<?php echo $gvo['remark']; ?>）
                                    <!--<input type="text" name="<?php echo $gvo['name']; ?>" data-type="file" id="gvo_id<?php echo $gvo['id']; ?>"-->
                                           <!--class="form-control zifu"-->
                                           <!--value="<?php echo $gvo['value']; ?>" readonly="readonly">-->
                                    <!--<button class="btn btn-info btn-block" type="button" data-gid="gvo_id<?php echo $gvo['id']; ?>"-->
                                            <!--onclick="choose_file($(this))" style="width: 100px;margin-top: 10px;"><i-->
                                            <!--class="fa fa-upload"></i>&nbsp;&nbsp;<span-->
                                            <!--class="bold">上传文件</span></button>-->
                                    <div class="input-group zifu">
                                        <input type="text" class="form-control zifu" name="<?php echo $gvo['name']; ?>" data-type="file" id="gvo_id<?php echo $gvo['id']; ?>" value="<?php echo $gvo['value']; ?>" readonly="readonly">
                                        <span class="input-group-btn">
                                            <button data-gid="gvo_id<?php echo $gvo['id']; ?>" onclick="choose_file($(this))" type="button" class="btn btn-primary">
                                                <i class="fa fa-upload"></i>&nbsp;<span
                                                    class="bold">上传文件</span></button>
                                        </span>
                                    </div>
                                </div>
                                <?php elseif($gvo['type'] == 6): ?>
                                <div class="form-group">
                                    <label class="js-copy" data-clipboard-text="<?php echo $gvo['name']; ?>"><?php echo $gvo['title']; ?></label>&nbsp;&nbsp;&nbsp;&nbsp;（<?php echo $gvo['remark']; ?>）
                                    <input type="hidden" name="<?php echo $gvo['name']; ?>" data-type="img"
                                           value="<?php echo $gvo['value']; ?>">
                                    <div>
                                        <?php if(empty($gvo['value']) || (($gvo['value'] instanceof \think\Collection || $gvo['value'] instanceof \think\Paginator ) && $gvo['value']->isEmpty())): ?>
                                        <img class="dzimg show-big-img" src="/public/admin/img/webuploader.png" id="<?php echo $gvo['name']; ?>"
                                             data-url="<?php echo $gvo['value']; ?>" data-title="<?php echo $gvo['title']; ?>"/>
                                        <?php else: ?>
                                        <img class="dzimg show-big-img" src="<?php echo $gvo['value']; ?>" id="<?php echo $gvo['name']; ?>"
                                             data-url="<?php echo $gvo['value']; ?>" data-title="<?php echo $gvo['title']; ?>"/>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn btn-info btn-block open-img-window" data-title="选择图片"
                                            data-url="<?php echo U('widget/images'); ?>" data-max="1" data-name="<?php echo $gvo['name']; ?>" type="button" style="width: 100px;margin-top: 10px;"><i
                                            class="fa fa-upload"></i>&nbsp;&nbsp;<span
                                            class="bold">上传图片</span></button>
                                </div>
                                <?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                <div class="form-group">
                                        <button class="btn btn-primary btns ajax-post" type="submit"
                                                target-form="peizhiModal">保存
                                        </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="file" name="file" id="fileid" onchange="fileUpload()" style="display: none"/>

<script src="/public/admin/js/ajaxfileupload.js" type="text/javascript"></script>
<script>
    var btn = document.getElementsByClassName("js-copy");
    var clipboard = new Clipboard(btn);//实例化
    //复制成功执行的回调，可选
    clipboard.on('success', function (e) {
        toastr.info('配置关键词复制成功');
    });

    //复制失败执行的回调，可选
    clipboard.on('error', function (e) {
        toastr.warning('复制失败!请切换重试！');
    });
</script>
<script>
    $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
    });
    window.changeIMG = function (name, res) {
        $("#" + name).attr("src", res);
        $("[name=" + name + "]").val(res);
    }

    var gid;
    function choose_file(obj) {
        gid = obj.data("gid");
        $("#fileid").trigger("click");
    }

    function fileUpload() {
        var index = layer.load(1, {
            shade: [0.1, '#fff'] //0.1透明度的白色背景
        });
        //上传图片
        $.ajaxFileUpload({
            url: "<?php echo U('File/upload'); ?>", //文件上传到哪个地址，告诉ajaxFileUpload
            secureuri: false, //一般设置为false
            fileElementId: 'fileid', //文件上传控件的Id  <input type="file" id="fileUpload" name="file" />
            dataType: 'json', //返回值类型 一般设置为json
            success: function (ret, status)  //服务器成功响应处理函数
            {
                layer.closeAll();
                console.log(ret);
                if (ret.errno == 0) {
                    $("#" + gid).val(ret.data);
                    if ($("#" + gid).data('type') == 'img') {
                        $("#" + gid + "img").attr('src', ret.data);
                        $("#" + gid + "img").data('url', ret.data);
                    }
                    toastr.success(ret.errmsg);
                } else {
                    toastr.error(ret.errmsg);
                }
            }
        })
    }
</script>


</body>

</html>
