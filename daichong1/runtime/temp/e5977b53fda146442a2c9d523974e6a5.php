<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:78:"/var/www/html/public/../application/admin/view/daichong/upload_token_work.html";i:1704686572;s:55:"/var/www/html/application/admin/view/public/header.html";i:1655264244;}*/ ?>
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
<div class="wrapper wrapper-content animated fadeIn">
  <div class="row">
    <div class="col-sm-12">
      <div class="ibox float-e-margins">
        <div class="ibox-title">
          <h5>凭证上传日志</h5>
        </div>
        <div class="ibox-content">
          <div class="row input-groups">
            <div class="col-md-8 form-inline text-left">
              <div class="form-group">
                <<div class="input-group">
                  <input type="text" placeholder="请输入重试次数" class="input-sm form-control" id="pwd" name="times"
                         value="<?php echo isset($times)?$times: 0; ?>">
                </div>
                  <div class="input-group">
                  <input type="text" placeholder="是否开启自动上传(1开启, 0关闭)" class="input-sm form-control" id="pwd" name="open"
                         value="<?php echo isset($open)?$open: 0; ?>">
                         </div>
                         <div class="input-group">
                  <span class="input-group-btn">
                    <button type="button" id="search" url="<?php echo U('uploadTokenWork'); ?>" class="btn btn-sm btn-info bc">保存</button>
                  </span>
                  </div>
                  <div class="input-group">
                  <div class="form-group">
                    <a href="javascript:void(0)" class="btn btn-sm btn-primary sjqingli"   title="清理数据"
                       style="padding: 4px 12px;" type="button">清理数据</a>
                       </div>
                </div>
              </div>
              <div class="form-group">
                <a class="btn btn-sm btn-white open-reload"><i
                        class="glyphicon glyphicon-repeat"></i></a>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
              <tr>
                <th>ID</th>
                <th>代充单号</th>
                <th>执行时间</th>
                <th>上传次数</th>
                <th>上传状态</th>
                <th>操作</th>
              </tr>
              </thead>
              <tbody>
              <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$log): $mod = ($i % 2 );++$i;?>
              <tr>
                <td>
                <?php echo $log['id']; ?>
                </td>
                <td>
                  <?php echo $log['order_id']; ?>
                </td>
                <td><?php echo $log['created_at']; ?></td>
                <td><?php echo $log['times']; ?></td>
                <td><?php if(($log['status'] == 1)): ?> 进行中
                  <?php elseif($log['status'] == 2): ?>完成
                  <?php else: ?>
                  开始上传
                  <?php endif; ?></td>
                <td>
                  <a class="open-window no-refresh" href="<?php echo U('show_work?id='.$log['id']); ?>"
                     title="详情">日志</a>
                </td>
              </tr>
              <?php endforeach; endif; else: echo "" ;endif; ?>
              </tbody>
            </table>

          </div>
          <?php echo $page; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<input id="sss" type="hidden" value="1">

<script>
$('.sjqingli').click(function () {

    var url = "<?php echo url('daichong/delToken'); ?>";
            alert("操作后会清理完所有数据 注意这是危险操作！");
    $.get(url,function(data,status){
        if(data.code==1){
            layer.msg('清理成功！');
        }else{
            layer.msg('清理失败！');
        }

        // alert("数据: " + data + "\n状态: " + status);
    });
});
</script>
</body>
</html>
