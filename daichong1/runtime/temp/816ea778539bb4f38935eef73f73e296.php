<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:83:"/www/wwwroot/115.126.57.143/public/../application/admin/view/daichongs/addwork.html";i:1728633213;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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

<link rel="stylesheet" type="text/css" href="/public/admin/css/plugins/select/select-addl.min.css"/>
<link rel="stylesheet" type="text/css" href="/public/admin/css/plugins/select/select-krajee.min.css"/>
<link rel="stylesheet" type="text/css" href="/public/admin/css/plugins/select/select.min.css"/>
<script type="text/javascript" src="/public/admin/js/plugins/select/select2.full.min.js"></script>
<script type="text/javascript" src="/public/admin/js/plugins/select/select2-krajee.min.js"></script>

<body class="gray-bg">
<div class="wrapper wrapper-content">

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>代收账号管理</h5>
                    <div class="ibox-tools">
                    </div>
                </div>
                <div class="ibox-content">
                    <form method="post" class="form-horizontal" action="<?php echo U(''); ?>">
                        <div class="form-group">
                            <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $info['user_id']; ?>">
                            <div class="col-sm-4">
                                <label>运营商<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select name="yunying" class="form-control m-b">
                                    <option value="1" <?php if(1 == $info['yunying']){ echo "selected"; } ?>>移动</option>
                                    <option value="2" <?php if(2 == $info['yunying']){ echo "selected"; } ?>>联通</option>
                                    <option value="3" <?php if(3 == $info['yunying']){ echo "selected"; } ?>>电信</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label>地区<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select id="prov_select" class="form-control m-b" name="prov_select[]" multiple placeholder="请选择地区" data-s2-options="provSelectOptions" data-krajee-select2="selectInitData" style="display:none">
                                </select>
<!--                                <span class="input-group-btn"><button id="updateAreaBtn" type="button" class="btn btn-sm btn-info ">更新地区</button></span>-->
                            </div>
                            <div class="col-sm-4">
                                <label>金额<span style="margin-left: 8px;color: #aaa;"></span></label>
                                 <input name="money" type="text" class="form-control" value="<?php echo $info['money']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>每次接单数量<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="count" value="<?php echo $info['count']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>超时时间<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <input type="text" class="form-control" name="te" value="<?php echo $info['te']; ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>任务状态<span style="margin-left: 8px;color: #aaa;"></span></label>
                                <select name="status" class="form-control m-b">
                                    <option value="1" <?php if(1 == $info['status']){ echo "selected"; } ?>>开启</option>
                                    <option value="0" <?php if(0 == $info['status']){ echo "selected"; } ?>>关闭</option>
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
    var provSelectOptions = {"themeCss":".select2-container--krajee","sizeCss":"","doReset":true,"doToggle":true,"doOrder":false};
    var provSelectOptions2 = {"themeCss":".select2-container--krajee","sizeCss":"","doReset":true,"doToggle":true,"doOrder":false};
    window.selectInitData = {"theme":"krajee","width":"100%","placeholder":"请选择","language":"zh-CN"};
    window.selectInitData2 = {"theme":"krajee","width":"100%","placeholder":"请选择","language":"zh-CN"};
    const daichongsProv = JSON.parse('<?php echo json_encode($daichongs_prov); ?>')
    const daichongsMoney = JSON.parse('<?php echo json_encode($info['money']); ?>')
    $(document).ready(function () {
        const provSelectList = [{"value": "北京","name": "北京"},{"value": "广东","name": "广东"},{"value": "上海","name": "上海"},{"value": "天津","name": "天津"},{"value": "重庆","name": "重庆"},{"value": "辽宁","name": "辽宁"},{"value": "江苏","name": "江苏"},{"value": "湖北","name": "湖北"},{"value": "四川","name": "四川"},{"value": "陕西","name": "陕西"},{"value": "河北","name": "河北"},{"value": "山西","name": "山西"},{"value": "河南","name": "河南"}, {"value": "吉林","name": "吉林"}, {"value": "黑龙江","name": "黑龙江"}, {"value": "内蒙古","name": "内蒙古"}, {"value": "山东","name": "山东"}, {"value": "安徽","name": "安徽"}, {"value": "浙江","name": "浙江"}, {"value": "福建","name": "福建"}, {"value": "湖南","name": "湖南"}, {"value": "广西","name": "广西"}, {"value": "江西","name": "江西"}, {"value": "贵州","name": "贵州"}, {"value": "云南","name": "云南"}, {"value": "西藏","name": "西藏"}, {"value": "海南","name": "海南"}, {"value": "甘肃","name": "甘肃"}, {"value": "宁夏","name": "宁夏"}, {"value": "青海","name": "青海"}, {"value": "新疆","name": "新疆"}];
        let selectHtml = ``
        provSelectList.forEach((item) => {
            if($.inArray(item.value , daichongsProv) == -1){
                selectHtml += `<option value="${item.value}" >${item.name}</option>`
            }else{
                selectHtml += `<option value="${item.value}" selected>${item.name}</option>`
            }
        })
        $('#prov_select').append(selectHtml)
        if ($('#prov_select').data('select2')){
            $('#prov_select').select2('destroy');
        }
        $.when($('#prov_select').select2(selectInitData)).done(initS2Loading('prov_select','provSelectOptions'));

        //钱数
        const provMoneyList = [{"value": "10","name": "10"},{"value": "20","name": "20"},{"value": "30","name": "30"},{"value": "50","name": "50"},{"value": "100","name": "100"},{"value": "200","name": "200"},{"value": "300","name": "300"},{"value": "500","name": "500"}];
        let selectHtml2 = ``
        provMoneyList.forEach((item) => {
            if($.inArray(item.value , daichongsMoney) == -1){
                selectHtml2 += `<option value="${item.value}" >${item.name}</option>`
            }else{
                selectHtml2 += `<option value="${item.value}" selected>${item.name}</option>`
            }
        })
        $('#prov_money').append(selectHtml2)
        if ($('#prov_money').data('select2')){
            $('#prov_money').select2('destroy');
        }
        $.when($('#prov_money').select2(selectInitData2)).done(initS2Loading('prov_money','provSelectOptions2'));

    });
    $("#updateAreaBtn").click(function(){
        let data = $('#prov_select').val()
        if(data == null){
            data = null
        }
        $.post('./update_prov', {'prov_data': data}, function (res){
            layer.msg('更新成功');
        })
    })
</script>
</html>
