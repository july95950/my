<?php
// +----------------------------------------------------------------------
// | JunYuCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.213idc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tian <tian@213idc.cn> <http://www.213idc.cn>
// +----------------------------------------------------------------------

namespace app\common\controller;

class Api {

	protected $data;

	public function __construct() {
		$this->data = array('code' => 0, 'msg' => '', 'time' => time(), 'data' => '');
	}
}