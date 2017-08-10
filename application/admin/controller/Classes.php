<?php
// +----------------------------------------------------------------------
// | JunYuCMS 
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.213idc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: info <info@213idc.com> <http://www.213idc.cn>
// +----------------------------------------------------------------------

namespace app\admin\controller;
use app\common\controller\Admin;

class Classes extends Admin {

	public function index() {
		$map = array();
		$name = input('name');
		if($name){
			$map['className'] = array('like','%'.$name.'%');
		}
		$order = "id desc";
		$list  = db('Class')->where($map)->order($order)->paginate(10);
		//dump($list);die;
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);

		$this->assign($data);
		$this->setMeta("班级信息");
		return $this->fetch();
	}

	//添加
	public function add() {
		//$link = model('Class');
		if (IS_POST) {
			$data = input('post.');
			if ($data) {
				//unset($data['id']);
				$data['addtime'] = time();
				//判断编号是否重复
				$rs = db('Class')->where(array('classId'=>$data['classId']))->find();
				if($rs){
					$data['classId'] = substr($data['classId'],5,4);
					$data['classId'] = intval($data['classId'])+1;
					$data['classId'] = strval($data['classId']);
					switch(strlen($data['classId'])){
						case 1 : $classId = 'B'.date('Y',time()).'000'.$classId;break;
						case 2 : $classId = 'B'.date('Y',time()).'00'.$classId;break;
						case 3 : $classId = 'B'.date('Y',time()).'0'.$classId;break;
						default : $classId = 'B'.date('Y',time()).$classId;break;
					}
				}

				$result = db('Class')->insert($data);
				if ($result) {
					return $this->success("添加成功！", url('classes/index'));
				} else {
					return $this->error($link->getError());
				}
			} else {
				return $this->error($link->getError());
			}
		} else {
			$maxNum = db('Class')->order('id desc')->find();
			$classId = $maxNum['classId'];
			$classId = substr($classId,5,4);
			$classId = intval($classId)+1;
			$classId = strval($classId);
			$years = substr($maxNum['classId'],1,4);
			if($years != date('Y',time())){
				$classId = 1;
			}
			switch(strlen($classId)){
				case 1 : $classId = 'B'.date('Y',time()).'000'.$classId;break;
				case 2 : $classId = 'B'.date('Y',time()).'00'.$classId;break;
				case 3 : $classId = 'B'.date('Y',time()).'0'.$classId;break;
				default : $classId = 'B'.date('Y',time()).$classId;break;
			}
			$this->assign('classId',$classId);
			$this->setMeta("添加班级");
			return $this->fetch('classes/add');
		}
	}

	//修改
	public function edit() {
		$id   = input('id', '', 'trim,intval');
		if (IS_POST) {
			$data = input('post.');
			if ($data) {
				$result = db('Class')->update($data, array('id' => $data['id']));
				if ($result) {
					return $this->success("修改成功！", url('classes/index'));
				} else {
					return $this->error("修改失败！");
				}
			} else {
				return $this->error($link->getError());
			}
		} else {
			$map  = array('id' => $id);
			$info = db('Class')->where($map)->find();
			
			$this->assign('info',$info);
			$this->setMeta("编辑班级信息");
			return $this->fetch('classes/edit');
		}
	}

	//删除
	public function delete() {
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			return $this->error('非法操作！');
		}
		$link = db('Class');

		$map    = array('id' => array('IN', $id));
		$result = $link->where($map)->delete();
		if ($result) {
			return $this->success("删除成功！");
		} else {
			return $this->error("删除失败！");
		}
	}

	//班级组建
	public function organize(){
		if(IS_POST){
			$data = input('post.');
			//dump($data);die;
			//新增未分配人员
			if(isset($data['pid'])){
				for($i=0;$i<count($data['pid']);$i++){
				$re = db('students')->where('id='.$data['pid'][$i])->update(array('classId' => $data['classId']));
				}
				if($re){
					return $this->success("添加成功",url('classes/index'));
				}else{
					return $this->error("添加失败！");
				}
			}
			//修改已有人员
			if(isset($data['peid'])){
				$stu = db('students')->where('classId='.$data['classId'])->field('id')->select();
				//dump($data['peid']);
				for($i=0;$i<count($stu);$i++){
					$stu[$i] = strval($stu[$i]['id']);
				}
				$arr = array_diff($stu,$data['peid']);
				//dump($arr);die;
				foreach($arr as $key=>$val){
					$res = db('students')->where('id='.$arr[$key])->update(array('classId' => ''));
				}
				if($res){
					return $this->success("修改成功",url('classes/index'));
				}else{
					return $this->error("修改失败！");
				}

			}
		}
		$id = input('id');
		$map['classId'] = '';
		$map['status'] = 1;
		$order = 'id asc';
		$students = db('students')->where($map)->order($order)->select();
		$thisStudents = db('students')->where('classId='.$id)->order($order)->select();
		$classes = db('class')->where('id='.$id)->find();
		//dump($thisStudents);
		$this->assign('students',$students);
		$this->assign('thisStudents',$thisStudents);
		$this->assign('classes',$classes);
		$this->setMeta("班级组建");
		return $this->fetch('classes/organize');
	}


}