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
class Lease extends Base{

	protected $name = "Lease";
	protected $auto = array('update_time', 'status'=>1);

//	protected $type = array(
//		'icon'  => 'integer',
//	);
    public function change(){

        $data['name'] = input('post.name');
        $data['spec'] = input('post.spec');
        $data['operate_id'] = session('user_auth.uid');
        $data['create_time'] = time();
        $data['price'] = input('post.price');
        $data['margin'] = input('post.margin');
        $result = $this->save($data);

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

