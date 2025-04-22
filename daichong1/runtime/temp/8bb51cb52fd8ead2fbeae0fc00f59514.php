<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:69:"/www/wwwroot/daichong1/public/../application/admin/view/menu/add.html";i:1655264244;s:64:"/www/wwwroot/daichong1/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
    * {
        margin: 0;
        padding: 0;
    }

    .main {
        background: #fff;
        padding: 20px;
        color: #404040;
    }

    h2 {
        display: block;
        font-size: 1.5em;
        -webkit-margin-before: 0.83em;
        -webkit-margin-after: 0.83em;
        -webkit-margin-start: 0px;
        -webkit-margin-end: 0px;
        font-weight: bold;
    }
</style>
<body class="gray-bg">
<div class="wrapper wrapper-content" id="page">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo isset($info['id'])?'编辑':'新增'; ?>菜单</h5>
                </div>
                <div class="ibox-content">
                    <form role="form" action="<?php echo U(); ?>" method="post" class="menu_form form-horizontal">
                        <div class="form-group">
                            <?php $module=C('MENU_MODULE');?>
                            <div class="col-sm-12">
                                <label>模块<span style="margin-left: 8px;color: #aaa;">该地址所属模块</span></label>
                                <select class="form-control m-b" name="module">
                                    <?php if(is_array($module) || $module instanceof \think\Collection || $module instanceof \think\Paginator): $i = 0; $__LIST__ = $module;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $to; ?>"
                                    <?php if($info['module']==$to){ echo "selected='selected'"; } ?>><?php echo $to; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>标题<span style="margin-left: 8px;color: #aaa;">（用于后台显示的配置标题）</span></label>
                                <input type="text" placeholder="请输入菜单标题" class="form-control" name="title"
                                       value="<?php echo (isset($info['title']) && ($info['title'] !== '')?$info['title']:''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>排序<span style="margin-left: 8px;color: #aaa;">（用于分组显示的顺序）</span></label>
                                <input type="text" placeholder="" class="form-control" name="sort"
                                       value="<?php echo (isset($info['sort']) && ($info['sort'] !== '')?$info['sort']:0); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>链接<span style="margin-left: 8px;color: #aaa;">（U函数解析的URL或者外链）</span></label>
                                <input type="text" placeholder="" class="form-control" name="url"
                                       value="<?php echo (isset($info['url']) && ($info['url'] !== '')?$info['url']:''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>上级菜单<span style="margin-left: 8px;color: #aaa;">（所属的上级菜单）</span></label>
                                <select class="js-example-basic-single select form-control m-b" name="pid">
                                    <?php if(is_array($Menus) || $Menus instanceof \think\Collection || $Menus instanceof \think\Paginator): $i = 0; $__LIST__ = $Menus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo $menu['id']; ?>"
                                    <?php if($info['pid']==$menu['id'] || $menu['id']==I('pid')){ echo "selected='selected'"; } ?>
                                    ><?php echo $menu['title_show']; ?></option>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>图标<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" placeholder="" class="form-control" name="icon"
                                       value="<?php echo (isset($info['icon']) && ($info['icon'] !== '')?$info['icon']:''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>类型<span style="margin-left: 8px;color: #aaa;">菜单地址：后台会自动显示到菜单栏，最多显示三级；URL地址：普通地址，不显示到菜单栏；开放性地址：不会检查权限，所以不会出现到授权管理中</span></label>
                                <select class="form-control m-b" name="type">
                                    <option value="0"
                                    <?php if($info['type']==0){ echo "selected='selected'"; } ?>>菜单地址</option>
                                    <option value="1"
                                    <?php if($info['type']==1){ echo "selected='selected'"; } ?>>URL地址</option>
                                    <option value="2"
                                    <?php if($info['type']==2){ echo "selected='selected'"; } ?>>开放性地址</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>是否隐藏</label>
                                <div>
                                    <input type="radio" name="hide"
                                           value="0" <?php if($info['hide']==0 || $info['hide'] == null){ echo "checked"; } ?>
                                    > <i></i> 否</label>
                                    <input type="radio" name="hide"
                                           value="1" <?php if($info['hide']==1 && $info['hide'] != null){ echo "checked"; } ?>
                                    > <i></i> 是</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>仅开发者模式可见</label>
                                <div>
                                    <input type="radio" name="is_dev"
                                           value="0" <?php if($info['is_dev']==0 || $info['is_dev'] == null){ echo "checked"; } ?>
                                    > <i></i> 否</label>
                                    <input type="radio" name="is_dev"
                                           value="1" <?php if($info['is_dev']==1 && $info['is_dev'] != null){ echo "checked"; } ?>
                                    > <i></i> 是</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>说明<span style="margin-left: 8px;color: #aaa;">（菜单详细说明）</span></label>
                                <input type="text" placeholder="" class="form-control" name="tip"
                                       value="<?php echo (isset($info['tip']) && ($info['tip'] !== '')?$info['tip']:''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <input type="hidden" name="id" value="<?php echo (isset($info['id']) && ($info['id'] !== '')?$info['id']:''); ?>">
                                <button class="btn btn-primary ajax-post" type="submit" target-form="menu_form">
                                    <strong>确 定</strong></button>
                                <button class="btn btn-white" style="margin-left: 10px;"
                                        onclick="javascript:history.back(-1);return false;"><strong>返 回</strong>
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
</html>