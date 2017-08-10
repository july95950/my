<?php
// +----------------------------------------------------------------------
// | JunYuCMS 
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.213idc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: info <info@213idc.com> <http://www.213idc.cn>
// +----------------------------------------------------------------------

namespace app\common\model;

/**
* 设置模型
*/
class InStorageSetail extends Base{

	protected $name = "InStorageSetail";
	protected $auto = array('update_time', 'icon'=>1, 'status'=>1);

	protected $type = array(
		'icon'  => 'integer',
	);

	public function changess($areas){

		$data['id'] = input('post.id');
		$data['title'] = input('post.title');
		$data['area_id'] = $areas;
		$data['uid'] = session('user_auth.uid');
		$data['num'] = input('post.num');
		$data['in_time'] = input('post.in_time');
		$data['spec'] = input('post.spec');
		$data['in_price'] = input('post.in_price');
		if($areas == 'wy'){
			$data['out_price'] = input('post.in_price');
		}else{
			$data['out_price'] = input('post.out_price');
		}
		
		if(input('post.in_time') == '' && input('post.in_time') == null){
			$data['in_time'] = time();
		}else{
			$data['in_time'] = strtotime(input('post.in_time'));
		}
		if ($data['id']) {

			$result = $this->save($data,array('id'=>$data['id']));

		}else{

			unset($data['id']);
			//$data['type'] = '新增';
			$result = $this->save($data);
		}
		if (false !== $result) {

			return true;
		}else{

			$this->error = "失败！";
			return false;
		}
	}

	public function info($id, $field = true){
		return $this->db()->where(array('id'=>$id))->field($field)->find();
	}
}