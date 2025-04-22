<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:68:"/var/www/html/public/../application/admin/view/product/edit_api.html";i:1655264244;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
    <div class="row" id="page">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>接口选择</h5>
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
                    <div class="form-horizontal">
                        <input type="hidden" name="product_id" v-model="product_id">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>接口开关<span style="margin-left: 8px;color: #aaa;">关闭后会绕开此接口</span></label>
                                <select class="form-control m-b" v-model="status">
                                    <option value="1">打开</option>
                                    <option value="0">关闭</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>接口平台<span style="margin-left: 8px;color: #aaa;">渠道</span></label>
                                <select class="form-control m-b" v-model="apiselected"
                                        @change="apiSelectedChange">
                                    <option value="0">请选择</option>
                                    <option :value="item.id" v-for="(item,index) in api_list">{{item.name}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>选择套餐<span style="margin-left: 8px;color: #aaa;">渠道套餐</span></label>
                                <select class="form-control m-b" v-model="paramselected">
                                    <option value="0">请选择</option>
                                    <option :value="item.id" v-for="(item,index) in param_list">{{item.desc}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>重试次数<span style="margin-left: 8px;color: #aaa;">当接口回调失败时，该接口会再次执行提交，N次循环</span></label>
                                <input type="text" class="form-control" v-model="num">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label>支持运营商<span style="margin-left: 8px;color: #aaa;">话费流量渠道生效，其他类型渠道不用选；选择以后不匹配的订单会自动绕过，执行下一个接口。一个都不选代表不限制。</span></label><br/>
                                <block v-for="(item,index) in isps">
                                    <input type="checkbox" v-model="isp" :value="index"/>&nbsp;<lable>{{item}}</lable>&nbsp;
                                </block>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label>排序<span style="margin-left: 8px;color: #aaa;">值越小越靠前</span></label>
                                <input type="text" class="form-control" v-model="sort">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary" @click="saveApi">
                                    保 存
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script src="/public/admin/js/vue.min.js"></script>
<script>
    var v = new Vue({
        el: '#page',
        data: {
            info: eval('(<?php echo json_encode($info); ?>)'),
            product_id: "<?php echo I('product_id'); ?>",
            isps: eval('(<?php echo json_encode($isps); ?>)'),
            status: 0,
            sort: 0,
            num: 1,
            isp: [],
            api_list: [],
            apiselected: 0,
            param_list: [],
            paramselected: 0
        },
        created: function () {
            this.init();
            this.id = this.info.id;
            this.status = this.info.status;
            this.sort = this.info.sort;
            this.num = this.info.num;
            this.isp = this.info.isp.split(',');
            this.apiselected = this.info.reapi_id;
            this.paramselected = this.info.param_id;
            this.getReapiParam();
        },
        methods: {
            init: function () {
                var that = this;
                $.post("<?php echo U('reapi'); ?>", {}, function (ret) {
                    if (ret.errno == 0) {
                        that.api_list = ret.data;
                    }
                });
            },
            apiSelectedChange: function () {
                this.getReapiParam();
                console.log(this.apiselected);
            },
            getReapiParam: function () {
                if (!this.apiselected) {
                    return
                }
                $.post("<?php echo U('reapi_param'); ?>", {reapi_id: this.apiselected}, function (ret) {
                    if (ret.errno == 0) {
                        v.param_list = ret.data;
                    }
                });
            },
            saveApi: function () {
                $.post("<?php echo U('edit_api'); ?>", {
                    id: this.info.id,
                    product_id: this.product_id,
                    status: this.status,
                    sort: this.sort,
                    num: this.num,
                    isp: this.isp.toString(),
                    reapi_id: this.apiselected,
                    param_id: this.paramselected
                }, function (ret) {
                    if (ret.errno == 0) {
                        layer.alert(ret.errmsg, {
                            icon: 1
                        }, function () {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                        })
                    } else {
                        layer.alert(ret.errmsg, {
                            icon: 2
                        });
                    }
                });
            }
        }
    });
</script>
</html>
