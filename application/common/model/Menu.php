<?php
// +----------------------------------------------------------------------
// | JunYuCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.213idc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tian <tian@213idc.cn> <http://www.213idc.cn>
// +----------------------------------------------------------------------

namespace app\common\model;

/**
 * 菜单模型类
 * @author tian <tian@213idc.cn>
 */
class Menu extends \app\common\model\Base {

	protected $type = array(
		'id'  => 'integer',
	);

	public function getAuthNodes($type = 'admin'){
		$map['type'] = $type;
		$rows = $this->db()->field('id,pid,group,title,url')->where($map)->order('id asc')->select();
		foreach ($rows as $key => $value) {
			$data[$value['id']] = $value;
		}
		foreach ($data as $key => $value) {
			if ($value['pid'] > 0) {
				$value['group'] = $data[$value['pid']]['title'] . '管理';
				$list[] = $value;
			}
		}
		return $list;
	}
}