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

class College extends Admin {

	public function index() {
		$map = array();
		//$map['status'] = 1;
		$order = "id desc";
		$name = input('name');
		if($name){
			$map['stuName'] = array('like','%'.$name.'%');
			$list  = db('students')->where($map)->order($order)->paginate(10,false,['query'=>array('name'=>$name)]);
		}else{

			$list  = db('students')->where($map)->order($order)->paginate(10);
		}
		
		$count = db('students')->where("stuName like '%".$name."%' and status=1")->count();
		//dump($list);die;
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign('count',$count);
		$this->assign($data);
		$this->setMeta("学员信息");
		return $this->fetch();
	}

	//添加
	public function add() {
		//$link = model('students');
		if (IS_POST) {
			$data = input('post.');
			if ($data) {
				//unset($data['id']);
				$data['addtime'] = time();
				//判断编号是否重复
				$rs = db('students')->where(array('stuNo'=>$data['stuNo']))->find();
				if($rs){
					$data['stuNo'] = substr($data['stuNo'],5,4);
					$data['stuNo'] = intval($data['stuNo'])+1;
					$data['stuNo'] = strval($data['stuNo']);
					switch(strlen($data['stuNo'])){
						case 1 : $stuNo = 'S'.date('Y',time()).'000'.$stuNo;break;
						case 2 : $stuNo = 'S'.date('Y',time()).'00'.$stuNo;break;
						case 3 : $stuNo = 'S'.date('Y',time()).'0'.$stuNo;break;
						default : $stuNo = 'S'.date('Y',time()).$stuNo;break;
					}
				}

				$cardRep = db('students')->where(array('status'=>1,'bindCardNo'=>$data['bindCardNo']))->find();
				//dump($cardRep);die;
				if($cardRep){
					return $this->error('当前卡号已绑定，请确认后重新绑定');
				}

				$result = db('students')->insert($data);
				if ($result) {
					//卡号变更行为记录
					$info = db('students')->where(array('status'=>1,'bindCardNo'=>$data['bindCardNo']))->find();
					$actionData['studentId'] = $info['id'];
					$actionData['cardId'] = $data['bindCardNo'];
					$actionData['addtime'] = time();

					$res = db('bind_card_action')->insert($actionData);

					return $this->success("添加成功！", url('college/index'));
				} else {
					return $this->error($link->getError());
				}
			} else {
				return $this->error($link->getError());
			}
		} else {
			$maxNum = db('students')->order('id desc')->find();
			$stuNo = $maxNum['stuNo'];
			$stuNo = substr($stuNo,5,4);
			$stuNo = intval($stuNo)+1;
			$stuNo = strval($stuNo);
			$years = substr($maxNum['stuNo'],1,4);
			if($years != date('Y',time())){
				$stuNo = 1;
			}
			switch(strlen($stuNo)){
				case 1 : $stuNo = 'S'.date('Y',time()).'000'.$stuNo;break;
				case 2 : $stuNo = 'S'.date('Y',time()).'00'.$stuNo;break;
				case 3 : $stuNo = 'S'.date('Y',time()).'0'.$stuNo;break;
				default : $stuNo = 'S'.date('Y',time()).$stuNo;break;
			}
			$this->assign('stuNo',$stuNo);
			$this->setMeta("添加学员");
			return $this->fetch('college/add');
		}
	}

	//修改
	public function edit() {
		$id   = input('id', '', 'trim,intval');
		if (IS_POST) {
			$data = input('post.');
			if ($data) {
				/*
				$cardRep = db('students')->where(array('status'=>1,'bindCardNo'=>$data['bindCardNo']))->find();
				//dump($cardRep);die;
				if($cardRep && $id != $cardRep['id']){
					return $this->error('当前卡号已绑定，请确认后重新绑定');
				}
				*/
				$result = db('students')->update($data, array('id' => $data['id']));
				if ($result) {
					//卡号变更行为记录
					$actionData['studentId'] = $id;
					//$actionData['cardId'] = $data['bindCardNo'];
					//$actionData['addtime'] = time();
					//$res = db('bind_card_action')->insert($actionData);

					return $this->success("修改成功！", url('college/index'));
				} else {
					return $this->error("修改失败！");
				}
			} else {
				return $this->error($link->getError());
			}
		} else {
			$map  = array('id' => $id);
			$info = db('students')->where($map)->find();
			
			$this->assign('info',$info);
			$this->setMeta("编辑学员信息");
			return $this->fetch('college/edit');
		}
	}

	//删除
	public function delete() {
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			return $this->error('非法操作！');
		}
		$link = db('students');

		$map    = array('id' => array('IN', $id));
		$result = $link->where($map)->delete();
		if ($result) {
			return $this->success("删除成功！");
		} else {
			return $this->error("删除失败！");
		}
	}

	//个人消费卡变更记录
	public function cardRecord(){
		$id   = input('id', '', 'trim,intval');
		$order = "a.id desc";
		$list  = db('bind_card_action')
				->alias('a')
				->join('jy_students b','a.studentId = b.id')
				->where('a.mark=1 and a.studentId='.$id)
				->field('a.*,b.stuName')
				->order($order)
				->paginate(10);
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);
		$this->setMeta("个人消费卡变更记录");
		return $this->fetch('college/cardRecord');
	}

}