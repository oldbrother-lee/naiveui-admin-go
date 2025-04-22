<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:75:"/www/wwwroot/daichong1/public/../application/admin/view/customer/price.html";i:1686270556;s:64:"/www/wwwroot/daichong1/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
                    <h5>代理价格表</h5>
                </div>
                <div class="ibox-content">
                    <div class="row input-groups">
                        <div class="col-sm-4 m-b-xs">
                            <a class="btn btn-white open-reload"><i class="glyphicon glyphicon-repeat"></i></a>
                            <div class="form-group">
                                <button class="btn btn-sm btn-info allranges" title="多选框选中的产品，批量操作浮动价格"
                                        data-name="批量设置勾选产品的浮动价" id="batchPriceBtn">
                                    <i class="fa fa-check-square"></i>批量设置浮动价(利润)
                                </button>
                            </div>
                        </div>
                        <div class="col-sm-6" style="float: right;">
                            <div class="input-group">
                                <input type="hidden" name="product_id" value="<?php echo I('product_id'); ?>">
                                <input type="hidden" name="grade_id" value="<?php echo I('grade_id'); ?>">
                                <input type="text" name="key" placeholder="请输入套餐/描述" value="<?php echo I('key'); ?>"
                                       class="input-sm form-control">
                                <span class="input-group-btn"><button type="button" id="search"
                                                                      class="btn btn-sm btn-primary"
                                                                      url="<?php echo U('price'); ?>"> 搜索</button></span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th><input type="checkbox" class="i-checks check-all">全选</th>
                                <td>等级</td>
                                <th>产品</th>
                                <th>描述</th>
                                <th>备注</th>
                                <th>运营商</th>
                                <th>状态</th>
                                <th>基础价</th>
                                <th>浮动价(相对)</th>
                                <th>返利</th>
                                <th>编辑</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr>
                                <td align=center style="width: 70px;">
                                    <input type="checkbox" class="i-checks ids" name="pid[]" value="<?php echo $vo['id']; ?>">
                                </td>
                                <td><?php echo $vo['grade_name']; ?></td>
                                <td><?php echo $vo['name']; ?></td>
                                <td><?php echo $vo['desc']; ?></td>
                                <td><?php echo $vo['remark']; ?></td>
                                <td><?php echo getISPText($vo['isp']); ?></td>
                                <td><?php if($vo['added'] == '0'): ?><span
                                        class="text-danger">下架</span><?php else: ?><span
                                        class="text-info">上架</span><?php endif; ?>
                                </td>
                                <td><?php echo $vo['price']; ?></td>
                                <td><?php echo $vo['ranges']; ?></td>
                                <td>上下级关系中的等级差价格</td>
                                <td><a class="open-window" href="<?php echo U('price_edit?id='.$vo['id']); ?>" title="价格-<?php echo $vo['name']; ?>">编辑</a>
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
<!--弹出批量设置浮动价窗口-->
<div class="modal inmodal" id="rangesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <form method="post" action="<?php echo U('price_edits'); ?>" class="rangesModal">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-body">
                    <input type="hidden" name="id" id="rangesid">
                    <input type="hidden" name="product_id" id="ranges_product_id">
                    <p><strong id="rangesname">浮动价格</strong></p>
                    <p style="color: #f00">批量修改浮动价（利润）时，需输入浮动价基数，将自动计算不同额度浮动价</p>
                    <div class="form-group">
                        <input type="number" placeholder="请输入浮动价基数" value=""
                               class="form-control" name="ranges" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" onclick="closeRangesModal()">关闭
                    </button>
                    <button type="submit" class="btn btn-primary ajax-post no-close" target-form="rangesModal">保存
                    </button>
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

    // 绑定点击事件

    $("#batchPriceBtn").click(function () {
        $("#rangesname").text($(this).data("name") + "-利润");
        let ids = [];
        $("div.checked input[name='pid[]']").each(function (index,element){
            console.log('index:'+$(this).val());
            ids[index] = $(this).val();
        })
        $("#ranges_product_id").val(ids);
        $("[name=ranges]").val('');
        $("#rangesModal").show();
    });
    // 绑定点击事件取消
    function closeRangesModal() {
        $("#rangesModal").hide();
    }

</script>
</body>
</html>
