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
class Classify extends Base{

	protected $name = "Classify";
	protected $auto = array('update_time', 'icon'=>1, 'status'=>1);

	protected $type = array(
		'icon'  => 'integer',
	);

	public function change(){
		$data['id'] = input('post.id');
		$data['title'] = input('post.title');
		$data['business_id'] = 'wy'.time();
		$data['create_person'] = session('user_auth.uid');

		if(input('post.create_time') == '' && input('post.create_time') == null){
			$data['create_time'] = time();
		}else{
			$data['create_time'] = strtotime(input('post.create_time'));
		}
		if ($data['id']) {
			$result = $this->save($data,array('id'=>$data['id']));
		}else{
			unset($data['id']);
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