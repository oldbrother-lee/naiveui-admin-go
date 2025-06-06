<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use app\admin\model\Archives;
use think\Exception;
class Article extends Admin
{
	public function _init()
	{
		if (!IS_CLI && (!function_exists('get_shoquan_key') || !S(md5(get_shoquan_key())))) {
			echo C('sqyc_msg');
			exit;
		}
	}
	public function index()
	{
		$list = M('arctype')->order('sort,id')->select();
		$arc = $this->getTrees($list, 0);
		$archtml = $this->proc_menu_Html($arc);
		$this->assign('arctype', $archtml);
		return view();
	}
	public function add($id = 0)
	{
		if (request()->isPost()) {
			$arr = I('post.');
			if (I('post.id')) {
				$data = M('arctype')->update($arr);
				if ($data) {
					return $this->success('更新成功', U('index'));
				} else {
					return $this->error('更新失败');
				}
			} else {
				$data = M('arctype')->insertGetId($arr);
				if ($data) {
					return $this->success('新增成功', U('index'));
				} else {
					return $this->error('新增失败');
				}
			}
		} else {
			$info = M('arctype')->field(true)->find($id);
			$arctypes = M('arctype')->where(array('topid' => 0))->field(true)->order('sort asc')->select();
			$arctypes = array_merge(array(0 => array('id' => 0, 'typename' => '顶级菜单')), $arctypes);
			if (false === $info) {
				return $this->error('获取栏目信息错误');
			}
			$this->assign('info', $info);
			$this->assign('arctypes', $arctypes);
			$this->assign('channel', M('channeltype')->order('id asc')->select());
			$this->meta_title = '新增栏目';
		}
		return view();
	}
	public function deltype($id)
	{
		$chid = M('arctype')->where(array('topid' => $id))->find();
		if ($chid) {
			return $this->error('请先删除子级栏目！');
		}
		$arc1 = M('archives')->where(array('typeid' => $id))->find();
		$arc2 = M('archives')->where(array('typeid2' => $id))->find();
		if ($arc1 || $arc2) {
			return $this->error('请先删除栏目下的文档！');
		}
		if (M('arctype')->where(array('id' => $id))->delete()) {
			return $this->success('删除成功');
		} else {
			return $this->error('删除失败！');
		}
	}
	public function conlist()
	{
		$map = array();
		if (I('typeid')) {
			$map['a.typeid'] = I('typeid');
		}
		if (I('typeid2')) {
			$map['a.typeid2'] = I('typeid2');
		}
		if (I('channel')) {
			$map['a.channel'] = I('channel');
		}
		if (I('key')) {
			$map['a.title|a.keywords'] = array('like', "%" . I('key') . "%");
		}
		$list = M('archives a')->join("dyr_arctype c", "c.id=a.typeid", 'LEFT')->join("dyr_channeltype h", "h.id=a.channel", 'LEFT')->where($map)->field("a.*,c.typename,h.typename as channelname,(select typename from dyr_arctype where id=a.typeid2) as typename2")->order("a.pubdate desc")->select();
		$this->assign('_list', $list);
		return view();
	}
	public function conedit()
	{
		if (request()->isPost()) {
			$channel = M('channeltype')->where(array('id' => I('channel')))->find();
			$fieldset = json_decode($channel['fieldset'], true);
			if (I('id') && I('aid')) {
				$archivarr = I('post.');
				$archivarr['lastpost'] = time();
				D('archives')->allowField(true)->save($archivarr, array('id' => I('id')));
				$addtableclo = M()->query("select column_name from information_schema.columns where table_name='" . $channel['addtable'] . "'");
				$addtbarr = array();
				foreach ($addtableclo as $key => $vo) {
					if ($vo['column_name'] != 'aid' && $vo['column_name'] != 'typeid') {
						if (array_key_exists($vo['column_name'], $archivarr)) {
							if ($fieldset[$vo['column_name']] == 'html') {
								$bl = $_POST[$vo['column_name']];
							} else {
								$bl = I($vo['column_name']);
							}
							if (isset($bl)) {
								$addtbarr[$vo['column_name']] = $bl;
							}
						} else {
							$addtbarr[$vo['column_name']] = '';
						}
					}
				}
				M()->table($channel['addtable'])->where(array('aid' => I('aid')))->update($addtbarr);
				return $this->success('保存成功！');
			} else {
				$archivesarr = I('post.');
				$archivesarr['pubdate'] = time();
				$archivesarr['lastpost'] = time();
				$archivesarr['mid'] = UID;
				$archivesmodel = new Archives($archivesarr);
				$archivesmodel->allowField(true)->save();
				$aid = $archivesmodel->id;
				$addtableclo = M()->query("select column_name from information_schema.columns where table_name='" . $channel['addtable'] . "'");
				$addtbarr = array('aid' => $aid, 'typeid' => I('typeid'));
				foreach ($addtableclo as $key => $vo) {
					if ($vo['column_name'] != 'aid' && $vo['column_name'] != 'typeid') {
						if (array_key_exists($vo['column_name'], $archivesarr)) {
							if ($fieldset[$vo['column_name']] == 'html') {
								$bl = $_POST[$vo['column_name']];
							} else {
								$bl = I($vo['column_name']);
							}
							if (isset($bl)) {
								$addtbarr[$vo['column_name']] = $bl;
							}
						} else {
							$addtbarr[$vo['column_name']] = '';
						}
					}
				}
				M()->table($channel['addtable'])->insertGetId($addtbarr);
				return $this->success('添加成功！');
			}
		} else {
			if (I('id')) {
				$info = M('archives a')->join("dyr_channeltype h", "h.id=a.channel", 'LEFT')->where(array('a.id' => I('id')))->field("a.*,h.addtable,h.fieldset")->find();
				$infoz = M()->query("select * from " . $info['addtable'] . " where aid=" . $info['id']);
				$addtable = M()->query("select table_name,column_comment,column_name from information_schema.columns where table_name='" . $info['addtable'] . "'");
				$fieldset = $info['fieldset'];
				$this->assign('channel', $info['channel']);
				$this->assign('typeid', $info['typeid']);
				$this->assign('typeid2', $info['typeid2']);
			} else {
				$info = null;
				$arctype = M('arctype')->where(array('id' => I('typeid2') ?: I('typeid')))->find();
				$channel = M('channeltype')->where(array('id' => $arctype['channeltype']))->find();
				$infoz = null;
				$addtable = M()->query("select table_name,column_comment,column_name from information_schema.columns where table_name='" . $channel['addtable'] . "'");
				$fieldset = $channel['fieldset'];
				$this->assign('channel', $arctype['channeltype']);
				$this->assign('typeid', I('typeid'));
				$this->assign('typeid2', I('typeid2'));
			}
			$fieldset = json_decode($fieldset, true);
			$this->assign('fieldset', $fieldset);
			$this->assign('info', $info);
			$this->assign('infoz', $infoz[0]);
			$this->assign('addtable', $addtable);
			return view();
		}
	}
	public function delcon($id)
	{
		$info = M('archives a')->join("dyr_channeltype h", "h.id=a.channel", 'LEFT')->where(array('a.id' => $id))->field("a.id,h.addtable")->find();
		M()->table($info['addtable'])->where(array('aid' => $info['id']))->delete();
		if (M('archives')->where(array('id' => $info['id']))->delete()) {
			return $this->success('删除成功！');
		} else {
			return $this->error('删除失败！');
		}
	}
	public function channel()
	{
		$channel = M('channeltype')->order('id asc')->select();
		foreach ($channel as $key => $vo) {
			$channel[$key]['doccount'] = M('archives')->where(array('channel' => $vo['id']))->count();
		}
		$this->assign('_list', $channel);
		return view();
	}
	public function editchannel()
	{
		$info = M('channeltype')->where(array('id' => I('id')))->find();
		$maintable = M()->query("select table_name,column_comment,column_name,is_nullable,data_type,column_type from information_schema.columns where table_name='" . $info['maintable'] . "'");
		$addtable = M()->query("select table_name,column_comment,column_name,is_nullable,data_type,column_type from information_schema.columns where table_name='" . $info['addtable'] . "'");
		$this->assign('info', $info);
		$this->assign('maintable', $maintable);
		$this->assign('addtable', $addtable);
		return view();
	}
	public function addchannel()
	{
		if (request()->isPost()) {
			if (!I('nid') || !I('typename') || !I('addtable')) {
				return $this->error('参数未填写');
			}
			$istb = M()->query("select count(*) as cc from information_schema.columns where table_name='dyr_addon" . I('addtable') . "'");
			if ($istb[0]['cc'] != 0) {
				return $this->error('内容表名已经存在了，请重新填写');
			}
			try {
				$cid = M('channeltype')->insertGetId(array('nid' => I('nid'), 'typename' => I('typename'), 'isshow' => I('isshow'), 'addtable' => 'dyr_addon' . I('addtable'), 'issystem' => 0, 'fieldset' => '[]'));
			} catch (Exception $e) {
				return $this->error('添加模型失败！' . $e->getMessage());
			}
			$creatsql = "CREATE TABLE `dyr_addon" . I('addtable') . "` (\r\n  `aid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '主键',\r\n  `typeid` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '栏目',\r\n  PRIMARY KEY (`aid`),\r\n  UNIQUE KEY `aid` (`aid`) USING BTREE,\r\n  KEY `typeid` (`typeid`) USING BTREE\r\n) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			try {
				M()->query($creatsql);
				if ($cid) {
					return $this->success('添加成功,即将进行下一步！', U('addchannelclo', array('id' => $cid)));
				} else {
					return $this->error('添加数据表失败！');
				}
			} catch (Exception $e) {
				M('channeltype')->where(array('id' => $cid))->delete();
				return $this->error('创建数据表出错！' . $e->getMessage());
			}
		} else {
			return view();
		}
	}
	public function addchannelclo()
	{
		$info = M('channeltype')->where(array('id' => I('id')))->find();
		$maintable = M()->query("select table_name,column_comment,column_name,is_nullable,data_type,column_type from information_schema.columns where table_name='" . $info['maintable'] . "'");
		$addtable = M()->query("select table_name,column_comment,column_name,is_nullable,data_type,column_type from information_schema.columns where table_name='" . $info['addtable'] . "'");
		$fieldset = json_decode($info['fieldset'], true);
		$this->assign('fieldset', $fieldset);
		$this->assign('info', $info);
		$this->assign('maintable', $maintable);
		$this->assign('addtable', $addtable);
		$this->assign('columntype', array('int(10)', 'varchar(20)', 'varchar(50)', 'varchar(200)', 'text', 'tinyint(4)', 'double(10,2)'));
		$this->assign('columninput', array('input' => '单行文本', 'text' => '多行文本', 'number' => '数字', 'file' => '附件', 'img' => '图片', 'html' => '富文本'));
		return view();
	}
	public function addaddoncloumn()
	{
		if (!I('column_name') || !I('column_type') || !I('column_comment') || !I('columninput')) {
			return djson(1, "参数错误！");
		}
		$info = M('channeltype')->where(array('id' => I('id')))->find();
		$sql = "alter table " . $info['addtable'] . " add " . I('column_name') . " " . I('column_type') . " " . (I('is_nullable') == 1 ? '' : 'NOT NULL') . " COMMENT '" . I('column_comment') . "'";
		try {
			M()->query($sql);
			$fieldset = json_decode($info['fieldset'], true);
			$fieldset[I('column_name')] = I('columninput');
			M('channeltype')->where(array('id' => I('id')))->setField(array('fieldset' => json_encode($fieldset)));
			return djson(0, "添加成功！");
		} catch (Exception $e) {
			return djson(1, $e->getMessage());
		}
	}
	public function deladdoncloumn()
	{
		$info = M('channeltype')->where(array('id' => I('id')))->find();
		$sql = "alter table " . $info['addtable'] . " drop column `" . I('column_name') . "`";
		try {
			M()->query($sql);
			$fieldset = json_decode($info['fieldset'], true);
			unset($fieldset[I('column_name')]);
			M('channeltype')->where(array('id' => I('id')))->setField(array('fieldset' => json_encode($fieldset)));
			return $this->success('删除成功！');
		} catch (Exception $e) {
			return $this->error('删除出错,' . $e->getMessage() . $sql);
		}
	}
	public function channelsave()
	{
		if (I('id') && request()->isPost()) {
			M('channeltype')->where(array('id' => I('id')))->update(array('nid' => I('nid'), 'typename' => I('typename'), 'isshow' => I('isshow'), 'list_template' => I('list_template'), 'article_template' => I('article_template')));
			return $this->success('保存成功！');
		} else {
			return $this->error('出错');
		}
	}
	public function delchannel()
	{
		$arc = M('archives')->where(array('channel' => I('id')))->find();
		if ($arc) {
			return $this->error('该模型下还有文档，请先删除文档！');
		}
		$arctype = M('arctype')->where(array('channeltype' => I('id')))->find();
		if ($arctype) {
			return $this->error('还有栏目使用该模型，请先删除或者修改栏目！');
		}
		$info = M('channeltype')->where(array('id' => I('id')))->find();
		if (M('channeltype')->where(array('id' => I('id')))->delete()) {
			M()->query("DROP TABLE " . $info['addtable']);
			return $this->success('删除成功！');
		} else {
			return $this->error('出错');
		}
	}
	private function getTrees($data, $pid)
	{
		$tree = array();
		foreach ($data as $k => $v) {
			if ($v['topid'] == $pid) {
				$v['child'] = $this->getTrees($data, $v['id']);
				$tree[] = $v;
			}
		}
		return $tree;
	}
	private function proc_menu_Html($tree, $lv = 0)
	{
		$html = '';
		foreach ($tree as $t) {
			$edit = 'add?id=' . $t['id'];
			$del = 'deltype?id=' . $t['id'];
			$addnew = 'add?topid=' . $t['id'];
			$adddoc = 'conedit?typeid=' . ($t['topid'] ?: $t['id']) . '&typeid2=' . ($t['topid'] ? $t['id'] : 0);
			$content = 'conlist?typeid=' . ($t['topid'] ?: $t['id']) . '&typeid2=' . ($t['topid'] ? $t['id'] : 0);
			if ($t['child']) {
				$html .= "<li class=\"list-group-item node-treeview1\" >";
				$m_html = '';
				for ($m = 0; $m < $lv; $m++) {
					$m_html .= " <span class=\"indent\"></span>";
				}
				$html .= $m_html;
				$html .= " <span class=\"icon\"><i class=\"click-collapse glyphicon glyphicon-plus\"></i></span>";
				$html .= "<span  class=\"icon\"><i class=\"glyphicon glyphicon-stop\"></i></span><a  class=\"open-window no-refresh\" title='" . $t['typename'] . "' href=\"" . url($content) . "\">" . $t['typename'] . "</a>" . "&nbsp;&nbsp;&nbsp;<span style='font-size: 12px;'>[" . $this->channeltypetext($t['channeltype']) . "]</span>" . "&nbsp;&nbsp;&nbsp;<span style='font-size: 12px;'>[" . ($t['isshow'] == 1 ? "显示" : "隐藏") . "]</span>" . "&nbsp;&nbsp;&nbsp;<span  style='font-size: 12px;'>[" . $this->archives_count($t['id']) . "文档]</span>";
				$html .= "<span  class=\"badge badge-warning\">" . $t['sort'] . "</span>";
				$html .= "<a class='open-window' title='添加下级' href=\"" . url($addnew) . "\"><span class=\"text-navy\" style=\"float: right\">添加栏目&nbsp;</span></a>" . "<a class='open-window no-refresh' title='编辑' href=\"" . url($edit) . "\"><span class=\"text-navy\" style=\"float: right\">编辑&nbsp;</span></a>" . "<a class='open-window no-refresh' title='添加文档' href=\"" . url($adddoc) . "\"><span class=\"text-navy\" style=\"float: right\">添加文档&nbsp;</span></a>" . " </li>" . "<ul class=\"list-group\" style='display: none;'>";
				$html .= $this->proc_menu_Html($t['child'], $lv + 1);
				$html = $html . "</ul>";
			} else {
				$html .= "<li class=\"list-group-item node-treeview1\">";
				$m_html = '';
				for ($m = 0; $m < $lv; $m++) {
					$m_html .= " <span class=\"indent\"></span>";
				}
				$html .= $m_html;
				$html .= " <span style=\"width:20px;display:inline-block\"></span>";
				$html .= "<span  class=\"icon\"><i class=\"glyphicon glyphicon-stop\"></i></span><a  class=\"open-window no-refresh\" title='" . $t['typename'] . "' href=\"" . url($content) . "\">" . $t['typename'] . "</a>" . "&nbsp;&nbsp;&nbsp;<span style='font-size: 12px;'>[" . $this->channeltypetext($t['channeltype']) . "]</span>" . "&nbsp;&nbsp;&nbsp;<span style='font-size: 12px;'>[" . ($t['isshow'] == 1 ? "显示" : "隐藏") . "]</span>" . "&nbsp;&nbsp;&nbsp;<span  style='font-size: 12px;'>[" . $this->archives_count($t['id']) . "文档]</span>";
				$html .= "<span  class=\"badge badge-warning\">" . $t['sort'] . "</span>";
				$html .= "<a class=\"ajax-get confirm\" url=\"" . url($del) . "\"><span style=\"float: right;margin-right: 10px;color: #b31a2f;\">删除</span></a>";
				if ($t['topid'] == 0) {
					$html .= "<a class='open-window' title='添加下级' href=\"" . url($addnew) . "\"><span class=\"text-navy\" style=\"float: right\">添加栏目&nbsp;</span></a>";
				}
				$html .= "<a class='open-window no-refresh' title='编辑' href=\"" . url($edit) . "\"><span class=\"text-navy\" style=\"float: right\">编辑&nbsp;</span></a>" . "<a class='open-window no-refresh' title='添加文档' href=\"" . url($adddoc) . "\"><span class=\"text-navy\" style=\"float: right\">添加文档&nbsp;</span></a>" . " </li>";
			}
		}
		return $html;
	}
	private function channeltypetext($id)
	{
		$channel = M('channeltype')->where(array('id' => $id))->find();
		return $channel['typename'];
	}
	private $formatTree;
	public function toFormatTree($list, $title = 'typename', $pk = 'id', $pid = 'topid', $root = 0)
	{
		$list = list_to_tree($list, $pk, $pid, '_child', $root);
		$this->formatTree = array();
		$this->_toFormatTree($list, 0, $title);
		return $this->formatTree;
	}
	private function _toFormatTree($list, $level = 0, $title = 'typename')
	{
		foreach ($list as $key => $val) {
			$tmp_str = str_repeat("&nbsp;", $level * 2);
			$tmp_str .= "└";
			$val['level'] = $level;
			$val['title_show'] = $level == 0 ? $val[$title] . "&nbsp;" : $tmp_str . $val[$title] . "&nbsp;";
			if (!array_key_exists('_child', $val)) {
				array_push($this->formatTree, $val);
			} else {
				$tmp_ary = $val['_child'];
				unset($val['_child']);
				array_push($this->formatTree, $val);
				$this->_toFormatTree($tmp_ary, $level + 1, $title);
			}
		}
		return;
	}
	private function archives_count($id)
	{
		$map['typeid|typeid2'] = $id;
		$arccount = M('archives')->where($map)->count();
		return $arccount;
	}
}