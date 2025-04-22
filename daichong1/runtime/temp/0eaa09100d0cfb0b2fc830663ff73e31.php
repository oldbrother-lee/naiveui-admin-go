<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:64:"/var/www/html/public/../application/admin/view/product/edit.html";i:1655264244;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>产品编辑</h5>
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
                            <div class="col-sm-6">
                                <label>套餐<span style="margin-left: 8px;color: #f00;">填写的时候带数字面值且只有面值的数字，比如“100元”,"快充100","四川100电费"</span></label>
                                <input type="text" class="form-control" name="name" value="<?php echo $info['name']; ?>">
                            </div>
                            <div class="col-sm-6">
                                <label>描述<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="desc" value="<?php echo $info['desc']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <label>类型<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="type">
                                    <?php if(is_array($types) || $types instanceof \think\Collection || $types instanceof \think\Paginator): $i = 0; $__LIST__ = $types;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $to['id']; ?>"
                                    <?php if(($info && $info['type']==$to['id']) || I('type')==$to['id']){ echo "selected='selected'"; } ?>
                                    ><?php echo $to['type_name']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label>分类<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="cate_id">
                                    <?php if(is_array($cates) || $cates instanceof \think\Collection || $cates instanceof \think\Paginator): $i = 0; $__LIST__ = $cates;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $to['id']; ?>"
                                    <?php if($info['cate_id']==$to['id']){ echo "selected='selected'"; } ?>
                                    ><?php echo $to['cate']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <?php $showstyle=C('PRODUCT_SHOW_CLIENT');?>
                                <label>显示端<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="show_style">
                                    <?php if(is_array($showstyle) || $showstyle instanceof \think\Collection || $showstyle instanceof \think\Paginator): $i = 0; $__LIST__ = $showstyle;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if($info['show_style']==$key){ echo "selected='selected'"; } ?>><?php echo $to; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>可用等级<span style="margin-left: 8px;color: #aaa;">如果一个都未选择，默认会所有等级显示</span></label><br/>
                                <?php if(is_array($grades) || $grades instanceof \think\Collection || $grades instanceof \think\Paginator): $i = 0; $__LIST__ = $grades;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;?>
                                <input type="checkbox" name="grade_ids[]" id="g<?php echo $to['id']; ?>"
                                       value="<?php echo $to['id']; ?>" <?php echo inArrayDou($info['grade_ids'],$to['id'])?'checked':''; ?> /><label for="g<?php echo $to['id']; ?>"><?php echo $to['grade_name']; ?></label>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3">
                                <label>基础价<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="price" value="<?php echo $info['price']; ?>">
                            </div>
                            <div class="col-sm-3">
                                <label>封顶价<span style="margin-left: 8px;color: #aaa;">店铺最高可设置的售价，0代表不限制</span></label>
                                <input type="text" class="form-control" name="max_price" value="<?php echo $info['max_price']; ?>">
                            </div>
                            <div class="col-sm-6">
                                <?php $isps=C('ISP_TEXT');?>
                                <label>运营商<span style="margin-left: 8px;color: #aaa;">话费/流量必选</span></label><br/>
                                <?php if(is_array($isps) || $isps instanceof \think\Collection || $isps instanceof \think\Paginator): $i = 0; $__LIST__ = $isps;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <input type="checkbox" name="isp[]"
                                       value="<?php echo $key; ?>" <?php echo strstr($info['isp'],$key.'')?'checked':''; ?> /><?php echo $vo; endforeach; endif; else: echo "" ;endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-6">
                                <label>允许下单省份<span style="margin-left: 8px;color: #aaa;">话费/流量/电费生效</span></label>
                                <input type="text" class="form-control" name="allow_pro" value="<?php echo $info['allow_pro']; ?>"
                                       placeholder="多个用英文,隔开，如：广东省,福建省">
                            </div>
                            <div class="col-sm-6">
                                <label>允许下单城市<span style="margin-left: 8px;color: #aaa;">话费/流量/电费生效</span></label>
                                <input type="text" class="form-control" name="allow_city" value="<?php echo $info['allow_city']; ?>"
                                       placeholder="多个用英文,隔开，如：深圳市,东莞市">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label>禁止下单省份<span style="margin-left: 8px;color: #aaa;">话费/流量/电费生效</span></label>
                                <input type="text" class="form-control" name="forbid_pro" value="<?php echo $info['forbid_pro']; ?>"
                                       placeholder="多个用英文,隔开，如：广东省,福建省">
                            </div>
                            <div class="col-sm-6">
                                <label>禁止下单城市<span style="margin-left: 8px;color: #aaa;">话费/流量/电费生效</span></label>
                                <input type="text" class="form-control" name="forbid_city" value="<?php echo $info['forbid_city']; ?>"
                                       placeholder="多个用英文,隔开，如：深圳市,东莞市">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label>套餐右上角<span style="margin-left: 8px;color: #aaa;">如：8.8折</span></label>
                                <input type="text" class="form-control" name="ys_tag" value="<?php echo $info['ys_tag']; ?>">
                            </div>
                            <div class="col-sm-6">
                                <label>备注<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="remark" value="<?php echo $info['remark']; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label>凭证充值金额/面值<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="voucher_name"
                                       value="<?php echo $info['voucher_name']; ?>">
                            </div>
                            <div class="col-sm-6">
                                <label>凭证支付金额/价格<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="voucher_price"
                                       value="<?php echo $info['voucher_price']; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-6">
                                <label>状态<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="added">
                                    <option value="1"
                                    <?php if($info['added']==1){ echo "selected='selected'"; } ?>>上架</option>
                                    <option value="0"
                                    <?php if($info['added']==0){ echo "selected='selected'"; } ?>>下架</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label>排序<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="sort" value="<?php echo $info['sort']; ?>">
                            </div>
                        </div>
                        <?php $sysadapis=C('ODAPI_FAIL_STYLE');if($sysadapis == '3'): ?>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>api失败时的处理方式<span
                                        style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="api_fail_style">
                                    <option value="1"
                                    <?php if($info['api_fail_style']==1){ echo "selected='selected'"; } ?>>直接失败</option>
                                    <option value="2"
                                    <?php if($info['api_fail_style']==2){ echo "selected='selected'"; } ?>
                                    >压单</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label>接口充值<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="api_open">
                                    <option value="1"
                                    <?php if($info['api_open']==1){ echo "selected='selected'"; } ?>>开启</option>
                                    <option value="0"
                                    <?php if($info['api_open']==0){ echo "selected='selected'"; } ?>>关闭</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label>延迟提交api（单位：小时）<span style="margin-left: 8px;color: #aaa;">新订单等待n小时后才开始提交</span></label>
                                <input type="text" class="form-control" name="delay_api" value="<?php echo $info['delay_api']; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-4">
                                <label>接码<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select class="form-control m-b" name="is_jiema">
                                    <option value="0"
                                    <?php if($info && $info['is_jiema']==0){ echo "selected='selected'"; } ?>>关闭</option>
                                    <option value="1"
                                    <?php if($info && $info['is_jiema']==1){ echo "selected='selected'"; } ?>>打开</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label>接码api<span style="margin-left: 8px;color: #aaa;"></span></label><br/>
                                <select class="form-control m-b" name="jmapi_id">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($jiemaapi) || $jiemaapi instanceof \think\Collection || $jiemaapi instanceof \think\Paginator): $i = 0; $__LIST__ = $jiemaapi;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $to['id']; ?>"
                                    <?php if($info && $info['jmapi_id']==$to['id']){ echo "selected='selected'"; } ?>
                                    ><?php echo $to['name']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label>接码api套餐<span style="margin-left: 8px;color: #aaa;"></span></label><br/>
                                <select class="form-control m-b" name="jmapi_param_id">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($jmapiparams) || $jmapiparams instanceof \think\Collection || $jmapiparams instanceof \think\Paginator): $i = 0; $__LIST__ = $jmapiparams;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $to['id']; ?>"
                                    <?php if($info && $info['jmapi_param_id']==$to['id']){ echo "selected='selected'"; } ?>
                                    ><?php echo $to['desc']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
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
<script>
    $("[name=jmapi_id]").change(function () {
        var jmapi_id = $(this).children('option:selected').val();
        $.post("<?php echo U('jmapi/get_jmapi_param'); ?>", {jmapi_id: jmapi_id}, function (result) {
            console.log(result)
            $("[name=jmapi_param_id]").empty();
            $("[name=jmapi_param_id]").append("<option  value=0>请选择</option>");
            for (var i = 0; i < result.data.length; i++) {
                var item = result.data[i];
                $("[name=jmapi_param_id]").append("<option  value=" + item.id + ">" + item.desc + "</option>");
            }
        });
    });
</script>

</html>
