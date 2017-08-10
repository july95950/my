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
class Gymnasium extends Base{

	protected $name = "Gymnasium";
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
                $kk=model('Gymnasium')->where('id='.$data['pid'])->column('keywords');
            }
            $result = $this->save($data);
            $yy=model('Gymnasium')->getLastInsID();
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

