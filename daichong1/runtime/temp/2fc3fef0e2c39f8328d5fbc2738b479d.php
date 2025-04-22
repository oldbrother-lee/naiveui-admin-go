<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:77:"/www/wwwroot/115.126.57.143/public/../application/agent/view/index/index.html";i:1669529428;s:69:"/www/wwwroot/115.126.57.143/application/agent/view/public/header.html";i:1655264248;}*/ ?>
<!-- lq      -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo C('WEB_SITE_TITLE'); ?></title>
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="/public/agent/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/public/agent/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link href="/public/agent/css/animate.css" rel="stylesheet">
    <link href="/public/agent/css/style.css?v8" rel="stylesheet">
    <link href="/public/agent/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="/public/agent/css/layx.min.css" rel="stylesheet"/>
    <link href="/public/agent/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/public/agent/css/animate.css" rel="stylesheet">
    <script src="/public/agent/js/jquery.min.js?v=2.1.4"></script>
    <script src="/public/agent/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/public/agent/js/plugins/layer/layer.min.js"></script>
    <script src="/public/agent/js/content.js"></script>
    <script src="/public/agent/js/plugins/toastr/toastr.min.js"></script>
    <script src="/public/agent/js/dayuanren.js?v121"></script>
    <script src="/public/agent/js/layx.js" type="text/javascript"></script>
    <script src="/public/agent/js/plugins/iCheck/icheck.min.js"></script>
    <script src="/public/agent/js/clipboard.min.js"></script>
    <script src="/public/agent/js/vue.min.js?v=3.3.6"></script>
    <script src="/public/agent/js/util.js?V=1"></script>
    <script src="/public/agent/js/laydate/laydate.js" type="text/javascript"></script>
    <script src="/public/agent/js/ajaxfileupload.js?v1" type="text/javascript"></script>
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
                    <h5>平台公告</h5>
                    <div class="ibox-tools">
                        <a class="text-success" target="_blank" href="<?php echo U('all_notice_doc'); ?>">历史公告></a>
                    </div>
                </div>
                <div class="ibox-content" id="noticeid">

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-success pull-right">当前</span>
                    <h5>账户余额</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $data['balance']; ?></h1>
                    <small>元</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-info pull-right">累计</span>
                    <h5>累计消费金额</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $data['leiji_total_price']; ?></h1>
                    <small>元</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">今日</span>
                    <h5>今日消费金额</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $data['today_total_price']; ?></h1>
                    <small>元</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-primary pull-right">累计</span>
                    <h5>累计下单</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $data['order_num']; ?></h1>
                    <small>次</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">今日</span>
                    <h5>今日下单</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $data['today_order_all_num']; ?></h1>
                    <small>单</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-primary pull-right">所有</span>
                    <h5>所有充值中</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $data['order_ing_num']; ?></h1>
                    <small>单</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">今日</span>
                    <h5>今日成功</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $data['today_order_sus_num']; ?></h1>
                    <small>单</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">今日</span>
                    <h5>今日失败</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $data['today_order_fail_num']; ?></h1>
                    <small>单</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">额度</span>
                    <h5>授信额度</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $data['shouxin_e']; ?></h1>
                    <small>允许余额负数的范围</small>
                </div>
            </div>
        </div>

    </div>
    <div class="row">
        <div id="mains" style="width:100%;height:400px;"></div>
    </div>
</div>
</body>
<script src="/public/agent/js/echarts.min.js"></script>
<script>

    $.post("<?php echo U('index/notice_doc'); ?>", {}, function (result) {
        if (result.errno == 0) {
            $("#noticeid").html(result.data.body);
            if (result.data.is_read == 0) {
                layer.open({
                    type: 1,
                    title: "平台公告",
                    content: $('#noticeid')
                });
            }
        } else {
            console.log('未查询到数据');
        }
    });

    $.post("<?php echo U('index/statistics'); ?>", {}, function (result) {
        if (result.errno == 0) {
            var date = [];
            var data = [];
            for (var i = 0; i < result.data.length; i++) {
                date.push(result.data[i].time);
                data.push(parseFloat(result.data[i].price));
            }
            show_charts(date, data);
        } else {
            console.log('未查询到数据');
        }
    });

    function show_charts(date, data) {
        var myChart = echarts.init(document.getElementById('mains'));
        myChart.setOption({
            color: ['#ff9c00'],
            tooltip: {
                trigger: 'axis',
                axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                    type: 'line'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            title: {
                left: 'center',
                text: '消费金额统计',
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: [
                {
                    type: 'category',
                    data: date,
                    axisTick: {
                        alignWithLabel: true
                    }
                }
            ],
            yAxis: [
                {
                    type: 'value'
                }
            ],
            series: [
                {
                    name: '消费金额',
                    itemStyle: {
                        color: 'rgb(255, 70, 131)'
                    },
                    smooth: true,
                    symbol: 'none',
                    sampling: 'average',
                    type: 'line',
                    barWidth: '60%',
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                            offset: 0,
                            color: 'rgb(255, 158, 68)'
                        }, {
                            offset: 1,
                            color: 'rgb(255, 70, 131)'
                        }])
                    },
                    data: data
                }
            ]
        });
    }
</script>
</html>
