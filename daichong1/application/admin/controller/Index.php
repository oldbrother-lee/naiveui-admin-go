<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use app\common\model\Balance;
use think\Db;
use Util\GoogleAuth;
use Util\Ispzw;
class Index extends Admin
{
	public function index()
	{
        // 今日订单
        $data['today_amount'] = M('porder')->where(array('pay_time' => array('egt',strtotime(date('Y-m-d'))),'status' => array('gt', 1), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
        $data['today_count'] = M('porder')->where(array('pay_time' => array('egt', strtotime(date('Y-m-d'))),'status' => array('gt', 1), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['today_yes_amount'] = M('porder')->where(array('pay_time' => array('egt', strtotime(date('Y-m-d'))),'status'=>4,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
        $data['today_yes_count'] = M('porder')->where(array('pay_time' => array('egt', strtotime(date('Y-m-d'))),'status'=>4,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['today_yes9_amount'] = M('porder')->where(array('pay_time' => array('egt', strtotime(date('Y-m-d'))),'status'=>9,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('charge_amount');
        $data['today_yes9_count'] = M('porder')->where(array('pay_time' => array('egt', strtotime(date('Y-m-d'))),'status'=>9,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['today_yes_amount'] += $data['today_yes9_amount'];
        $data['today_yes_count'] += $data['today_yes9_count'];
        $data['today_no_amount'] = M('porder')->where(array('pay_time' => array('egt', strtotime(date('Y-m-d'))),'status'=>6,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('refund_price');
        $data['today_no_count'] = M('porder')->where(array('pay_time' => array('egt', strtotime(date('Y-m-d'))),'status'=>6,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['today_in_amount'] = M('porder')->where(array('pay_time' => array('egt', strtotime(date('Y-m-d'))),'status' => array('in', '3,11'),'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
        $data['today_in_count'] = M('porder')->where(array('pay_time' => array('egt', strtotime(date('Y-m-d'))),'status' => array('in', '3,11'),'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();

        // 昨日订单
        $data['zuori_amount'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d')))),'status' => array('gt', 1), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
        $data['zuori_count'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d')))),'status' => array('gt', 1), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['zuori_yes_amount'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d')))),'status'=>4,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
        $data['zuori_yes_count'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d')))),'status'=>4,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['zuori_yes9_amount'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d')))),'status'=>9,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('charge_amount');
        $data['zuori_yes9_count'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d')))),'status'=>9,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['zuori_yes_amount'] += $data['today_yes9_amount'];
        $data['zuori_yes_count'] += $data['today_yes9_count'];
        $data['zuori_no_amount'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d')))),'status'=>6,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('refund_price');
        $data['zuori_no_count'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d')))),'status'=>6,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['zuori_in_amount'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d')))),'status' => array('in', '3,11'),'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
        $data['zuori_in_count'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d')))),'status' => array('in', '3,11'),'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();

        // 前日订单
        $data['qianri_amount'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d')) - 86400)),'status' => array('gt', 1), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
        $data['qianri_count'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d')) - 86400)),'status' => array('gt', 1), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['qianri_yes_amount'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d')) - 86400)),'status'=>4,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
        $data['qianri_yes_count'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d'))- 86400)),'status'=>4,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['qianri_yes9_amount'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d'))- 86400)),'status'=>9,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('charge_amount');
        $data['qianri_yes9_count'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d'))- 86400)),'status'=>9,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['qianri_yes_amount'] += $data['today_yes9_amount'];
        $data['qianri_yes_count'] += $data['today_yes9_count'];
        $data['qianri_no_amount'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d'))- 86400)),'status'=>6,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('refund_price');
        $data['qianri_no_count'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d'))- 86400)),'status'=>6,'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['qianri_in_amount'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d'))- 86400)),'status' => array('in', '3,11'),'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
        $data['qianri_in_count'] = M('porder')->where(array('pay_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d'))- 86400)),'status' => array('in', '3,11'),'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();

        // 总订单
		$data['total_price'] = M('porder')->where(array('status' => array('in', '2,3,4,5,8,9,10,11,12,13'), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
		$data['total_count'] = M('porder')->where(array('status' => array('gt', 1), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['total_yes_price'] = M('porder')->where(array('status' => array('in', '4,12,13'), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
        $data['total_yes_count'] = M('porder')->where(array('status' => array('in', '4,12,13'), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['total_no_price'] = M('porder')->where(array('status' => array('in', 6), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
        $data['total_no_count'] = M('porder')->where(array('status' => array('in', 6), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();
        $data['total_ing_price'] = M('porder')->where(array('status' => array('in', '3,11'), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');
		$data['total_ing_count'] = M('porder')->where(array('status' => array('in', '3,11'), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->count();

        // 代理总加款
        $jk = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 1))->sum('money');
        $kk = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 2))->sum('money');
        $data['jiakuan'] = sprintf("%.2f", $jk - $kk);
        $data['jiakuan_count'] = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 1))->count();
        // 今日代理加款
        $jk = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 1, 'create_time' => array('egt', strtotime(date('Y-m-d')))))->sum('money');
        $kk = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 2, 'create_time' => array('egt', strtotime(date('Y-m-d')))))->sum('money');
        $data['today_jiakuan'] = sprintf("%.2f", $jk - $kk);
        $data['today_jiakuan_count'] = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 1, 'create_time' => array('egt', strtotime(date('Y-m-d')))))->count();
        // 昨日代理加款
        $jkz = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 1, 'create_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d'))))))->sum('money');
        $kkz = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 2, 'create_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d'))))))->sum('money');
        $data['zuotian_jiakuan'] = sprintf("%.2f", $jkz - $kkz);
        $data['zuotian_jiakuan_count'] = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 1, 'create_time' => array('between', array(strtotime(date('Y-m-d')) - 86400, strtotime(date('Y-m-d'))))))->count();
        // 前日代理加款
        $jkz = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 1, 'create_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d'))- 86400))))->sum('money');
        $kkz = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 2, 'create_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d'))- 86400))))->sum('money');
        $data['qianri_jiakuan'] = sprintf("%.2f", $jkz - $kkz);
        $data['qianri_jiakuan_count'] = M('balance_log')->where(array('style' => Balance::STYLE_RECHARGE, 'type' => 1, 'create_time' => array('between', array(strtotime(date('Y-m-d')) - (86400*2), strtotime(date('Y-m-d'))- 86400))))->count();

        // 代理商与余额
        $data['agent_num'] = M('customer')->where(array('type' => 2, 'is_del' => 0))->count();
        $data['agent_balance'] = M('customer')->where(array('type' => 2, 'is_del' => 0))->sum('balance');
        $data['shouxin_e'] = M('customer')->where(array('is_del' => 0))->sum('shouxin_e');

        // 会员与余额
        $data['cus_num'] = M('customer')->where(array('type' => 1, 'is_del' => 0))->count();
        $data['cus_balance'] = M('customer')->where(array('type' => 1, 'is_del' => 0))->sum('balance');

        // 当前携号转网剩余条数
		$res = Ispzw::balance(C('ISP_ZHUANW_CFG.apikey'));
		$data['ispzw_balance'] = $res['errno'] == 0 ? $res['data'] : '未配置';
		$data['zwsw'] = C('ISP_ZHUANW_SW');
        $data['total_price_upgrade'] = M('order_upgrade')->where(array('is_pay' => 1))->sum('total_price');
        $data['total_price_wait'] = M('porder')->where(array('status' => array('in', '2,10'), 'is_del' => 0, 'is_apart' => array('in', array(0, 2))))->sum('total_price');

        $this->assign('data', $data);
        return view();
	}
	public function sysinfo()
	{
		$server_info = array('系统版本' => 'v' . 9.82, '操作系统' => PHP_OS, '运行环境' => $_SERVER["SERVER_SOFTWARE"], 'PHP版本' => PHP_VERSION, 'MYSQL版本' => Db::query('select version() as v')[0]['v'], '上传附件限制' => ini_get('upload_max_filesize'), '执行时间限制' => ini_get('max_execution_time') . '秒', '服务器时间' => date("Y年n月j日 H:i:s"), '授权域名/IP地址' => $_SERVER['SERVER_NAME'] . ' [ ' . gethostbyname($_SERVER['SERVER_NAME']) . ' ]', '当前登录用户IP地址' => get_client_ip(0), '脚本运行占用最大内存' => get_cfg_var("memory_limit") ?: "无", '磁盘剩余空间' => round(disk_free_space(".") / (1024 * 1024), 2) . 'M');
		$this->assign('server_info', $server_info);
		return view();
	}
	public function tongji()
	{
		return view();
	}
	public function statistics()
	{
		$list = M()->query('select sum(total_price) as price,FROM_UNIXTIME(create_time,\'%Y年%m月%d日\') as time from dyr_porder where status in(2,3,4) GROUP BY time order by time asc');
		return djson(0, 'ok', $list);
	}
	public function bind_google_auth()
	{
		if ($this->adminuser['google_auth_secret']) {
			$this->redirect('admin/index');
		}
		$name = C('WEB_SITE_TITLE') . "-" . $this->adminuser['nickname'];
		$secret = GoogleAuth::createSecret();
		$qrCodeUrl = GoogleAuth::getQRCodeGoogleUrl($name, $secret);
		$this->assign('qrcode_url', $qrCodeUrl);
		$this->assign('secret', $secret);
		return view();
	}
	public function save_google_auth()
	{
		if ($this->adminuser['google_auth_secret']) {
			$this->redirect('admin/index');
		}
		$secret = dyr_encrypt(I('secret'));
		$goret = GoogleAuth::verifyCode($secret, I('verifycode'), 1);
		if (!$goret) {
			return $this->error("谷歌身份验证码错误！");
		}
		M('Member')->where(array('id' => $this->adminuser['id']))->setField(array('google_auth_secret' => $secret));
		return $this->success("绑定成功！", U('admin/index'));
	}
	public function skip_google_auth()
	{
		if ($this->adminuser['google_auth_secret']) {
			$this->redirect('admin/index');
		}
		M('Member')->where(array('id' => $this->adminuser['id']))->setField(array('google_auth_secret' => '0'));
		return $this->success("已跳过绑定操作", U('admin/index'));
	}
	public function has_apbla()
	{
		$bb = M('apply_addbla')->where(array('status' => 1))->find();
		if ($bb) {
			return djson(0, '有待处理的打款申请单', $bb);
		} else {
			return djson(1, '无');
		}
	}

    public function has_wrong()
    {
        $bb = M('porder')->where(array('status' => 8))->field('id,order_number')->find();
        if ($bb) {
            return djson(0, '有待处理的异常订单', $bb);
        } else {
            return djson(1, '无');
        }
    }

	public function has_apply_refund()
	{
		$bb = M('porder')->where(array('apply_refund' => 1, 'status' => 3))->field('id,order_number')->find();
		if ($bb) {
			return djson(0, '有待处理的退款申请单', $bb);
		} else {
			return djson(1, '无');
		}
	}
}