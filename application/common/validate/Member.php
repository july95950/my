<?php
// +----------------------------------------------------------------------
// | JunYuCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.213idc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tian <tian@213idc.cn> <http://www.213idc.cn>
// +----------------------------------------------------------------------

namespace app\common\validate;

/**
* 设置模型
*/
class Member extends Base{

	protected $rule = array(
		'username'   => 'require|unique:member',
		'email'      => 'require|unique:member|email',
		'mobile'     => 'require|unique:member|/^1[34578]{1}\d{9}$/',
		'password'   => 'require',
		'repassword' => 'confirm:password'
	);
	protected $message = array(
		'username.require'    => '用户名必须',
		'username.unique'    => '用户名已存在',
		'email.require'    => '邮箱必须',
		'email.unique'    => '邮箱已存在',
		'mobile.unique'    => '手机号已存在',
		'password.require' => '密码必须',
		'repassword.require'    => '确认密码和密码必须一致',
	);
	protected $scene = array(
		'edit'     => 'email,mobile',
		'password' => 'password,repassword'
	);

}