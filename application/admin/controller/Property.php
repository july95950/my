<?php
// +----------------------------------------------------------------------
// | JunYuCMS 
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.213idc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: info <info@213idc.com> <http://www.213idc.cn>
// +----------------------------------------------------------------------
// | 各模块选择自己需要的一二级分类
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\common\controller\Admin;

class Property extends Admin {

	protected $model;
	protected $rule;

	public function _initialize() {
		parent::_initialize();
		$this->group = model('AuthGroup');
		$this->rule  = model('AuthRule');
	}

	//一级分类添加
	public function index($type = 'admin') {
		//$map['module'] = $type;
		if(IS_POST){
			if(empty($_POST['id'])){
				return $this->error('请选择分类！', url('index'));
			}
			$ClassifyId  = $_POST['id'];
			$ClassifyModel1 = model('ClassifyModel1');
			$id = $_POST['id'];
			$info = db('ClassifyModel1')->where(array('area_id'=>'wy'))->select();
			$pids = array();
			for($k=0;$k<count($info);$k++){
				array_push($pids,$info[$k]['pid']);
			}
			$area_id = 'wy';
			$status = 1;
			if($info){
				$pid = array();
				//循环改变之前表中存在的一级菜单状态
				for($i=0;$i<count($info);$i++){		
					$dels = $ClassifyModel1->changeSta($info[$i]['pid'],$area_id);	
				}
				//循环插入本次新选择则的一级菜单
				foreach ($id as $key => $value) {
					$result = $ClassifyModel1->changes($area_id,$value);
				}
			}else{	
				for($j=0;$j<count($id);$j++){
					$result = $ClassifyModel1->changes($area_id,$id[$j]);
				}
			}
			if (false !== $result) {
				return $this->success('编辑成功！', url('index'));
			} else {
				$error = $Classify->getError();
				return $this->error(empty($error) ? '未知错误！' : $error);
			}
		}else{
			$list = db('classify')->select();
			$info = db('ClassifyModel1')->where(array('area_id'=>'wy','status'=>0))->select();
			$classify1 = array();
			for($k=0;$k<count($info);$k++){
				array_push($classify1,$info[$k]['pid']);
			}
			$this->assign('list',$list);
			$this->assign('classify1',$classify1);
			return $this->fetch();
		}
	
	}

	//二级分类添加
	public function indexs($type = 'admin') {
		if(IS_POST){
			$ClassifyModel2 = model('ClassifyModel2');
			if(empty($_POST['sid'])){
				return $this->error('请选择分类！', url('index'));
			}
			$sid = $_POST['sid'];			
			for($i=0;$i<count($sid);$i++){
				$sid[$i]=explode('&',$sid[$i]);
			}
			$info = db('ClassifyModel2')->where(array('area_id'=>'wy'))->select();
			$area_id = 'wy';
			if($info){
				//循环改变表中存在的二级菜单状态
				for($i=0;$i<count($info);$i++){		
					$dels = $ClassifyModel2->changeSta($info[$i]['sid'],$area_id);	
				}
				//循环插入本次新选择则的二级菜单
				foreach ($sid as $key => $value) {
					$result = $ClassifyModel2->changes($area_id,$value[0],$value[1]);
					//$value[0]为一级分类id $value[1]为二级分类id
				}
			}else{
				for($j=0;$j<count($sid);$j++){
					$result = $ClassifyModel2->changes($area_id,$sid[$j][0],$sid[$j][1]);
					//$sid[$j][0]为一级分类id $sid[$j][1]为二级分类id
				}
			}
			if (false !== $result) {
				return $this->success('编辑成功！', url('index'));
			} else {
				$error = $Classify->getError();
				return $this->error(empty($error) ? '未知错误！' : $error);
			}
			
		}else{
			
			$list1 = db('classify')->select();
			$list1s = db('ClassifyModel1')->where(array('area_id'=>'wy','status'=>0))->select();
			for ($i=0; $i < count($list1s); $i++) { 
				$classify_2 = db('classify_2')->where('pid='.$list1s[$i]['pid'])->select();
				$list1s[$i]['classify_2'] = $classify_2;
				for ($j=0; $j < count($list1); $j++) { 
					if($list1s[$i]['pid'] ==  $list1[$j]['id']){
						$list1s[$i]['title'] = $list1[$j]['title'];
					}
				}
			}
			//var_dump($list1s);
			$info = db('ClassifyModel2')->where(array('area_id'=>'wy','status'=>0))->select();
			$classify2 = array();
			for($k=0;$k<count($info);$k++){
				array_push($classify2,$info[$k]['sid']);
			}
			$this->assign('list1s',$list1s);
			$this->assign('classify2',$classify2);
			$this->assign('info', null);
			return $this->fetch();
		}
	}












}