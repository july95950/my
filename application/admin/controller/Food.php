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

class Food extends Admin {

	public function foodstaff() {
		
		$map = array();
		$map['status'] = 1;
		$map['departmentId'] = 2;
		$order = "uid desc";
		$list  = db('member')->where($map)->order($order)->paginate(10);
		//dump($list);die;
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);

		$this->assign($data);
		
		$this->setMeta("餐厅工作人员信息");
		return $this->fetch();
	}

	public function foodclass(){
		$map = array();
		$order = "id asc";
		$list  = db('foodclass')->where($map)->order($order)->paginate(10);
		//dump($list);die;
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);

		$this->assign($data);
		$this->setMeta("餐品类别");
		return $this->fetch();
	}
	//餐品分类添加
	public function addclass(){
		if(IS_POST){
			$data = input('post.');
			$data['addtime'] = time();
			$result = db('foodclass')->insert($data);
			if ($result) {
				return $this->success("添加成功！", url('food/foodclass'));
			} else {
				return $this->error($link->getError());
			}
			//dump($data);die;
		}else{
			$this->setMeta("添加餐品分类");
			return $this->fetch();
		}
	}
	//餐品分类编辑
	public function editclass(){
		$id   = input('id', '', 'trim,intval');
		if(IS_POST){
			$data = input('post.');
			//dump($data);die;
			if ($data) {
				$result = db('foodclass')->update($data, array('id' => $data['id']));
				if ($result) {
					return $this->success("修改成功！", url('food/foodclass'));
				} else {
					return $this->error("修改失败！");
				}
			} else {
				return $this->error($link->getError());
			}
			//dump($data);die;
		}else{
			$map  = array('id' => $id);
			$info = db('foodclass')->where($map)->find();
			$this->assign('info',$info);
			$this->setMeta("添加餐品分类");
			return $this->fetch();
		}
	}
	//餐品分类删除
	public function deleteclass() {
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			return $this->error('非法操作！');
		}
		$link = db('foodclass');

		$map    = array('id' => array('IN', $id));
		$result = $link->where($map)->delete();
		if ($result) {
			return $this->success("删除成功！");
		} else {
			return $this->error("删除失败！");
		}
	}
	//菜品管理
	public function cookfood(){
		$map = array();
		$order = "id asc";
		$list  = db('cookfood')->where($map)->order($order)->paginate(10);

		$lists = $list->all();
		foreach ($lists as $k =>$v){
			//$v['foodclass'] = '2344';
			$foodArr = explode(',',$v['foodclass']);

			$class = '';
			foreach($foodArr as $key => $val){
				$classes = db('foodclass')->where('id='.$foodArr[$key])->find();
				$class .= ' '.$classes['title'];
			}
			$v['foodclass'] =$class;
			$list[$k] = $v;
		};

		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);
		$this->setMeta("餐品管理");
		return $this->fetch();
	}
	//菜品添加
	public function cookadd(){
		if(IS_POST){
			$data = input('post.');
			$data['foodclass'] = implode(',',$data['foodclass']);
			$data['addtime'] = time();
			$result = db('cookfood')->insert($data);
			if ($result) {
				return $this->success("添加成功！", url('food/cookfood'));
			} else {
				return $this->error($link->getError());
			}
			//dump($data);die;
		}else{
			$foodclass = db('foodclass')->where('status=1')->select();
			$field = 'img';
			$this->assign('field',$field);
			$this->assign('foodclass',$foodclass);
			$this->setMeta("添加菜品");
			return $this->fetch();
		}
	}
	//菜品编辑
	public function cookedit(){
		$id   = input('id', '', 'trim,intval');
		
		if(IS_POST){
			
			$data = input('post.');
			
			$data['foodclass'] = implode(',',$data['foodclass']);
			//dump($data);die;
			$result = db('cookfood')->update($data, array('id' => $data['id']));
			if ($result) {
				return $this->success("修改成功！", url('food/cookfood'));
			} else {
				return $this->error($link->getError());
			}
			//dump($data);die;
		}else{
			$cook = db('cookfood')->where('id='.$id)->find();
			$cook['foodclass'] = explode(',',$cook['foodclass']);
			//dump($cook);
			$foodclass = db('foodclass')->where('status=1')->select();
			$foodclass1 = $foodclass;
			//去掉已选择的分类，获取未选择分类
			foreach($foodclass1 as $key=>$val){
				//dump($foodclass1[$key]);
				foreach($cook['foodclass'] as $k => $v){
					//dump($cook['foodclass'][$k]);
					if($foodclass1[$key]['id'] == $cook['foodclass'][$k]){
						$foodclass1[$key]['mark']=1;
						continue;
					}
				}
			}
			foreach($foodclass1 as $key=>$val){
				if(isset($foodclass1[$key]['mark'])){
					unset($foodclass1[$key]);
				}
			}
			//dump($foodclass1);die;
			$field = 'img';
			$this->assign('field',$field);
			$this->assign('foodclass',$foodclass);
			$this->assign('foodclass1',$foodclass1);
			$this->assign('cook',$cook);
			$this->setMeta("编辑菜品");
			return $this->fetch();
		}
	}
	//菜品删除
	public function deletecook() {
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			return $this->error('非法操作！');
		}
		$link = db('cookfood');

		$map    = array('id' => array('IN', $id));
		$result = $link->where($map)->delete();
		if ($result) {
			return $this->success("删除成功！");
		} else {
			return $this->error("删除失败！");
		}
	}

	//原料入库
	public function originalfood(){
		$map = array();
		$order = "id desc";
		$list  = db('foodstorage')->where($map)->order($order)->paginate(10);
		//dump($list);die;
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);

		$this->assign($data);
		$this->setMeta("原料入库");
		return $this->fetch();
	}
	//原料入库添加
	public function originaladd(){
		if(IS_POST){
			$data = input('post.');
			$data['addtime'] = time();
			$result = db('foodstorage')->insert($data);
			if ($result) {
				return $this->success("添加成功！", url('food/originalfood'));
			} else {
				return $this->error($link->getError());
			}
			//dump($data);die;
		}else{
			$this->setMeta("添加餐品分类");
			return $this->fetch();
		}
	}

	//原料入库编辑
	public function originaledit(){
		$id   = input('id', '', 'trim,intval');
		if(IS_POST){
			$data = input('post.');
			//dump($data);die;
			if ($data) {
				$result = db('foodstorage')->update($data, array('id' => $data['id']));
				if ($result) {
					return $this->success("修改成功！", url('food/originalfood'));
				} else {
					return $this->error("修改失败！");
				}
			} else {
				return $this->error($link->getError());
			}
			//dump($data);die;
		}else{
			$map  = array('id' => $id);
			$info = db('foodstorage')->where($map)->find();
			//dump($info);
			$this->assign('info',$info);
			$this->setMeta("添加餐品分类");
			return $this->fetch();
		}
	}
	//原料入库删除
	public function originaldelete() {
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			return $this->error('非法操作！');
		}
		$link = db('foodstorage');

		$map    = array('id' => array('IN', $id));
		$result = $link->where($map)->delete();
		if ($result) {
			return $this->success("删除成功！");
		} else {
			return $this->error("删除失败！");
		}
	}

	//菜品售卖
	public function foodsell(){
		//dump($_SESSION['admin']);die;
		$cook = db('cookfood')->where('status=1')->select();
		$this->assign('cook',$cook);
		$username = session('user_auth.username');
		$userid = session('user_auth.uid');
		$this->assign('username',$username);
		$this->assign('userid',$userid);
		$this->setMeta("菜品售卖");
		return $this->fetch();
	}
	//菜品结算
	public function settlement(){
		if(IS_POST){
			$orderNo = 'CT'.time().'P'.input('subprice');
			$data['create_card_id'] = input('cardman');
			$data['clear_card_id'] = input('cardman');
			$data['price'] = input('subprice');
			$data['order_id'] = $orderNo;
			$data['create_time'] = time();
			$data['clear_time'] = time();
			$data['status'] = 1;
			$data['departmentId'] = 2;
			$data['site_id'] = '88';//餐厅场地ID
			$data['operate_id'] = session('user_auth.uid');

			$cookfood = input('subfood');
			$cookfood = trim($cookfood,',');
			$cookArr = explode(',',$cookfood);

			$price = (float)input('subprice');
			//return $price;
			//是否为员工卡
			$staffInfo = db('member')->where('status=1 and cardNo='.input('cardman'))->find();
			//return $staffInfo;
			if(!$staffInfo){
				//是否为学生卡
				$stuInfo = db('students')->where('status=1 and bindCardNo='.input('cardman'))->find();
				//return $stuInfo;
				if(!$stuInfo){
					$info['code'] = 0;
					$info['msg'] = '未绑定卡号';
					return $info;
				}else{
					//学生卡结算
					//判断余额
					$data['type'] = 1;
					$data['uid'] = $stuInfo['id'];
					if($stuInfo['recharge'] < $price){
						$info['code'] = -1;
						$info['msg'] = '账户余额不足';
						return $info;
					}else{
						//账户中减去相应的金额
						$rs = db('students')->where('status=1 and bindCardNo='.input('cardman'))->setDec('recharge',$price);
					if(!$rs){
							return $this->error($link->getError());
						}
					}
				}
			}else{
				//员工卡结算
				//判断公司账户余额
				$data['type'] = 2;
				$data['uid'] = $staffInfo['uid'];
				if($staffInfo['companyRecharge'] >= $price){
					//账户中减去相应的金额
					$rs = db('member')->where('status=1 and cardNo='.input('cardman'))->setDec('companyRecharge',$price);
					if(!$rs){
						return $this->error($link->getError());
					}
				}else{
					//如果公司账户余额不足，判断个人账户余额
					if($staffInfo['personalRecharge'] >= $price){
						//个人账户中减去相应的金额
						$rs = db('member')->where('status=1 and cardNo='.input('cardman'))->setDec('personalRecharge',$price);
						if(!$rs){
							return $this->error($link->getError());
						}
					}else{
						$info['code'] = -3;
						$info['msg'] = '账户余额不足';
						return $info;
					}
				}

			}
			//插入订单表
			$result = db('order')->insertGetId($data);
			if(!$result){
				return $this->error($link->getError());
			}
			//return $orderinfo;
			//插入订单详情表
			for($i=0;$i<count($cookArr);$i++){
				$datas['title'] = $cookArr[$i];
				$datas['order_id'] = $result;
				$datas['count'] = 1;
				$datas['price'] = input('subprice');
				$rs = db('order_detail')->insert($datas);
				if(!$rs){
					return $this->error($link->getError());
				}
			}
			return $this->success("支付成功");
			//$info['code'] = 1;
			//$info['msg'] = '支付成功';
			//return $info;
			
		}
	}
	//物料申请
	public function purchase(){
		if(IS_POST){
			$data = input('post.data');
			$data = json_decode($data,true);
			//return $data;
			for($i=0;$i<count($data);$i++){
				$data[$i]['addtime'] = time();
				$data[$i]['departmentId'] = 2;
				$secendC = db('classify_2')->where('id='.$data[$i]['secendC'])->find();
				$spec = db('spec')->where('id='.$secendC['spec_id'])->find();
				$data[$i]['spec'] = $spec['id'];

				$result = db('purchase')->insert($data[$i]);
				if(!$result){
					return $this->error($link->getError());
				}
			}
			return $this->success("物料申请添加成功！");
		}
		$classify = db('classify')->select();
		//dump($classify);
		$branch = db('branch')->where('id=1 and status=1')->find();
		$department = db('department')->where('id=2 and status=1')->find();
		//已提交的计划
		$list = db('purchase')->where('departmentId=2')->order('addtime desc')->paginate(10);
		$lists = $list->all();
		foreach ($lists as $k =>$v){
			$v['firstC'] = db('classify')->where('id='.$v['firstC'])->value('title');
			$v['secendC'] = db('classify_2')->where('id='.$v['secendC'])->value('title');
			$v['spec'] = db('spec')->where('id='.$v['spec'])->value('title');
			$list[$k] = $v;
		};
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);

		//dump($purchase);
		$this->assign('branch',$branch);
		$this->assign('department',$department);
		$this->assign('classify',$classify);
		$this->setMeta("物料申请");
		return $this->fetch();
	}
	//获取二级分类
	public function getC2(){
		if(IS_POST){
			$id = input('id');
			$classify2 = db('classify_2')->where('pid='.$id)->select();
			for($i=0;$i<count($classify2);$i++){
				$spec = db('spec')->where('id='.$classify2[$i]['spec_id'])->find();
				$classify2[$i]['spec_id'] = $spec['title'];
			}
			$ajaxReturn['pid'] = input('pid');
			$ajaxReturn['classify2'] = $classify2;
			return $ajaxReturn;
		}
	}
	//获取规格
	public function getSpec(){
		if(IS_POST){
			$id = input('id');

			$secendC = db('classify_2')->where('id='.$id)->find();
			$spec = db('spec')->where('id='.$secendC['spec_id'])->find();
			$spec = $spec['title'];
			$ajaxReturn['pid'] = input('pid');
			$ajaxReturn['spec'] = $spec;
			return $ajaxReturn;
		}
	}
	//物料申请编辑
	public function purchaseedit(){
		$id   = input('id', '', 'trim,intval');
		if(IS_POST){
			$data = input('post.');
			//dump($data);die;
			if ($data) {
				$result = db('purchase')->update($data, array('id' => $data['id']));
				if ($result) {
					return $this->success("修改成功！", url('food/purchase'));
				} else {
					return $this->error("修改失败！");
				}
			} else {
				return $this->error($link->getError());
			}
			//dump($data);die;
		}else{
			$map  = array('id' => $id);
			$info = db('purchase')->where($map)->find();
			$this->assign('info',$info);
			$this->setMeta("编辑物料申请");
			return $this->fetch();
		}
	}
	//物料申请删除
	public function purchasedelete() {
		$id = $this->getArrayParam('id');
		if (empty($id)) {
			return $this->error('非法操作！');
		}
		$link = db('purchase');

		$map    = array('id' => array('IN', $id));
		$result = $link->where($map)->delete();
		if ($result) {
			return $this->success("删除成功！");
		} else {
			return $this->error("删除失败！");
		}
	}
	//营销报表
	public function marketinglist(){
		//dump($cook);die;
		//获取本周全部餐厅订单(测试用本周)
		$beginLastWeek = mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));
		$endLastWeek = mktime(23,59,59,date('m'),date('d')-date('w')+7,date('Y'));
		$map['site_id'] = '88';
		$map['clear_time'] = array('BETWEEN',array($beginLastWeek,$endLastWeek));
		//本周订单
		$lastWeekOrder = db('order')->where($map)->select();
		$orderDetailArr = [];
		for($i=0;$i<count($lastWeekOrder);$i++){
			$orderDetail = db('order_detail')->where('order_id='.$lastWeekOrder[$i]['id'])->select();
			if($orderDetail){
				for($j=0;$j<count($orderDetail);$j++){
					$orderDetail[$j] = $orderDetail[$j]['title'];
					array_push($orderDetailArr,$orderDetail[$j]);
				}
			}
		}
		//获取本周各菜品总点击次数
		$countArr = array_count_values($orderDetailArr);
		$countArr = array_keys($countArr);
		//全部菜品ID
		$cookId = db('cookfood')->where(array('status'=>1))->select();
		for($i=0;$i<count($cookId);$i++){
			$cookId[$i] = $cookId[$i]['id'];
		}
		if(count($countArr)<9){
			$num = 9-count($countArr);
			//差集
			$intersection = array_diff($cookId,$countArr);
			$mArr = array_slice($intersection,0,$num);
			$countArr = array_merge($countArr,$mArr);
		}
		$countArr = array_slice($countArr,0,9);
		//dump($countArr);die;

		//周一的点击次数
		$mondayOrder = $this->dateClickNum(1,$countArr);

		//周二的点击次数
		$tuesdayOrder = $this->dateClickNum(2,$countArr);

		//周三的点击次数
		$wednesdayOrder = $this->dateClickNum(3,$countArr);

		//周四的点击次数
		$thursdayOrder =  $this->dateClickNum(4,$countArr);

		//周五的点击次数
		$fridayOrder =  $this->dateClickNum(5,$countArr);

		//周六的点击次数
		$saturdayOrder =  $this->dateClickNum(6,$countArr);

		//周日的点击次数
		$sundayOrder =  $this->dateClickNum(7,$countArr);

		//dump($countArr);
		//dump($thursdayOrder);die;
		for($k=0;$k<count($countArr);$k++){
			$arr[$k]['cookfoodId'] = $countArr[$k];
			$names = db('cookfood')->where('id='.$arr[$k]['cookfoodId'])->find();
			$arr[$k]['cookfoodName'] = $names['foodname'];
		}
		for($w=0;$w<count($arr);$w++){
			$arr[$w]['clickNum'] = [];
			array_push($arr[$w]['clickNum'],$mondayOrder[$arr[$w]['cookfoodId']]);
			array_push($arr[$w]['clickNum'],$tuesdayOrder[$arr[$w]['cookfoodId']]);
			array_push($arr[$w]['clickNum'],$wednesdayOrder[$arr[$w]['cookfoodId']]);
			array_push($arr[$w]['clickNum'],$thursdayOrder[$arr[$w]['cookfoodId']]);
			array_push($arr[$w]['clickNum'],$fridayOrder[$arr[$w]['cookfoodId']]);
			array_push($arr[$w]['clickNum'],$saturdayOrder[$arr[$w]['cookfoodId']]);
			array_push($arr[$w]['clickNum'],$sundayOrder[$arr[$w]['cookfoodId']]);
		}
		//dump($arr);die;
		$maps['in_time'] = array('BETWEEN',array($beginLastWeek,$endLastWeek));
		$storageNum = db('dp_product')->where($maps)->select();
		$storageNum = JSON_encode($storageNum);
		//dump($storageNum);

		$this->assign('storageNum',$storageNum);
		$this->assign('cook',$arr);

		$this->setMeta("营销报表");
		return $this->fetch();
	}

	public function dateClickNum($dates,$finalArr){
		$starts = mktime(0,0,0,date('m'),date('d')-date('w')+$dates,date('Y'));
		$end = mktime(23,59,59,date('m'),date('d')-date('w')+$dates,date('Y'));
		$maps_1['site_id'] = '88';
		$maps_1['clear_time'] = array('BETWEEN',array($starts,$end));
		$order = db('order')->where($maps_1)->select();
		$orderDetailArr = [];
		for($i=0;$i<count($order);$i++){
			$orderDetail = db('order_detail')->where('order_id='.$order[$i]['id'])->select();
			if($orderDetail){
				for($j=0;$j<count($orderDetail);$j++){
					$orderDetail[$j] = $orderDetail[$j]['title'];
					array_push($orderDetailArr,$orderDetail[$j]);
				}
			}
		}
		$countArrs = array_count_values($orderDetailArr);
		$key = array_keys($countArrs);
		if(count($key)<9){
			$num = 9-count($key);
			//差集
			$intersection = array_diff($finalArr,$key);
			foreach($intersection as $k=>$v){
				$countArrs[$v] = 0;
			}
			//return $intersection;
		}
		return $countArrs;
	}
	//下周菜谱
	public function foodplan(){
		if(IS_POST){
			$data = input('post.cList');
			$data = json_decode($data,true);
			$monday = mktime(0,0,0,date('m'),date('d')-date('w')+1+7,date('Y'));
			$sunday = mktime(23,59,59,date('m'),date('d')-date('w')+7+7,date('Y'));
			$maps['date'] = array('BETWEEN', array($monday, $sunday));
			$cList = db('foodplan')->where($maps)->delete();
			if ($data) {
				//unset($data['id']);
				for($i=0;$i<count($data);$i++){
					$data[$i]['date1'] = $data[$i]['date'];
					$data[$i]['date'] = strtotime($data[$i]['date']);
					$data[$i]['cookId'] = trim($data[$i]['cookId'],',');
					$result = db('foodplan')->insert($data[$i]);

					$cookArr = explode(',',$data[$i]['cookId']);
					for($j=0;$j<count($cookArr);$j++){
						$info['foodname'] = db('cookfood')->where('id='.$cookArr[$j])->value('foodname');
						$info['date'] = $data[$i]['date1'];
						$info['mealtime'] = $data[$i]['mealtime'];
						$re = db('foodplan2')->insert($info);
						if(!$re){
							return $this->error($link->getError());
						}
					}

					if(!$result){
						return $this->error($link->getError());
					}
				}
				return $this->success("操作成功！", url('food/foodplan'));
			} else {
				return $this->error($link->getError());
			}
		}

		$monday = mktime(0,0,0,date('m'),date('d')-date('w')+1+7,date('Y'));
		$tuesday = mktime(0,0,0,date('m'),date('d')-date('w')+2+7,date('Y'));
		$wednesday = mktime(0,0,0,date('m'),date('d')-date('w')+3+7,date('Y'));
		$thursday  = mktime(0,0,0,date('m'),date('d')-date('w')+4+7,date('Y'));
		$friday  = mktime(0,0,0,date('m'),date('d')-date('w')+5+7,date('Y'));
		$saturday  = mktime(0,0,0,date('m'),date('d')-date('w')+6+7,date('Y'));
		$sunday = mktime(23,59,59,date('m'),date('d')-date('w')+7+7,date('Y'));

		$maps['date'] = array('BETWEEN', array($monday, $sunday));
		$cList = db('foodplan')->where($maps)->select();
		for($j=0;$j<count($cList);$j++){
			$cookArr = explode(',',$cList[$j]['cookId']);
			$cList[$j]['foodname'] = '';
			for($k=0;$k<count($cookArr);$k++){
				$name = db('cookfood')->where('id='.$cookArr[$k])->value('foodname');
				$cList[$j]['foodname'] .= ' '.$name;
			}
		}
		$cList = json_encode($cList);
		$cookfood = db('cookfood')->where('status=1')->select();

		$this->assign('cList',$cList);
		$this->assign('cook',$cookfood);
		$this->assign('monday',$monday);
		$this->assign('tuesday',$tuesday);
		$this->assign('wednesday',$wednesday);
		$this->assign('thursday',$thursday);
		$this->assign('friday',$friday);
		$this->assign('saturday',$saturday);
		$this->assign('sunday',$sunday);
		$this->setMeta("下周菜谱");
		return $this->fetch();
	}
	//餐厅订单
	public function foodorder(){
		$map = array();
		$query = array();
		$map['site_id'] = '88';
		$starttime = strtotime(input('starttime'));
		$endtime = strtotime(input('endtime'));
		if($starttime && $endtime){
			$map['create_time'] = array('BETWEEN',array($starttime,$endtime));
			$query['starttime'] = $starttime;
			$query['endtime'] = $endtime;
			//dump($map['addtime']);die;
		}

		$order = "id asc";
		$list  = db('order')->where($map)->order($order)->paginate(10,false,['query'=>$query]);
		$count = db('order')->where($map)->order($order)->count();
		$lists = $list->all();
		foreach ($lists as $k =>$v){
			$v['operate_id'] = db('member')->where('uid='.$v['operate_id'])->value('username');
			$detail = db('order_detail')->where('order_id='.$v['id'])->select();
			$nameStr = '';
			foreach($detail as $key => $val){
				$foodname = db('cookfood')->where('id='.$detail[$key]['title'])->value('foodname');
				$nameStr .= ' '.$foodname;
			}
			$v['foodname'] =$nameStr;
			$list[$k] = $v;
		};
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);
		$this->assign('count',$count);
		$this->setMeta("餐厅订单");
		return $this->fetch();
	}
	//菜谱预览
	public function menupreview(){
		$monday = mktime(0,0,0,date('m'),date('d')-date('w')+1+7,date('Y'));
		$tuesday = mktime(0,0,0,date('m'),date('d')-date('w')+2+7,date('Y'));
		$wednesday = mktime(0,0,0,date('m'),date('d')-date('w')+3+7,date('Y'));
		$thursday  = mktime(0,0,0,date('m'),date('d')-date('w')+4+7,date('Y'));
		$friday  = mktime(0,0,0,date('m'),date('d')-date('w')+5+7,date('Y'));
		$saturday  = mktime(0,0,0,date('m'),date('d')-date('w')+6+7,date('Y'));
		$sunday = mktime(23,59,59,date('m'),date('d')-date('w')+7+7,date('Y'));
		$maps['date'] = array('BETWEEN', array($monday, $sunday));
		$cList = db('foodplan')->where($maps)->select();
		for($j=0;$j<count($cList);$j++){
			$cookArr = explode(',',$cList[$j]['cookId']);
			$cList[$j]['foodname'] = '';
			for($k=0;$k<count($cookArr);$k++){
				$name = db('cookfood')->where('id='.$cookArr[$k])->value('foodname');
				$cList[$j]['foodname'] .= ' '.$name;
			}
		}
		$cList = json_encode($cList);
		$cookfood = db('cookfood')->where('status=1')->select();
		$this->assign('cList',$cList);
		$this->assign('cook',$cookfood);
		$this->assign('monday',$monday);
		$this->assign('tuesday',$tuesday);
		$this->assign('wednesday',$wednesday);
		$this->assign('thursday',$thursday);
		$this->assign('friday',$friday);
		$this->assign('saturday',$saturday);
		$this->assign('sunday',$sunday);
		return $this->fetch();
	}

	//入库物品列表
	public function storage() {

		$search_name = input('pro');
		$id_info = db('department')->where(array('name'=>'餐厅'))->find();
		$search = array();
		if($search_name){
			$search['title'] = array('like','%'.$search_name.'%');
			$search['departmentId'] = $id_info['id'];
		}else{
			$search['departmentId'] = $id_info['id'];
		}
		
		$count = db('dp_product')->where($search)->count();
		$order = "id desc";
		$list  = db('dp_product')->where($search)->order($order)->paginate(10,false,['query'=>array('pro'=>$search_name)]);

	    $page = $list->render();


		
		$this->assign('tree', $list);
		$this->assign('page', $page);
		$this->setMeta('产品列表');
		return $this->fetch();
	}

	//确认入库
	public function sure_in(){
		$search_name = input('pro');
		$type = input('type');
		
		$id_info = db('department')->where(array('name'=>'餐厅'))->find();
		$search = array();
		if($search_name){
			$search['person'] = array('like','%'.$search_name.'%');
			$search['departmentId'] = $id_info['id'];
		}elseif($type){
			if($type == 1){
				$search['status'] = 1;
				$search['departmentId'] = $id_info['id'];
			}else{
				$search['status'] = 0;
				$search['departmentId'] = $id_info['id'];
			}
		}else{
			$search['departmentId'] = $id_info['id'];
		}
		
		//var_dump($search);
		$count = db('dp_in_sto')->where($search)->count();
		$order = "id desc";
		$list  = db('dp_in_sto')->where($search)->order($order)->paginate(10,false,['query'=>array('pro'=>$search_name)]);

	    $page = $list->render();


		
		$this->assign('tree', $list);
		$this->assign('page', $page);
		$this->setMeta('产品列表');
		return $this->fetch();
	}
	//入库全部商品明细
	public function all_detail(){

		$list = db('out_sto_detail')->where('area=2')->order('out_time desc')->paginate(10);
		//dump($list_info);die;
		$data = array(
			'list' => $list,
			'page' => $list->render(),
		);
		$this->assign($data);
		$this->setMeta('全部入库物料明细');
		return $this->fetch();
	}


	//确认入库明细列表
	public function in_detail(){
		$id = input('id');
		$info = db('dp_in_sto')->where(array('id'=>$id))->find();
		$info_detail = explode(",",$info['detail_id']);
		$list = array();
		for($i=0;$i<count($info_detail);$i++){
			$list_info = db('out_sto_detail')->where(array('id'=>$info_detail[$i]))->find();
			array_push($list,$list_info);
		}
		$pro = db('Product')->select();
		for($j=0;$j<count($list);$j++){
			for($m=0;$m<count($pro);$m++){
				if($list[$j]['title'] == $pro[$m]['title']){
					$list[$j]['bar_code'] =  $pro[$m]['bar_code'];
					$list[$j]['in_price'] =  $pro[$m]['in_price'];
					$list[$j]['out_price'] =  $pro[$m]['out_price'];
				}
			}
		}
		$this->assign('id',$id);
		$this->assign('list', $list);
		$this->setMeta('入库明细列表');
		return $this->fetch();
	}
	//确认,子系统进行入库操作
	public function sure_inSto(){
		$id = input('id');
		$info = db('dp_in_sto')->where(array('id'=>$id))->find();
		if($info['status'] == 0){
			$info_detail = explode(",",$info['detail_id']);
			$list = array();
			for($i=0;$i<count($info_detail);$i++){
				$list_info = db('out_sto_detail')->where(array('id'=>$info_detail[$i]))->find();
				array_push($list,$list_info);
			}
			$pro = db('Product')->select();
			for($j=0;$j<count($list);$j++){
				for($m=0;$m<count($pro);$m++){
					if($list[$j]['title'] == $pro[$m]['title']){
						$list[$j]['bar_code'] =  $pro[$m]['bar_code'];
						$list[$j]['in_price'] =  $pro[$m]['in_price'];
						$list[$j]['out_price'] =  $pro[$m]['out_price'];
						$list[$j]['firstC'] =  $pro[$m]['class1'];
					}
				}
			}
			for($n=0;$n<count($list);$n++){
				$dpInfo = db('dp_product')->where(array('title'=>$list[$n]['title'],'departmentId'=>$list[$n]['area']))->find();
				if($dpInfo){
					$data['title'] = $list[$n]['title'];
					$data['num'] = (int)$list[$n]['num'] + (int)$dpInfo['num'];
					$data['spec'] = $list[$n]['spec'];
					$data['firstC'] = $list[$n]['firstC'];
					$data['bar_code'] = $list[$n]['bar_code'];
					$data['in_price'] = $list[$n]['in_price'];
					$data['out_price'] = $list[$n]['out_price'];
					$data['departmentId'] = $list[$n]['area'];
					$data['in_time'] = $list[$n]['out_time'];

					$dp_product = db('dp_product')->where(array('title'=>$list[$n]['title'],'departmentId'=>$list[$n]['area']))->update($data);
				}else{
					$data['title'] = $list[$n]['title'];
					$data['num'] = $list[$n]['num'];
					$data['spec'] = $list[$n]['spec'];
					$data['firstC'] = $list[$n]['firstC'];
					$data['bar_code'] = $list[$n]['bar_code'];
					$data['in_price'] = $list[$n]['in_price'];
					$data['out_price'] = $list[$n]['out_price'];
					$data['departmentId'] = $list[$n]['area'];
					$data['in_time'] = $list[$n]['out_time'];

					$dp_product = db('dp_product')->insertGetId($data);
				}
				
				
			}
			if(false !== $dp_product){
				$change_sta = db('dp_in_sto')->where(array('id'=>$id))->update(['status'=>1]);
				if($change_sta > 0){
					$this->success('入库成功',url('foodstaff'));
				}			
			}
		}else{
			$this->success('您已确认过，不可重复确认！',url('sure_in'));
		}
		
	}

	//阈值管理列表页
	public function threshold(){
		$list = db('dp_product')->where(array('departmentId'=>2))->order('id','desc')->paginate(10);
		$page = $list->render();
		$this->assign('tree', $list);
		$this->assign('page', $page);
		$this->setMeta('阈值管理');
		return $this->fetch();
	}
	//阈值设置
	public function th_set(){
		$id = input('id');
		$down = input('down');
		$pro  = db('dp_product')->where(array('id'=>$id))->find();
		$info = db('threshold')->where(array('departmentId'=>2,'pro_id'=>$id))->find();
		
		if($info){
			$up_th  = db('threshold')->where(array('departmentId'=>2,'pro_id'=>$id))->update(['down'=>$down]);
			$up_pro = db('dp_product')->where(array('id'=>$id))->update(['down'=>$down]);
			if(false !== $up_th && false !== $up_pro){
				$data['code'] = 1;
				$data['msg'] = '设置成功！';
			}else{
				$data['code'] = -1;
				$data['msg'] = '设置失败！';
			} 
		}else{
			$datas['departmentId'] = 2;
			$datas['down'] = $down;
			$datas['pro_id'] = $id;
			$add_th = db('threshold')->insertGetId($datas);
			$up_pro = db('dp_product')->where(array('id'=>$id))->update(['down'=>$down]);
			if($add_th > 0 && false !== $up_pro){
				$data['code'] = 1;
				$data['msg'] = '设置成功！';
			}else{
				$data['code'] = -1;
				$data['msg'] = '设置失败！';
			}

		}
		return json($data);
	}
	//库存量编辑
	public function inventoryEdit(){
		$id   = input('id', '', 'trim,intval');
		if(IS_POST){
			$data = input('post.');
			//dump($data);die;
			if ($data) {
				$result = db('dp_product')->update($data, array('id' => $data['id']));
				if ($result) {
					return $this->success("修改成功！", url('food/storage'));
				} else {
					return $this->error("修改失败！");
				}
			} else {
				return $this->error($link->getError());
			}
			//dump($data);die;
		}else{
			$map  = array('id' => $id);
			$info = db('dp_product')->where($map)->find();
			$this->assign('info',$info);
			$this->setMeta('库存量编辑');
			return $this->fetch();
		}
	}


}