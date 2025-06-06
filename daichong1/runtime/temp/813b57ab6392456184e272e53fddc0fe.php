<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:77:"/www/wwwroot/115.126.57.143/public/../application/admin/view/index/index.html";i:1692898158;s:69:"/www/wwwroot/115.126.57.143/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
    .no-margins{
        font-size: 24px;
    }
    .amount-content{
        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }
</style>
<body class="gray-bg"  style="background-color:#D9FFFF">
<div class="wrapper wrapper-content">
    <div class="row">
        
<!--        <div class="col-sm-3">-->
<!--            <div class="ibox float-e-margins">-->
<!--                <div class="ibox-title">-->
<!--                    <span class="label label-blue pull-right">营业额</span>-->
<!--                    <h5>所有订单</h5>-->
<!--                </div>-->
<!--                <div class="ibox-content">-->
<!--                    <h1 class="no-margins">￥<?php echo $data['total_price']; ?></h1>-->
<!--                    <small>元</small>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-primary pull-right">今天</span>
                    <h5>今日新订单</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['today_amount']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['today_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-primary pull-right">今天</span>
                    <h5>今日新订单充值到账</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['today_yes_amount']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['today_yes_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-primary pull-right">今天</span>
                    <h5>今日新订单充值退款</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['today_no_amount']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['today_no_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-primary pull-right">今天</span>
                    <h5>今日新订单充值中</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['today_in_amount']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['today_in_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-warning pull-right">昨天</span>
                    <h5>昨日新订单</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['zuori_amount']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['zuori_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-warning pull-right">昨天</span>
                    <h5>昨日新订单充值到账</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['zuori_yes_amount']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['zuori_yes_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div><div class="col-sm-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-warning pull-right">昨天</span>
                <h5>昨日新订单充值退款</h5>
            </div>
            <div class="ibox-content amount-content">
                <div>
                    <span class="no-margins">￥<?php echo $data['zuori_no_amount']; ?></span>
                    <small>元</small>
                </div>
                <div>
                    <span class="no-margins"><?php echo $data['zuori_no_count']; ?></span>
                    <small>单</small>
                </div>
            </div>
        </div>
    </div><div class="col-sm-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-warning pull-right">昨天</span>
                <h5>昨日新订单充值中</h5>
            </div>
            <div class="ibox-content amount-content">
                <div>
                    <span class="no-margins">￥<?php echo $data['zuori_in_amount']; ?></span>
                    <small>元</small>
                </div>
                <div>
                    <span class="no-margins"><?php echo $data['zuori_in_count']; ?></span>
                    <small>单</small>
                </div>
            </div>
        </div>
    </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">前天</span>
                    <h5>前日新订单</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['qianri_amount']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['qianri_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">前天</span>
                    <h5>前日新订单充值到账</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['qianri_yes_amount']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['qianri_yes_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">前天</span>
                    <h5>前日新订单充值退款</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['qianri_no_amount']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['qianri_no_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">前天</span>
                    <h5>前日新订单充值中</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['qianri_in_amount']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['qianri_in_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-success pull-right">总订单</span>
                    <h5>所有总订单</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['total_price']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['total_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-success pull-right">总订单</span>
                    <h5>所有总订单充值到账</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['total_yes_price']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['total_yes_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-success pull-right">总订单</span>
                    <h5>所有总订单充值退款</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['total_no_price']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['total_no_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-success pull-right">总订单</span>
                    <h5>所有总订单充值中</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['total_ing_price']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['total_ing_count']; ?></span>
                        <small>单</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-primary pull-right">今日代理商</span>
                    <h5>今日代理商加款</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['today_jiakuan']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['today_jiakuan_count']; ?></span>
                        <small>次</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-primary pull-right">昨日代理商</span>
                    <h5>昨日代理商加款</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['zuotian_jiakuan']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['zuotian_jiakuan_count']; ?></span>
                        <small>次</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-primary pull-right">前日代理商</span>
                    <h5>前日代理商加款</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['qianri_jiakuan']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['qianri_jiakuan_count']; ?></span>
                        <small>次</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-primary pull-right">加款</span>
                    <h5>所有代理商总加款</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins">￥<?php echo $data['jiakuan']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><?php echo $data['jiakuan_count']; ?></span>
                        <small>次</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-primary pull-right">代理商</span>
                    <h5>所有代理商</h5>
                </div>
                <div class="ibox-content">
                    <div>
                        <span class="no-margins"><?php echo $data['agent_num']; ?></span>
                        <small>总人数</small>
                    </div>
                    <div>
                        <span class="no-margins"><small>总余额:</small>￥<?php echo $data['agent_balance']; ?></span>
                        <small>元</small>
                    </div>
                    <div>
                        <span class="no-margins"><small>总授信：</small>￥<?php echo $data['shouxin_e']; ?></span>
                        <small>元</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-success pull-right">累计</span>
                    <h5>会员数</h5>
                </div>
                <div class="ibox-content amount-content">
                    <div>
                        <span class="no-margins"><?php echo $data['cus_num']; ?></span>
                        <small>总人数</small>
                    </div>
                    <div>
                        <span class="no-margins">总余额：￥<?php echo $data['cus_balance']; ?></span>
                        <small>元</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-warning pull-right">当前</span>
                    <h5>携号转网查询剩余条数</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo $data['ispzw_balance']; ?></h1>
                    <small>条</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">参考</span>
                    <h5>短信余额</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo S('qxt800_balance')?S('qxt800_balance'):'-'; ?></h1>
                    <small>条，仅做参考，不完全准确</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-success pull-right">累计</span>
                    <h5>客户端升级付费</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins">￥<?php echo $data['total_price_upgrade']; ?></h1>
                    <small>元</small>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-danger pull-right">营业额</span>
                    <h5>待充值</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins">￥<?php echo $data['total_price_wait']; ?></h1>
                    <small>元</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div id="mains" style="width:100%;height:400px;"></div>
    </div>
</div>
</body>
<script src="/public/admin/js/echarts.min.js"></script>
<script>

    if (parseInt("<?php echo $data['zwsw']; ?>") > 0 && "<?php echo $data['ispzw_balance']; ?>" == "0.00") {
        toastr.warning("携号转往查询已经没有剩余条数了");
    }

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
                text: '营业金额统计（不含退款）',
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
                    name: '营业收入',
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
