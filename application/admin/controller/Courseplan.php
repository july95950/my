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

class Courseplan extends Admin {

	public function index() {
		$info = input('get.');
		if(isset($info['date2'])){
			$info['date2'] = strtotime($info['date2']);
			//当周的时间戳
			$beginNextWeek = mktime(0,0,0,date('m',$info['date2']),date('d',$info['date2'])-date('w',$info['date2'])+1,date('Y',$info['date2']));
			$endNextWeek = mktime(23,59,59,date('m',$info['date2']),date('d',$info['date2'])-date('w',$info['date2'])+7,date('Y',$info['date2']));
		}else{
			//下周一、周日的时间戳
			$beginNextWeek = mktime(0,0,0,date('m'),date('d')-date('w')+1+7,date('Y'));
			$endNextWeek = mktime(23,59,59,date('m'),date('d')-date('w')+7+7,date('Y'));
		}
		$rsnextWeek = mktime(0,0,0,date('m'),date('d')-date('w')+1+7,date('Y'));
		$renextWeek = mktime(23,59,59,date('m'),date('d')-date('w')+7+7,date('Y'));
		$map = array();
		$order = "id desc";
		$list  = db('Courseplan')->where($map)->order($order)->paginate(10);

		$classes = db('Class')->where(array('status'=>1))->order('addtime desc')->select();
		$allClass = $classes;
		$doClass = [];
		$nClass = [];
		$countY = 0;
		$countW = 0;
		//查看是否已有课表安排
		for($i=0;$i<count($classes);$i++){
			$maps['classId'] = $classes[$i]['id'];
			$maps['date'] = array('BETWEEN', array($beginNextWeek, $endNextWeek));
			$cList = db('Courseplan')->where($maps)->select();
			if($cList){
				$classes[$i]['status'] = 1;
				$countY += 1;
				//unset($classes[$i]);
			}else{
				$classes[$i]['status'] = 0;
				$countW += 1;
				array_push($doClass,$classes[$i]);
			}
			$maps['date'] = array('BETWEEN', array($rsnextWeek, $renextWeek));
			$cLists = db('Courseplan')->where($maps)->select();
			if(!$cLists){
				array_push($nClass,$classes[$i]);
			}
		}
		//dump($countY);
		//dump($classes);
		$this->assign('countY',$countY);
		$this->assign('countW',$countW);
		$this->assign('doClass',$doClass);
		$this->assign('nClass',$nClass);
		$this->assign('classes',$classes);
		$this->assign('allClass',$allClass);
		$this->assign('beginNextWeek',$beginNextWeek);
		$this->assign('endNextWeek',$endNextWeek);
		$this->assign('rsnextWeek',$rsnextWeek);

		//dump($list);die;
		$data = array(
			'list' => $list,
			//'page' => $list->render(),
		);

		$this->assign($data);
		$this->setMeta("课表信息");
		return $this->fetch();
	}

	//添加
	public function add() {
		//$link = model('Courseplan');
		if (IS_POST) {
			$data = input('post.cList');
			$data = json_decode($data,true);
			//return $data;
			if ($data) {
				//unset($data['id']);
				for($i=0;$i<count($data);$i++){
					$data[$i]['addtime'] = time();
					$data[$i]['date'] = strtotime($data[$i]['date']);
					$result = db('Courseplan')->insert($data[$i]);
					if(!$result){
						return $this->error($link->getError());
					}
				}
				return $this->success("添加成功！", url('courseplan/index'));
			} else {
				return $this->error($link->getError());
			}
		} else {
			$classid = input('get.classid');
			$selfClass = db('Class')->where('status=1 and id='.$classid)->field('id,className')->find();
			$course = db('course')->where('status=1 and pid>0')->field('id,title')->order('addtime desc')->select();
			//获取星期时间戳
			$monday = mktime(0,0,0,date('m'),date('d')-date('w')+1+7,date('Y'));
			$tuesday = mktime(0,0,0,date('m'),date('d')-date('w')+2+7,date('Y'));
			$wednesday = mktime(0,0,0,date('m'),date('d')-date('w')+3+7,date('Y'));
			$thursday  = mktime(0,0,0,date('m'),date('d')-date('w')+4+7,date('Y'));
			$friday  = mktime(0,0,0,date('m'),date('d')-date('w')+5+7,date('Y'));
			$saturday  = mktime(0,0,0,date('m'),date('d')-date('w')+6+7,date('Y'));
			$sunday = mktime(23,59,59,date('m'),date('d')-date('w')+7+7,date('Y'));

			$monday1 = date('Y-m-d',$monday);
			$sunday1 = date('Y-m-d',$sunday);
			$this->assign('selfClass',$selfClass);
			$this->assign('monday',$monday);
			$this->assign('tuesday',$tuesday);
			$this->assign('wednesday',$wednesday);
			$this->assign('thursday',$thursday);
			$this->assign('friday',$friday);
			$this->assign('saturday',$saturday);
			$this->assign('sunday',$sunday);
			$this->assign('course',$course);
			$this->setMeta("添加课表（下周课程安排 ".$monday1.'到'.$sunday1."）");
			return $this->fetch('courseplan/add');
		}
	}

