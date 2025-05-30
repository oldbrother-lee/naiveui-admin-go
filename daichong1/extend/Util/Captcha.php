<?php

//decode by http://chiran.taobao.com/
namespace Util;

class Captcha
{
	private $width;
	private $height;
	private $codeNum;
	private $code;
	private $im;
	function __construct($width = 80, $height = 20, $codeNum = 4)
	{
		$this->width = $width;
		$this->height = $height;
		$this->codeNum = $codeNum;
	}
	function showImg()
	{
		$this->createImg();
		$this->setDisturb();
		$this->setCaptcha();
		$this->outputImg();
	}
	function getCaptcha()
	{
		return $this->code;
	}
	private function createImg()
	{
		$this->im = imagecreatetruecolor($this->width, $this->height);
		$bgColor = imagecolorallocate($this->im, 255, 255, 255);
		imagefill($this->im, 0, 0, $bgColor);
	}
	private function setDisturb()
	{
		$area = $this->width * $this->height / 20;
		$disturbNum = $area > 250 ? 250 : $area;
		for ($i = 0; $i < $disturbNum; $i++) {
			$color = imagecolorallocate($this->im, rand(0, 255), rand(0, 255), rand(0, 255));
			imagesetpixel($this->im, rand(1, $this->width - 2), rand(1, $this->height - 2), $color);
		}
		for ($i = 0; $i <= 1; $i++) {
			$color = imagecolorallocate($this->im, rand(128, 255), rand(125, 255), rand(100, 255));
			imagearc($this->im, rand(0, $this->width), rand(0, $this->height), rand(30, 300), rand(20, 200), 50, 30, $color);
		}
	}
	private function createCode()
	{
		$str = "23456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ";
		for ($i = 0; $i < $this->codeNum; $i++) {
			$this->code .= $str[rand(0, strlen($str) - 1)];
		}
	}
	private function setCaptcha()
	{
		$this->createCode();
		$color = imagecolorallocate($this->im, rand(50, 250), rand(100, 250), rand(128, 250));
		imagefttext($this->im, 28, 0, 10, 30, $color, 'public/public/fonts/monofont.ttf', $this->code);
	}
	private function outputImg()
	{
		if (imagetypes() & IMG_JPG) {
			header('Content-type:image/jpeg');
			imagejpeg($this->im);
		} elseif (imagetypes() & IMG_GIF) {
			header('Content-type: image/gif');
			imagegif($this->im);
		} else {
			if (imagetype() & IMG_PNG) {
				header('Content-type: image/png');
				imagepng($this->im);
			} else {
				die("Don't support image type!");
			}
		}
	}
}