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

class Amn extends Admin {
	//分公司管理
	public function index() {
		$map = array();
		$order = "id desc";
		$list  = db('branch')->where($map)->order($order)->paginate(10);
		//dump($list);die;
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);
		$this->setMeta("分公司管理");
		return $this->fetch();
	}
	//分公司添加
	public function branchadd(){
		if(IS_POST){
			//dump($_POST);die;
			$data = input('post.');
			if($data){
				$data['addtime'] = time();
				$result = db('branch')->insert($data);
				if ($result) {
					return $this->success("添加成功！", url('amn/index'));
				} else {
					return $this->error($link->getError());
				}
			}
		}
		$this->setMeta("添加分公司");
		return $this->fetch();
	}
	//分公司编辑
	public function branchedit() {
		$id   = input('id', '', 'trim,intval');
		if (IS_POST) {
			$data = input('post.');
			if ($data) {
				$result = db('branch')->update($data, array('id' => $data['id']));
				if ($result) {
					return $this->success("修改成功！", url('amn/index'));
				} else {
					return $this->error("修改失败！");
				}
			} else {
				return $this->error($link->getError());
			}
		} else {
			$map  = array('id' => $id);
			$info = db('branch')->where($map)->find();
			$this->assign('info',$info);
			$this->setMeta("编辑分公司信息");
			return $this->fetch();
		}
	}
	//分公司删除
	public function branchdelete() {
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			return $this->error('非法操作！');
		}
		$link = db('branch');

		$map    = array('id' => array('IN', $id));
		$result = $link->where($map)->delete();
		if ($result) {
			return $this->success("删除成功！");
		} else {
			return $this->error("删除失败！");
		}
	}
	//部门管理
	public function department(){
		$map = array();
		$map['a.status'] = 1;
		$map['a.branchId'] = 1;
		$order = "id desc";
		$list  = db('department')
				->alias('a')
				->join('jy_branch b','a.branchId = b.id')
				->field('a.*,b.title')
				->where($map)
				->order($order)
				->select();
		//dump($list);die;
		$branch = db('branch')->where('status=1')->select();
		$branchNow = db('branch')->where('status=1 and id=1')->find();
		$this->assign('branch',$branch);
		$this->assign('branchNow',$branchNow);
		$this->assign('list',$list);
		//$list = db('department')->where('status=1 and branchId=1')->select();
		$this->setMeta("部门管理");
		return $this->fetch();
	}
	public function departmentajax(){
		if(IS_POST){
			$branchid = input('post.');

			if($branchid){
				$map = array();
				$map['a.status'] = 1;
				$map['a.branchId'] = $branchid['id'];
				$order = "id desc";
				$list  = db('department')
				->alias('a')
				->join('jy_branch b','a.branchId = b.id')
				->field('a.*,b.title')
				->where($map)
				->order($order)
				->select();
				//$departmentInfo = db('department')->where('branchId='.$branchid['id'])->find();
				$branchNow = db('branch')->where('id='.$branchid['id'])->find();
			}
			//return $branchNow;
			//return $departmentInfo;
			$this->assign('list',$list);
			$this->assign('branchNow',$branchNow);
			$html = $this->fetch();
			return $html;
		}
	}
	//部门添加
	public function departmentadd(){
		if(IS_POST){
			$data = input('post.');
			//dump($data);die;
			if($data){
				$data['addtime'] = time();
				$result = db('department')->insert($data);
				if ($result) {
					return $this->success("添加成功！", url('amn/department'));
				} else {
					return $this->error($link->getError());
				}
			}
		}
		$branchId = input('branchId');
		$branch = db('branch')->where('id='.$branchId)->find();
		dump($branch);
		$this->assign('branch',$branch);
		$this->setMeta("部门添加");
		return $this->fetch();
	}
	//部门编辑
	public function departmentedit(){
		if (IS_POST) {
			$data = input('post.');
			//dump($data);die;
			if ($data) {
				$result = db('department')->update($data, array('id' => $data['id']));
				if ($result) {
					return $this->success("修改成功！", url('amn/department'));
				} else {
					return $this->error("修改失败！");
				}
			} else {
				return $this->error($link->getError());
			}
		} else {
			$id   = input('id', '', 'trim,intval');
			$map  = array('id' => $id);
			$info = db('department')->where($map)->find();
			$branch = db('branch')->where('id='.$info['branchId'])->find();
			$this->assign('branch',$branch);
			$this->assign('info',$info);
			$this->setMeta("编辑部门信息");
			return $this->fetch();
		}
	}
	//部门删除
	public function departmentdelete() {
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			return $this->error('非法操作！');
		}
		$link = db('department');

		$map    = array('id' => array('IN', $id));
		$result = $link->where($map)->delete();
		if ($result) {
			return $this->success("删除成功！");
		} else {
			return $this->error("删除失败！");
		}
	}
	//员工管理分配部门
	public function staff(){
		$order = "uid desc";
		$map['departmentId'] = 0;
		$map['status'] = 1;
		$list = db('member')->where($map)->order($order)->paginate(10);
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		//dump($list);die;
		$branch = db('branch')->where('status=1')->select();
		$this->assign($data);
		$this->assign('branch',$branch);
		$this->setMeta("员工管理");
		return $this->fetch();
	}
	public function getdepartment(){
		if(IS_POST){
			$branchId = input('id');
			$department = db('department')->where('status=1 and branchId='.$branchId)->select();
			return $department;
		}
	}
	public function staffmanage(){
		if(IS_POST){
			$data = input('post.');
			//dump($data);die;
			//新增未分配人员
			if(isset($data['pid'])){
				for($i=0;$i<count($data['pid']);$i++){
				$re = db('member')->where('uid='.$data['pid'][$i])->update(array('departmentId' => $data['departmentId']));
				}
				if($re){
					return $this->success("添加成功",url('amn/staff'));
				}else{
					return $this->error("添加失败！");
				}
			}
			//修改已有人员
			if(isset($data['peid'])){
				$stu = db('member')->where('departmentId='.$data['departmentId'])->field('uid')->select();
				//dump($data['peid']);
				for($i=0;$i<count($stu);$i++){
					$stu[$i] = strval($stu[$i]['uid']);
				}
				//dump($stu);die;
				$arr = array_diff($stu,$data['peid']);
				//dump($arr);die;
				foreach($arr as $key=>$val){
					$res = db('member')->where('uid='.$arr[$key])->update(array('departmentId' => ''));
				}
				if($res){
					return $this->success("修改成功",url('amn/staff'));
				}else{
					return $this->error("修改失败！");
				}
			}else{
				$res = db('member')->where('departmentId='.$data['departmentId'])->update(array('departmentId' => ''));
				if($res){
					return $this->success("修改成功",url('amn/staff'));
				}else{
					return $this->error("修改失败！");
				}
			}
		}
		$departmentId = input('departmentId');
		$department = db('department')->where('status=1 and id='.$departmentId)->find();
		$branch = db('branch')->where('status=1 and id='.$department['branchId'])->find();
		$order = "uid desc";
		$map['departmentId'] = 0;
		$map['status'] = 1;
		$list = db('member')->where($map)->order($order)->select();
		$map['departmentId'] = $departmentId;
		$thisStaff = db('member')->where($map)->order($order)->select();
		$this->assign('list',$list);
		$this->assign('thisStaff',$thisStaff);
		$this->assign('department',$department);
		$this->assign('branch',$branch);
		$this->setMeta("员工分配");
		return $this->fetch();
	}
	//员工信息
	public function staffinfo(){
		if(IS_POST){
			$uid = input('post.id');
			$cardid = input('post.cardid');
			//判断是否关联卡
			$isRelative = db('makecard')->where('innercard='.$cardid.' and status=1')->find();
			if(!$isRelative){
				$ajaxReturn['code'] = -2;
				$ajaxReturn['msg'] = '该卡为未关联卡';
				return $ajaxReturn;
			}
			//判重
			$memhave = db('member')->where('status=1 and cardNo='.$cardid)->find();
			$stuhave = db('students')->where('status=1 and bindCardNo='.$cardid)->find();
			if($memhave || $stuhave){
				$ajaxReturn['code'] = -3;
				$ajaxReturn['msg'] = '该卡已绑定';
				return $ajaxReturn;
			}
			$lastCard = db('member')->where(array('uid'=>$uid))->value('cardNo');
			$result = db('member')->where(array('uid'=>$uid))->update(array('cardNo'=>$cardid));
			if(!$result){
				return $this->error($link->getError());
			}else{
				//将原卡设置为关联卡
				if($lastCard){
					$ress = db('makecard')->where('innercard='.$lastCard.' and status=2')->update(array('status'=>1));
				}
				$info['studentId'] = $uid;
				$info['cardId'] = $cardid;
				$info['addtime'] = time();
				$info['mark'] = 2;
				$rs = db('bind_card_action')->insert($info);
				//改变卡状态为已绑定卡
				$re = db('makecard')->where('innercard='.$cardid.' and status=1')->update(array('status'=>2));

				$ajaxReturn['code'] = 1;
				$ajaxReturn['id'] = $uid;
				$ajaxReturn['cardid'] = $cardid;
				return $ajaxReturn;
			}
		}
		$order = "uid desc";
		$list = db('member')->where('status=1 and departmentId>0')->order($order)->paginate(10);
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$lists = $list->all();
		foreach ($lists as $k =>$v){
			 $dein = db('department')->where('id='.$v['departmentId'])->find();
			 $v['department'] = $dein['name'];
			 $v['branch'] = db('branch')->where('id='.$dein['branchId'])->value('title');
			$list[$k] = $v;
		};
		$this->assign($data);
		$this->setMeta("员工信息");
		return $this->fetch();
	}

	//员工卡绑定记录
	public function staffCardRecord(){
		$id   = input('id', '', 'trim,intval');
		$order = "a.id desc";
		$list  = db('bind_card_action')
				->alias('a')
				->join('jy_member b','a.studentId = b.uid')
				->where('a.mark=2 and a.studentId='.$id)
				->field('a.*,b.username')
				->order($order)
				->paginate(10);
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);
		$this->setMeta("员工个人消费卡变更记录");
		return $this->fetch();
	}

	//制卡管理
	public function makecard(){
		if(IS_POST){
			$data = input('post.');
			if(isset($data['vip'])){
				$data['operator'] = session('user_auth.uid');
				$data['addtime'] = time();
				$ishave = db('makecard')->where('outercard='.$data['outercard'].' and vip='.$data['vip'])->find();
				if($ishave){
					$ajaxReturn['code'] = -1;
					$ajaxReturn['msg'] = '外部卡号已存在';
					return $ajaxReturn;
				}
				$result = db('makecard')->insert($data);
				if ($result) {
					return $this->success("预制成功");
				} else {
					return $this->error("预制失败");
				}
			}
			if(isset($data['vips'])){
				$startNo = $data['startNo'];
				$endNo = $data['endNo'];
				$map['vip'] = $data['vips'];
				$map['outercard'] = array('BETWEEN', array($startNo, $endNo));
				$ishave = db('makecard')->where($map)->select();
				for($j=0;$j<count($ishave);$j++){
					$ishave[$j] = $ishave[$j]['outercard'];
				}
				//return $ishave;
				for($i=$startNo;$i<=$endNo;$i++){
					if(!in_array($i,$ishave)){
						$info['outercard'] = $i;
						$info['addtime'] = time();
						$info['vip'] = $data['vips'];
						$info['operator'] = session('user_auth.uid');
						$result = db('makecard')->insert($info);
						if(!$result){
							return $this->error($link->getError());
						}
					}
				}

				$ajaxReturn['code'] = 1;
				return $ajaxReturn;
			}
		}
		$map = array();
		$map['a.status'] = 0;

		$order = "id desc";
		$list  = db('makecard')
				->alias('a')
				->join('jy_member b','a.operator = b.uid')
				->field('a.*,b.username')
				->where($map)
				->order($order)
				->paginate(10);
		//dump($list);die;

		$lists = $list->all();
		foreach ($lists as $k =>$v){
			$v['writedata'] = '0000000000000000000099'.$v['vip'].$v['outercard'];
			$list[$k] = $v;
		};
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);
		$map['a.status'] = 1;
		$todayStart= mktime(0,0,0,date('m'),date('d'),date('Y'));
		$todayEnd= mktime(23,59,59,date('m'),date('d'),date('Y'));
		$map['relationtime'] = array('BETWEEN', array($todayStart, $todayEnd));
		$list1  = db('makecard')
				->alias('a')
				->join('jy_member b','a.operator = b.uid')
				->field('a.*,b.username')
				->where($map)
				->order($order)
				->select();

		$this->assign('list1',$list1);
		$this->setMeta("制卡管理");
		return $this->fetch();
	}
	//内部卡号存储
	public function storecard(){
		if(IS_POST){
			$str = input('post.str');
			$innercard = input('post.innercard');
			//return $innercard;
			$arr = explode(',',$str);
			$id = $arr[0];
			$outercard = $arr[1];
			$ishave = db('makecard')->where('innercard='.$innercard)->find();
			if($ishave){
				return $this->error("该内部卡号已绑定");
			}else{
				$result = db('makecard')->where('id='.$id)->update(['status'=>1,'innercard'=>$innercard,'relationtime'=>time()]);
				if ($result) {
					$ajaxReturn['code'] = 1;
					$ajaxReturn['id'] = $id;
					return $ajaxReturn;
				} else {
					return $this->error($link->getError());
				}
			}
		}
	}
	//卡删除
	public function makeCardDelete(){
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			return $this->error('非法操作！');
		}
		$link = db('makecard');

		$map    = array('id' => array('IN', $id));
		$result = $link->where($map)->delete();
		if ($result) {
			return $this->success("删除成功！");
		} else {
			return $this->error("删除失败！");
		}
	}
	public function storecardajax(){
		$map = array();
		$map['a.status'] = 1;
		$order = "id desc";
		$todayStart= mktime(0,0,0,date('m'),date('d'),date('Y'));
		$todayEnd= mktime(23,59,59,date('m'),date('d'),date('Y'));
		$map['relationtime'] = array('BETWEEN', array($todayStart, $todayEnd));
		$list1  = db('makecard')
				->alias('a')
				->join('jy_member b','a.operator = b.uid')
				->field('a.*,b.username')
				->where($map)
				->order($order)
				->select();
		$this->assign('list1',$list1);

		$html = $this->fetch();
		return $html;
	}
	//制卡信息
	public function cardinfo(){
		$map = array();
		$vip = input('vips');
		$query = array();
		if(isset($vip)){
			$map['vip'] = $vip;
			$query['vip'] = $vip;
		}
		$status = input('status');
		if(isset($status)){
			$map['status'] = $status;
			$query['status'] = $status;
		}
		$starttime = strtotime(input('starttime'));
		$endtime = strtotime(input('endtime'));
		$endtime = mktime(23,59,59,date('m',$endtime),date('d',$endtime),date('Y',$endtime));
		if(isset($starttime) && isset($endtime)){
			$map['addtime'] = array('BETWEEN', array($starttime, $endtime));
			$query['starttime'] = input('starttime');
			$query['endtime'] = input('endtime');
		}
		$order = "id desc";
		$list = db('makecard')->where($map)->order($order)->paginate(10,false,['query'=>$query]);
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);
		$this->setMeta("制卡信息");
		return $this->fetch();
	}
	//订单流水
	public function allorders(){
		$map = array();
		$order = "id desc";
		$starttime = strtotime(input('starttime'));
		$endtime = strtotime(input('endtime'));
		if($starttime && $endtime){
			$dateStart= mktime(0,0,0,date('m',$starttime),date('d',$starttime),date('Y',$starttime));
			$dateEnd= mktime(23,59,59,date('m',$endtime),date('d',$endtime),date('Y',$endtime));
		}else{
			$dateStart= mktime(0,0,0,date('m'),date('d'),date('Y'));
			$dateEnd= mktime(23,59,59,date('m'),date('d'),date('Y'));
		}
		$map['clear_time'] = array('BETWEEN', array($dateStart, $dateEnd));
		$count = db('order')->where($map)->count();
		$allSales = db('order')->where($map)->sum('price');
		$map['departmentId'] = 2;
		$foodSales = db('order')->where($map)->sum('price');
		$map['departmentId'] = 1;
		$qlxSales = db('order')->where($map)->sum('price');
		$map['departmentId'] = 3;
		$gymSales = db('order')->where($map)->sum('price');
		$this->assign('dateStart',$dateStart);
		$this->assign('dateEnd',$dateEnd);
		$this->assign('count',$count);
		$this->assign('allSales',$allSales);
		$this->assign('foodSales',$foodSales);
		$this->assign('qlxSales',$qlxSales);
		$this->assign('gymSales',$gymSales);
		$this->setMeta("订单流水");
		return $this->fetch();
	}
	//单个订单流水
	public function sigleorders(){
		$map = array();
		$query = array();
		$map['departmentId'] = input('eid');
		$query['departmentId'] = input('eid');
		$maps['a.departmentId'] = input('eid');
		$starttime = input('starttime');
		$query['starttime'] = input('starttime');
		$endtime = input('endtime');
		$query['endtime'] = input('endtime');
		$map['clear_time'] = array('BETWEEN', array($starttime, $endtime));
		$maps['a.clear_time'] = array('BETWEEN', array($starttime, $endtime));
		$count = db('order')->where($map)->count();
		$list = db('order')
				->alias('a')
				->join('member b','a.operate_id = b.uid')
				->field('a.*,b.username')
				->where($maps)
				->paginate(10,false,['query'=>$query]);

		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);
		$this->assign('count',$count);
		$this->assign('starttime',$starttime);
		$this->assign('endtime',$endtime);
		$this->setMeta("各部门订单流水");
		return $this->fetch();
	}
	//卡号查询订单
	public function cardorders(){
		$map['clear_card_id'] = input('cardNo');
		$maps['a.clear_card_id'] = input('cardNo');
		$count = db('order')->where($map)->count();
		$list = db('order')
				->alias('a')
				->join('member b','a.operate_id = b.uid')
				->field('a.*,b.username')
				->where($maps)
				->paginate(10,false,['query'=>$maps]);

		$lists = $list->all();
		foreach ($lists as $k =>$v){
			 $v['outercard'] = db('makecard')->where('innercard='.$map['clear_card_id'])->value('outercard');
			$list[$k] = $v;
		};

		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);
		$this->assign('count',$count);
		$this->assign('cardNo',$map['clear_card_id']);
		$this->setMeta("订单流水");
		return $this->fetch();
	}


}