	//修改
	public function edit() {
		$id   = input('id', '', 'trim,intval');
		if (IS_POST) {
			$data = input('post.cList');
			$data = json_decode($data,true);
			$monday = mktime(0,0,0,date('m'),date('d')-date('w')+1+7,date('Y'));
			$sunday = mktime(23,59,59,date('m'),date('d')-date('w')+7+7,date('Y'));

			$maps['classId'] = $id;
			$maps['date'] = array('BETWEEN', array($monday, $sunday));
			$cList = db('Courseplan')->where($maps)->delete();

			if ($data) {
				//unset($data['id']);
				for($i=0;$i<count($data);$i++){
					$data[$i]['addtime'] = time();
					$data[$i]['date'] = strtotime($data[$i]['date']);
					$result = db('Courseplan')->insert($data[$i]);
					if(!$result){
						return $this->error($link->getError());
					}
				}
				return $this->success("修改成功！", url('courseplan/index'));
			} else {
				return $this->error($link->getError());
			}
			
		} else {
			$st = input('st');

			if(isset($st)){
				$monday = mktime(0,0,0,date('m',$st),date('d',$st)-date('w',$st)+1,date('Y',$st));
				$tuesday = mktime(0,0,0,date('m',$st),date('d',$st)-date('w',$st)+2,date('Y',$st));
				$wednesday = mktime(0,0,0,date('m',$st),date('d',$st)-date('w',$st)+3,date('Y',$st));
				$thursday = mktime(0,0,0,date('m',$st),date('d',$st)-date('w',$st)+4,date('Y',$st));
				$friday = mktime(0,0,0,date('m',$st),date('d',$st)-date('w',$st)+5,date('Y',$st));
				$saturday = mktime(0,0,0,date('m',$st),date('d',$st)-date('w',$st)+6,date('Y',$st));
				$sunday = mktime(23,59,59,date('m',$st),date('d',$st)-date('w',$st)+7,date('Y',$st));

			}else{
				$monday = mktime(0,0,0,date('m'),date('d')-date('w')+1+7,date('Y'));
				$tuesday = mktime(0,0,0,date('m'),date('d')-date('w')+2+7,date('Y'));
				$wednesday = mktime(0,0,0,date('m'),date('d')-date('w')+3+7,date('Y'));
				$thursday  = mktime(0,0,0,date('m'),date('d')-date('w')+4+7,date('Y'));
				$friday  = mktime(0,0,0,date('m'),date('d')-date('w')+5+7,date('Y'));
				$saturday  = mktime(0,0,0,date('m'),date('d')-date('w')+6+7,date('Y'));
				$sunday = mktime(23,59,59,date('m'),date('d')-date('w')+7+7,date('Y'));
			}
			//dump(date('Y-m-d',$tuesday));die;
			$selfClass = db('Class')->where('status=1 and id='.$id)->field('id,className')->find();
			$course = db('course')->where('status=1 and pid>0')->field('id,title')->order('addtime desc')->select();

			//查询课表信息
			$maps['classId'] = $id;
			$maps['date'] = array('BETWEEN', array($monday, $sunday));
			$cList = db('Courseplan')->where($maps)->select();
			$cList = json_encode($cList);
			$this->assign('monday',$monday);
			$this->assign('tuesday',$tuesday);
			$this->assign('wednesday',$wednesday);
			$this->assign('thursday',$thursday);
			$this->assign('friday',$friday);
			$this->assign('saturday',$saturday);
			$this->assign('sunday',$sunday);
			$this->assign('selfClass',$selfClass);
			$this->assign('course',$course);
			$this->assign('cList',$cList);
			$this->setMeta("编辑课表信息");
			return $this->fetch('courseplan/edit');
		}
	}

