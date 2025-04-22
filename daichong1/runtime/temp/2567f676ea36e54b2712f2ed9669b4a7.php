<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:78:"/www/wwwroot/115.126.57.143/public/../application/admin/view/voucher/edit.html";i:1704520338;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>编辑海报配置</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                        <a class="close-link">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <form method="post" class="form-horizontal" action="<?php echo U('edit'); ?>">
                        <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
                        <div class="form-group">
                            <div class="col-sm-4">
                                可一边修改配置，一边刷新凭证，查看文字位置是否合适
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3">
                                <label>产品栏目<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="type_id">
                                    <?php if(is_array($types) || $types instanceof \think\Collection || $types instanceof \think\Paginator): $i = 0; $__LIST__ = $types;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $to['id']; ?>"
                                    <?php if(($info && $info['type_id']==$to['id'])){ echo "selected='selected'"; } ?>
                                    ><?php echo $to['type_name']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <?php $isps=C('ISP_TEXT');?>
                                <label>运营商<span style="margin-left: 8px;color: #aaa;">话费流量有效，其他类型随意选</span></label>
                                <select class="form-control m-b" name="isp">
                                    <?php if(is_array($isps) || $isps instanceof \think\Collection || $isps instanceof \think\Paginator): $i = 0; $__LIST__ = $isps;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if($info['isp']==$key){ echo "selected='selected'"; } ?>><?php echo $to; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label>背景<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <div class="input-group zifu">
                                    <input type="text" class="form-control" id="gvo_id_path" name="path"
                                           data-type="file"
                                           value="<?php echo $info['path']; ?>" readonly="readonly">
                                    <span class="input-group-btn">
                                            <button data-gid="gvo_id_path" onclick="choose_file($(this))" type="button"
                                                    class="btn btn-primary">
                                                <i class="fa fa-upload"></i>&nbsp;<span
                                                    class="bold">上传文件</span></button>
                                        </span>
                                    <input type="file" name="file" id="fileid" onchange="fileUpload()"
                                           style="display: none"/>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <label>备注说明-文字</label>
                                <input type="text" class="form-control" name="explain_text" value="<?php echo $info['explain_text']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <label>单号-文字大小<span style="margin-left: 8px;color: #aaa;">像素</span></label>
                                <input type="text" class="form-control" name="no_size" value="<?php echo $info['no_size']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>单号-距离左边<span style="margin-left: 8px;color: #aaa;">背景宽度%</span></label>
                                <input type="text" class="form-control" name="no_left" value="<?php echo $info['no_left']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>单号-距离顶部<span style="margin-left: 8px;color: #aaa;">背景高度%</span></label>
                                <input type="text" class="form-control" name="no_top" value="<?php echo $info['no_top']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>颜色<span style="margin-left: 8px;color: #aaa;">如：#000</span></label>
                                <input type="text" class="form-control" name="no_color" value="<?php echo $info['no_color']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>是否显示单号<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="is_no">
                                    <option value="1"
                                    <?php if($info['is_no']==1){ echo "selected='selected'"; } ?>>是</option>
                                    <option value="0"
                                    <?php if($info['is_no']==0){ echo "selected='selected'"; } ?>>否</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <label>账号-文字大小<span style="margin-left: 8px;color: #aaa;">像素</span></label>
                                <input type="text" class="form-control" name="mobile_size" value="<?php echo $info['mobile_size']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>账号-距离左边<span style="margin-left: 8px;color: #aaa;">背景宽度%</span></label>
                                <input type="text" class="form-control" name="mobile_left" value="<?php echo $info['mobile_left']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>账号-距离顶部<span style="margin-left: 8px;color: #aaa;">背景高度%</span></label>
                                <input type="text" class="form-control" name="mobile_top" value="<?php echo $info['mobile_top']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>颜色<span style="margin-left: 8px;color: #aaa;">如：#000</span></label>
                                <input type="text" class="form-control" name="mobile_color"
                                       value="<?php echo $info['mobile_color']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>是否显示充值账号<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="is_mobile">
                                    <option value="1"
                                    <?php if($info['is_mobile']==1){ echo "selected='selected'"; } ?>>是</option>
                                    <option value="0"
                                    <?php if($info['is_mobile']==0){ echo "selected='selected'"; } ?>>否</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <label>时间-文字大小<span style="margin-left: 8px;color: #aaa;">像素</span></label>
                                <input type="text" class="form-control" name="date_size" value="<?php echo $info['date_size']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>时间-距离左边<span style="margin-left: 8px;color: #aaa;">背景宽度%</span></label>
                                <input type="text" class="form-control" name="date_left" value="<?php echo $info['date_left']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>时间-距离顶部<span style="margin-left: 8px;color: #aaa;">背景高度%</span></label>
                                <input type="text" class="form-control" name="date_top" value="<?php echo $info['date_top']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>颜色<span style="margin-left: 8px;color: #aaa;">如：#000</span></label>
                                <input type="text" class="form-control" name="date_color" value="<?php echo $info['date_color']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>是否显示时间<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="is_date">
                                    <option value="1"
                                    <?php if($info['is_date']==1){ echo "selected='selected'"; } ?>>是</option>
                                    <option value="0"
                                    <?php if($info['is_date']==0){ echo "selected='selected'"; } ?>>否</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <label>价格-文字大小<span style="margin-left: 8px;color: #aaa;">像素</span></label>
                                <input type="text" class="form-control" name="price_size" value="<?php echo $info['price_size']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>价格-距离左边<span style="margin-left: 8px;color: #aaa;">背景宽度%</span></label>
                                <input type="text" class="form-control" name="price_left" value="<?php echo $info['price_left']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>价格-距离顶部<span style="margin-left: 8px;color: #aaa;">背景高度%</span></label>
                                <input type="text" class="form-control" name="price_top" value="<?php echo $info['price_top']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>颜色<span style="margin-left: 8px;color: #aaa;">如：#000</span></label>
                                <input type="text" class="form-control" name="price_color" value="<?php echo $info['price_color']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>是否显示价格<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="is_price">
                                    <option value="1"
                                    <?php if($info['is_price']==1){ echo "selected='selected'"; } ?>>是</option>
                                    <option value="0"
                                    <?php if($info['is_price']==0){ echo "selected='selected'"; } ?>>否</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <label>套餐-文字大小<span style="margin-left: 8px;color: #aaa;">像素</span></label>
                                <input type="text" class="form-control" name="product_size"
                                       value="<?php echo $info['product_size']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>套餐-距离左边<span style="margin-left: 8px;color: #aaa;">背景宽度%</span></label>
                                <input type="text" class="form-control" name="product_left"
                                       value="<?php echo $info['product_left']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>套餐-距离顶部<span style="margin-left: 8px;color: #aaa;">背景高度%</span></label>
                                <input type="text" class="form-control" name="product_top" value="<?php echo $info['product_top']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>颜色<span style="margin-left: 8px;color: #aaa;">如：#000</span></label>
                                <input type="text" class="form-control" name="product_color"
                                       value="<?php echo $info['product_color']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>是否显示套餐<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="is_product">
                                    <option value="1"
                                    <?php if($info['is_product']==1){ echo "selected='selected'"; } ?>>是</option>
                                    <option value="0"
                                    <?php if($info['is_product']==0){ echo "selected='selected'"; } ?>>否</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label>状态<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="status">
                                    <option value="1"
                                    <?php if($info['status']==1){ echo "selected='selected'"; } ?>>上架</option>
                                    <option value="0"
                                    <?php if($info['status']==0){ echo "selected='selected'"; } ?>>下架</option>
                                </select>
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
</body>
<script src="/public/admin/js/ajaxfileupload.js" type="text/javascript"></script>
<script>
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
                    toastr.success(ret.errmsg);
                } else {
                    toastr.error(ret.errmsg);
                }
            }
        })
    }
</script>

</html>
