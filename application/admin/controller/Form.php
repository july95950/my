<?php
// +----------------------------------------------------------------------
// | JunYuCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.213idc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tian <tian@213idc.cn> <http://www.213idc.cn>
// +----------------------------------------------------------------------

namespace app\admin\controller;
use app\common\controller\Admin;

class Form extends Admin {

	//自定义表单
	public function index(){
		$this->assign('meta_title','自定义表单');
		return $this->fetch();
	}
}