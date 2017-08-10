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

class Teachers extends Admin {

	public function index() {
		$map = array();
		//$map['status'] = 1;
		$name = input('name');
		if($name){
			$map['teacherName'] = array('like','%'.$name.'%');
		}
		$order = "id desc";
		$count = db('Teachers')->where("teacherName like '%".$name."%' and status=1")->count();
		$list  = db('Teachers')->where($map)->order($order)->paginate(10);
		//dump($list);die;
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign('count',$count);
		$this->assign($data);
		$this->setMeta("教师信息");
		return $this->fetch();
	}

	//添加
	public function add() {
		//$link = model('Teachers');
		if (IS_POST) {
			$data = input('post.');
			if ($data) {
				//unset($data['id']);
				$data['addtime'] = time();

				//判断编号是否重复
				$rs = db('Teachers')->where(array('teacherNo'=>$data['teacherNo']))->find();
				if($rs){
					$data['teacherNo'] = substr($data['teacherNo'],1,4);
					$data['teacherNo'] = intval($data['teacherNo'])+1;
					$data['teacherNo'] = strval($data['teacherNo']);
					switch(strlen($data['teacherNo'])){
						case 1 : $data['teacherNo'] = 'T000'.$data['teacherNo'];break;
						case 2 : $data['teacherNo'] = 'T00'.$data['teacherNo'];break;
						case 3 : $data['teacherNo'] = 'T0'.$data['teacherNo'];break;
						default : $data['teacherNo'] = 'T'.$data['teacherNo'];break;
					}
				}

				$result = db('Teachers')->insert($data);
				if ($result) {
					return $this->success("添加成功！", url('teachers/index'));
				} else {
					return $this->error($link->getError());
				}
			} else {
				return $this->error($link->getError());
			}
		} else {

			$maxNum = db('Teachers')->order('id desc')->find();
			$teacherNo = $maxNum['teacherNo'];
			$teacherNo = substr($teacherNo,1,4);
			$teacherNo = intval($teacherNo)+1;
			$teacherNo = strval($teacherNo);
			switch(strlen($teacherNo)){
				case 1 : $teacherNo = 'T000'.$teacherNo;break;
				case 2 : $teacherNo = 'T00'.$teacherNo;break;
				case 3 : $teacherNo = 'T0'.$teacherNo;break;
				default : $teacherNo = 'T'.$teacherNo;break;
			}

			$this->assign('teacherNo', $teacherNo);
			$this->setMeta("添加教师");
			return $this->fetch('teachers/add');
		}
	}

	//修改
	public function edit() {
		$id   = input('id', '', 'trim,intval');
		if (IS_POST) {
			$data = input('post.');
			if ($data) {
				$result = db('Teachers')->update($data, array('id' => $data['id']));
				if ($result) {
					return $this->success("修改成功！", url('teachers/index'));
				} else {
					return $this->error("修改失败！");
				}
			} else {
				return $this->error($link->getError());
			}
		} else {
			$map  = array('id' => $id);
			$info = db('Teachers')->where($map)->find();
			
			$this->assign('info',$info);
			$this->setMeta("编辑教师信息");
			return $this->fetch('teachers/edit');
		}
	}

	//删除
	public function delete() {
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			return $this->error('非法操作！');
		}
		$link = db('Teachers');

		$map    = array('id' => array('IN', $id));
		$result = $link->where($map)->delete();
		if ($result) {
			return $this->success("删除成功！");
		} else {
			return $this->error("删除失败！");
		}
	}
}