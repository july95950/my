<?php
// +----------------------------------------------------------------------
// | JunYuCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.213idc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tian <tian@213idc.cn> <http://www.213idc.cn>
// +----------------------------------------------------------------------

namespace app\index\controller;
use app\common\controller\Fornt;

class Index extends Fornt {

	//网站首页
	public function index() {
		//设置SEO
		$this->setSeo(config('web_site_title'), config('web_site_keyword'), config('web_site_description'));
		return $this->fetch();
	}
}
