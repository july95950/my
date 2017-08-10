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
* 设置模型
*/
class Qlxmanagement extends Base{

	protected $name = "Qlxmanagement";
	protected $auto = array('update_time', 'status'=>1);

//	protected $type = array(
//		'icon'  => 'integer',
//	);

	public function change(){
		$data = input('post.');
		if ($data['id']) {
			$result = $this->save($data,array('id'=>$data['id']));
		}else{
			unset($data['id']);
            $kk = '';
            if($data['pid'] != 0 && $data['pid'] !=''){
                $kk=model('Qlxmanagement')->where('id='.$data['pid'])->column('keywords');
            }
			$result = $this->save($data);
            $yy=model('Qlxmanagement')->getLastInsID();
            if($kk != ''){
                $key=$kk[0]."|".$yy;
            }else{
                $key=$yy;
            }
            //var_dump($key);die;
            $this->save(['keywords'=>$key],['id'=>$yy]);
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