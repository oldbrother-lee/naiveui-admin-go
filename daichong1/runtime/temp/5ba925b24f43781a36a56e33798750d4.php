<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:65:"/var/www/html/public/../application/admin/view/product/index.html";i:1655264244;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>充值产品列表</h5>
                </div>
                <div class="ibox-content">
                    <div class="tabs-container">
                        <ul class="nav nav-tabs">
                            <?php if(is_array($types) || $types instanceof \think\Collection || $types instanceof \think\Paginator): $i = 0; $__LIST__ = $types;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <li class="<?php echo $typeid==$vo['id']?'active':''; ?> typetab" data-typeid="<?php echo $vo['id']; ?>">
                                <a data-toggle="tab" href="#tab-<?php echo $vo['id']; ?>" aria-expanded="false"><?php echo $vo['type_name']; ?></a>
                            </li>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                    </div>
                    <div class="row input-groups" style="margin-top: 10px;">
                        <div class="col-md-12 m-b-xs form-inline text-right">
                            <div class="form-group">
                                <label class="control-label">分类:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="cate_id"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($cates) || $cates instanceof \think\Collection || $cates instanceof \think\Paginator): $i = 0; $__LIST__ = $cates;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $vo['id']; ?>"
                                    <?php if(I('cate_id')==$vo['id']){ echo "selected='selected'"; } ?>
                                    ><?php echo $vo['cate']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">显示:</label>
                                <?php $showstyle=C('PRODUCT_SHOW_CLIENT');?>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="show_style"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <?php if(is_array($showstyle) || $showstyle instanceof \think\Collection || $showstyle instanceof \think\Paginator): $i = 0; $__LIST__ = $showstyle;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if(I('show_style')==$key){ echo "selected='selected'"; } ?>><?php echo $vo; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">状态:</label>
                                <select class="input-sm form-control input-s-sm inline serach_selects" name="added"
                                        style="width: auto;">
                                    <option value="0">请选择</option>
                                    <option value="2"
                                    <?php if(I('added')==2){ echo "selected='selected'"; } ?>>上架</option>
                                    <option value="1"
                                    <?php if(I('added')==1){ echo "selected='selected'"; } ?>>下架</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <div class="input-group">
                                    <input type="hidden" name="id" value="<?php echo I('id'); ?>"/>
                                    <input type="hidden" name="type" value="<?php echo I('type'); ?>"/>
                                    <input type="text" name="key" placeholder="请输入套餐/描述" value="<?php echo I('key'); ?>"
                                           class="input-sm form-control">
                                    <span class="input-group-btn"><button type="button" id="search"
                                                                          class="btn btn-sm btn-primary"
                                                                          url="<?php echo U('index'); ?>"> 搜索</button></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 m-b-xs form-inline text-left" style="color: #f00">
                            重要提示：添加套餐的时候，套餐名字不要带其他数字，只能带面值的数字，比如“快充100元”、“100元”、“移动100元快充”，如果填写不符合要求可能导致部分充值退款计算出现异常，造成损失；
                        </div>
                        <div class="col-md-12 form-inline text-left">
                            <div class="form-group">
                                <button class="btn btn-sm btn-info ajax-post" target-form="i-checks"
                                        href="<?php echo U('added',['added'=>1]); ?>">
                                    批量上架
                                </button>
                                <button class="btn btn-sm btn-warning ajax-post" target-form="i-checks"
                                        href="<?php echo U('added',['added'=>0]); ?>">
                                    批量下架
                                </button>
                                <button class="btn btn-sm btn-info ajax-post" target-form="i-checks"
                                        href="<?php echo U('apiopen',['api_open'=>1]); ?>">
                                    批量打开接口
                                </button>
                                <button class="btn btn-sm btn-warning ajax-post" target-form="i-checks"
                                        href="<?php echo U('apiopen',['api_open'=>0]); ?>">
                                    批量关闭接口
                                </button>
                                <a class="btn btn-sm btn-primary open-window no-refresh"
                                   href="<?php echo U('cates',['type'=>$typeid]); ?>"
                                   title="分类管理">分类管理</a>
                                <a class="btn btn-sm btn-primary open-window" href="<?php echo U('edit',['type'=>$typeid]); ?>"
                                   title="新增套餐"><i
                                        class="fa fa-plus"></i> 新增产品</a>
                                <a class="btn btn-sm btn-white open-reload"><i
                                        class="glyphicon glyphicon-repeat"></i></a>
                            </div>
                        </div>

                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th><input type="checkbox" class="i-checks check-all">全选</th>
                                <th>ID</th>
                                <th>套餐/分类/描述</th>
                                <th>类型</th>
                                <th>基础价</th>
                                <th>价格</th>
                                <th>使用等级</th>
                                <th>其他</th>
                                <th>接口</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td style="width: 70px;">
                                    <input type="checkbox" class="i-checks ids" name="id[]" value="<?php echo $vo['id']; ?>">
                                </td>
                                <td><?php echo $vo['id']; ?></td>
                                <td>套餐：<?php echo $vo['name']; ?><br/>
                                    分类：<?php echo $vo['cate_name']; ?><br/>
                                    描述：<?php echo $vo['desc']; ?>
                                </td>
                                <td>
                                    <?php echo $vo['type_name']; ?><br/>
                                    <?php echo getISPText($vo['isp']); ?><br/>
                                    <?php echo C('PRODUCT_SHOW_CLIENT')[$vo['show_style']]; ?><br/>
                                    允许：<?php echo $vo['allow_pro']; ?>-<?php echo $vo['allow_city']; ?><br/>
                                    禁止：<?php echo $vo['forbid_pro']; ?>-<?php echo $vo['forbid_city']; ?><br/>
                                    <?php echo !empty($vo['is_jiema'])?'接码-'.getJmApiName($vo['jmapi_id']).'-'.getJmApiParamName($vo['jmapi_param_id']):''; ?>
                                </td>
                                <td><?php echo $vo['price']; ?></td>
                                <td>
                                    <?php if(is_array($vo['grade']) || $vo['grade'] instanceof \think\Collection || $vo['grade'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['grade'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$g): $mod = ($i % 2 );++$i;?>
                                    <?php echo $g['grade_name']; ?>:￥<?php echo $g['price']; ?><br/>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </td>
                                <td><?php echo getGradeIdsNameText($vo['grade_ids']); ?></td>
                                <td>排序:<?php echo $vo['sort']; ?><br/>
                                    tag:<?php echo $vo['ys_tag']; ?><br/>
                                    状态:<?php if($vo['added'] == '0'): ?><span
                                            class="text-danger">下架</span><?php else: ?><span
                                            class="text-info">上架</span><?php endif; ?><br/>
                                    备注:<?php echo $vo['remark']; ?>
                                </td>
                                <td>

                                    <?php if($vo['api_open'] == '1'): ?>
                                    开启<br/>
                                    <?php if($vo['delay_api'] > '0'): ?>
                                    延迟<?php echo $vo['delay_api']; ?>小时<br/>
                                    <?php endif; if(is_array($vo['api_list']) || $vo['api_list'] instanceof \think\Collection || $vo['api_list'] instanceof \think\Paginator): $key = 0; $__LIST__ = $vo['api_list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ap): $mod = ($key % 2 );++$key;?>
                                    <?php echo $key; ?>.
                                    <?php echo $ap['status']==1?'':'[停用]'; ?><?php echo getReapiName($ap['reapi_id']); ?>-<?php echo getReapiParamName($ap['param_id']); ?>-<?php echo $ap['num']; ?>次<br/>
                                    <?php endforeach; endif; else: echo "" ;endif; else: ?>
                                    关闭
                                    <?php endif; ?>
                                    <br/>
                                    api失败时:
                                    <?php $sysadapis=C('ODAPI_FAIL_STYLE');if($sysadapis == '3'): ?>
                                    <?php echo $vo['api_fail_style']==1?'直接失败':'压单'; else: ?>
                                    <?php echo $sysadapis==1?'直接失败':'压单'; endif; ?>
                                </td>
                                <td>
                                    <?php if($vo['added'] == '1'): ?>
                                    <a class="text-warning ajax-get"
                                       href="<?php echo U('added?id='.$vo['id'].'&added=0'); ?>" title="下架">下架</a>
                                    <?php else: ?>
                                    <a class="text-info ajax-get" href="<?php echo U('added?id='.$vo['id'].'&added=1'); ?>"
                                       title="上架">上架</a>
                                    <?php endif; ?>
                                    <a class="open-window" href="<?php echo U('edit?id='.$vo['id']); ?>" title="编辑">编辑</a>
                                    <a class="open-window" href="<?php echo U('Customer/price?product_id='.$vo['id']); ?>"
                                       title="<?php echo $vo['name']; ?>价格设置">产品价格</a>
                                    <a class="open-window no-refresh" href="<?php echo U('api?id='.$vo['id']); ?>"
                                       title="接口选择">接口选择</a>
                                    <a class="text-danger ajax-get confirm" href="<?php echo U('deletes?id='.$vo['id']); ?>"
                                       title="删除">删除</a>
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
<script>
    $(".typetab").click(function () {
        var type_id = $(this).data('typeid');
        $("[name=type]").val(type_id);
        $("[name=cate_id]").val(0);
        $("#search").trigger("click");
    });
    $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green'
        });
    });

</script>
</body>
</html>
