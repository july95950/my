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
class OutStorageDetail extends Base{

	protected $name = "OutStorageDetail";
	protected $auto = array('update_time', 'icon'=>1, 'status'=>1);

	protected $type = array(
		'icon'  => 'integer',
	);

	public function changess($title,$num,$spec,$person,$time){

		$data['title'] = $title;
		$data['num'] = $num;
		$data['spec'] = $spec;
		$data['out_person'] = $person;
		
		if($time == '' && $time == null){
			$data['out_time'] = time();
		}else{
			$data['out_time'] = strtotime($time);
		}
		$result = $this->add($data);
		return $result;
	}

	public function info($id, $field = true){
		return $this->db()->where(array('id'=>$id))->field($field)->find();
	}
}