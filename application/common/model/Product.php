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
class Product extends Base{

	protected $name = "Product";
	protected $auto = array('update_time', 'icon'=>1, 'status'=>1);

	protected $type = array(
		'icon'  => 'integer',
	);
	//商品入库
	public function addproduct($areas){
		$data = input('post.');
		//header("Content-type: text/html; charset=utf-8");
		$data['area_id'] = $areas;
		if($area = 'wy'){
			$data['out_price'] = input('post.in_price');
		}
		$data['uid'] = session('user_auth.uid');
		if(input('post.in_time') == '' && input('post.in_time') == null){
			$data['in_time'] = time();
		}else{
			$data['in_time'] = strtotime(input('post.in_time'));
		}
		//$clss1 = db('classify')->where(array('id'=>input('post.p_Classify')))->find();
		$clss2 = db('ClassifyModel2')->where(array('id'=>input('post.classify_2')))->find();
		$data['class2'] = $clss2['sid'];
		//var_dump($clss2);die;
		$info = $this->where(array('title'=>$data['title'],'area_id'=>$areas))->find();

		if($info){
			unset($data['id']);
			$data['num'] = $data['num']+$info['num'];
			//var_dump($data);die;
			$result = $this->where(array('title'=>$data['title']))->update($data);

		}else{

			$result = $this->save($data);
		}

		if (false !== $result) {

			return true;
		}else{

			$this->error = "失败！";
			return false;
		}
	}
	//商品出库
	public function outproduct($title,$num){
		$result = $this->where(array('title'=>$title))->setDec('num',$num);
		return $result;
	}
	public function info($id, $field = true){
		return $this->db()->where(array('id'=>$id))->field($field)->find();
	}
}