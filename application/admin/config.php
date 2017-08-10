<?php
// +----------------------------------------------------------------------
// | JunYuCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.213idc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tian <tian@213idc.cn> <http://www.213idc.cn>
// +----------------------------------------------------------------------

return array(

	'user_administrator' => 1,

	//'url_common_param'=>true,

	'template'           => array(

	),

	'view_replace_str'   => array(
		'__ADDONS__' => BASE_PATH . '/addons',
		'__PUBLIC__' => BASE_PATH . '/public',
		'__STATIC__' => BASE_PATH . '/application/admin/static',
		'__IMG__'    => BASE_PATH . '/application/admin/static/images',
		'__CSS__'    => BASE_PATH . '/application/admin/static/css',
		'__JS__'     => BASE_PATH . '/application/admin/static/js',
        '__HOME__'     => BASE_PATH .'/admin'
	),

	'session'            => array(
		'prefix'     => 'admin',
		'type'       => '',
		'auto_start' => true,
	),
);