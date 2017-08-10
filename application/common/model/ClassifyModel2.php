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
class ClassifyModel2 extends Base{

	protected $name = "ClassifyModel2";
	protected $auto = array('update_time', 'icon'=>1, 'status'=>1);

	protected $type = array(
		'icon'  => 'integer',
	);

	public function changes($area_id,$classify1_id,$sid){
		$data['area_id'] = $area_id;
		$data['classify1_id'] = $classify1_id;
		$data['sid'] = $sid;
		$data['time'] = time();
		$result = $this->insert($data);
		if(false !== $result){
			return true;
		}else{
			return false;
		}
		
	}

	public function changeSta($sid,$area_id){
		$data['status'] = 1;
        $this->where(array('area_id'=>$area_id))->update($data);
    }

	public function info($id, $field = true){
		return $this->db()->where(array('id'=>$id))->field($field)->find();
	}
}