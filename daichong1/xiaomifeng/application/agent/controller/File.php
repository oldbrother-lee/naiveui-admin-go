<?php

//decode by http://chiran.taobao.com/
namespace app\agent\controller;

class File extends Admin
{
	public function upload()
	{
		return djson(1, "暂时停用所有上传接口，请联系管理员上传");
		$file = request()->file('file');
		if (empty($file)) {
			return $this->error('请选择上传文件');
		}
		$info = $file->validate(array('size' => C('DOWNLOAD_UPLOAD.maxSize'), 'ext' => C('DOWNLOAD_UPLOAD.exts')))->move(C('DOWNLOAD_UPLOAD.movePath'));
		if ($info) {
			return djson(0, '上传成功', C('DOWNLOAD_UPLOAD.rootPath') . $info->getSaveName());
		} else {
			return djson(1, $file->getError());
		}
	}
	public function upload_txt()
	{
		return djson(1, "暂时停用所有上传接口，请联系管理员上传");
		$file = request()->file('file');
		if (empty($file)) {
			return $this->error('请选择上传文件');
		}
		$info = $file->validate(array('size' => C('DOWNLOAD_UPLOAD_TXT.maxSize'), 'ext' => C('DOWNLOAD_UPLOAD_TXT.exts')))->move(C('DOWNLOAD_UPLOAD_TXT.movePath'), '');
		if ($info) {
			return djson(0, '上传成功', C('DOWNLOAD_UPLOAD_TXT.rootPath') . $info->getSaveName());
		} else {
			return djson(1, $file->getError());
		}
	}
}