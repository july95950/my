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

class Payinfo extends Admin {

	public function index() {
		$map = array();
		//$map['status'] = 1;
		$name = input('name');
		$starttime = strtotime(input('starttime'));
		$endtime = strtotime(input('endtime'));
		if($name){
			$map['stuName'] = array('like','%'.$name.'%');
		}
		if($starttime && $endtime){
			$map['a.addtime'] = array('BETWEEN',array($starttime,$endtime));
			//dump($map['addtime']);die;
		}
		$classes = db('Class')->where(array('status'=>1))->order('addtime desc')->select();
		$count  = db('payment')
				 ->alias('a')
				 ->order('a.id desc')
				 ->join('jy_students b','a.stuId = b.id')
				 ->field('a.*,b.stuName,b.stuNo')
				 ->where($map)
				 ->count();
		$list  = db('payment')
				 ->alias('a')
				 ->order('a.id desc')
				 ->join('jy_students b','a.stuId = b.id')
				 ->field('a.*,b.stuName,b.stuNo')
				 ->where($map)
				 ->paginate(10);
		//dump($list);
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign('count',$count);
		$this->assign($data);
		$this->assign('classes',$classes);
		$this->setMeta("缴费信息");
		return $this->fetch();
	}

	//班级列表
	public function classlist(){
		$map['classId'] = input('classid');
		$maps['id'] = input('classid');
		$students = db('students')->where($map)->select();
		$classes = db('class')->where($maps)->find();
		//dump($students);

		$list  = db('payment')
				 ->alias('a')
				 ->order('a.id desc')
				 ->join('jy_students b','a.stuId = b.id')
				 ->field('a.*,b.stuName,b.stuNo')
				 ->where($map)
				 ->paginate(10);
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);
		$this->assign('students',$students);
		$this->assign('classes',$classes);
		$this->setMeta('班级缴费');
		return $this->fetch();
	}
	//个人列表
	public function personlist(){
		$id = input('id', '', 'trim,intval');
		$students = db('students')->where('id='.$id)->find();
		if(IS_POST){
			$info = input('post.');
			$info['addtime'] = time();
			$result = db('payment')->insert($info);
			if($result){
				$this->success('添加成功', url('payinfo/classlist?classid='.$students['classId']));
			}else{
				return $this->error($link->getError());
			}
		}
		//dump($students);
		$list  = db('payment')
				 ->alias('a')
				 ->order('a.id desc')
				 ->join('jy_students b','a.stuId = b.id')
				 ->field('a.*,b.stuName,b.stuNo')
				 ->where('b.id='.$id)
				 ->paginate(10);
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);
		$this->assign('students',$students);
		$this->setMeta('个人缴费');
		return $this->fetch();
	}

	//修改
	public function edit() {
		$id   = input('id', '', 'trim,intval');
		$map  = array('id' => $id);
		$info = db('payment')->where($map)->find();
		if (IS_POST) {
			$data = input('post.');
			$data['updatetime'] = time();
			//dump($data);die;
			if ($data) {
				$result = db('payment')->update($data, array('id' => $data['id']));
				if ($result) {
					return $this->success("修改成功！", url('payinfo/personlist?id='.$info['stuId']));
				} else {
					return $this->error("修改失败！");
				}
			} else {
				return $this->error($link->getError());
			}
		} else {
			
			$students = db('students')->where('id='.$info['stuId'])->find();
			//dump($students);
			$this->assign('info',$info);
			$this->assign('students',$students);
			$this->setMeta("编辑个人缴费信息");
			return $this->fetch('payinfo/edit');
		}
	}

	//删除
	public function delete() {
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			return $this->error('非法操作！');
		}
		$link = db('payment');

		$map    = array('id' => array('IN', $id));
		$result = $link->where($map)->delete();
		if ($result) {
			return $this->success("删除成功！");
		} else {
			return $this->error("删除失败！");
		}
	}
}