	//删除
	public function delete() {
		//$id = input('id');
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			return $this->error('非法操作！');
		}
		$Courseplan = db('Courseplan');
		$monday = mktime(0,0,0,date('m'),date('d')-date('w')+1+7,date('Y'));
		$sunday = mktime(23,59,59,date('m'),date('d')-date('w')+7+7,date('Y'));
		$map    = array('classId' => array('IN', $id));
		$map['date'] = array('BETWEEN', array($monday, $sunday));
		$result = $Courseplan->where($map)->delete();
		if ($result) {
			return $this->success("删除成功！");
		} else {
			return $this->error("删除失败！");
		}
	}
	//课表预览
	public function menupreview(){
		$st = input('st');
		$id = input('id');
		if(isset($st)){
			$monday = mktime(0,0,0,date('m',$st),date('d',$st)-date('w',$st)+1,date('Y',$st));
			$tuesday = mktime(0,0,0,date('m',$st),date('d',$st)-date('w',$st)+2,date('Y',$st));
			$wednesday = mktime(0,0,0,date('m',$st),date('d',$st)-date('w',$st)+3,date('Y',$st));
			$thursday = mktime(0,0,0,date('m',$st),date('d',$st)-date('w',$st)+4,date('Y',$st));
			$friday = mktime(0,0,0,date('m',$st),date('d',$st)-date('w',$st)+5,date('Y',$st));
			$saturday = mktime(0,0,0,date('m',$st),date('d',$st)-date('w',$st)+6,date('Y',$st));
			$sunday = mktime(23,59,59,date('m',$st),date('d',$st)-date('w',$st)+7,date('Y',$st));

		}else{
			$monday = mktime(0,0,0,date('m'),date('d')-date('w')+1+7,date('Y'));
			$tuesday = mktime(0,0,0,date('m'),date('d')-date('w')+2+7,date('Y'));
			$wednesday = mktime(0,0,0,date('m'),date('d')-date('w')+3+7,date('Y'));
			$thursday  = mktime(0,0,0,date('m'),date('d')-date('w')+4+7,date('Y'));
			$friday  = mktime(0,0,0,date('m'),date('d')-date('w')+5+7,date('Y'));
			$saturday  = mktime(0,0,0,date('m'),date('d')-date('w')+6+7,date('Y'));
			$sunday = mktime(23,59,59,date('m'),date('d')-date('w')+7+7,date('Y'));
		}

		$maps['a.classId'] = $id;
		$maps['a.date'] = array('BETWEEN', array($monday, $sunday));
		$cList = db('Courseplan')
				 ->alias('a')
				 ->join('course b','a.courseId = b.id')
				 ->field('a.*,b.title')
				 ->where($maps)
				 ->select();
		$cList = json_encode($cList);
		$selfClass = db('Class')->where('status=1 and id='.$id)->field('id,className')->find();
		$this->assign('cList',$cList);
		$this->assign('selfClass',$selfClass);
		$this->assign('monday',$monday);
		$this->assign('tuesday',$tuesday);
		$this->assign('wednesday',$wednesday);
		$this->assign('thursday',$thursday);
		$this->assign('friday',$friday);
		$this->assign('saturday',$saturday);
		$this->assign('sunday',$sunday);
		return $this->fetch();
	}
}