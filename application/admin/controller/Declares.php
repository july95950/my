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

class Declares extends Admin {

	public function _initialize() {
		parent::_initialize();
		$this->getContentMenu();
	}
	//产品列表
	public function index() {
		//$map  = array('pid' => array('eq', 0));
		$list = db('purchase')->where('status=0')->group('secendC')->select();
		//$list = db('purchase')->where('status=0')->group('secendC')->paginate(10);
		$classify2 = db('classify_2')->select();
		$pro = db('Product')->select();
		$threshold = db('threshold')->where('departmentId=12')->select();
		for($i=0;$i<count($list);$i++){
			$list[$i]['all_num'] = db('purchase')->where(array('secendC'=>$list[$i]['secendC'],'status'=>0))->sum('num');
			
			for($j=0;$j<count($classify2);$j++){
				if($list[$i]['secendC'] == $classify2[$j]['id']){
					$list[$i]['title'] = $classify2[$j]['title'];
				}
			}
			for($m=0;$m<count($pro);$m++){
				if($list[$i]['title'] == $pro[$m]['title']){
					$list[$i]['inventory'] = $pro[$m]['num'];
					$list[$i]['pro_id'] = $pro[$m]['id'];
				}
			}
			/*$arrt = array();
			for($p=0;$p<count($threshold);$p++){
				array_push($arrt,$threshold[$p]['pro_id']);
			}
			$arrl = array();
			for($q=0;$q<count($list);$q++){
				array_push($arrl,$list[$q]['pro_id']);
			}*/
			for($n=0;$n<count($threshold);$n++){
				if($list[$i]['pro_id'] == $threshold[$n]['pro_id']){
					$list[$i]['down'] = $threshold[$n]['down'];
				}


			}
			$jh = $list[$i]['all_num'] + $list[$i]['down'] - $list[$i]['inventory'];
			if($jh<=0){
				$list[$i]['jihua'] = 0;
			}else{
				$list[$i]['jihua'] = $jh;
			}
		}
		
		//var_dump($list);die;
		$this->assign('tree', $list);
		//$this->assign('page', $page);
		$this->setMeta('采购申报管理');
		return $this->fetch();
	}
	//查看采购计划各部门提出数量详情
	public function detail(){
		$secendC = input('secendC');
		$title = input('title');
		$search = array();
		$list = db('purchase')->where(array('secendC'=>$secendC,'status'=>0))->group('departmentId')->order('addtime','desc')->select();
		$department = db('department')->select();
		for($i=0;$i<count($list);$i++){
			for($j=0;$j<count($department);$j++){
				if($list[$i]['departmentId'] == $department[$j]['id']){
					$list[$i]['title'] = $department[$j]['name'];
				}
				
			}
			$list[$i]['num_all'] = db('purchase')->where(array('secendC'=>$secendC,'departmentId'=>$list[$i]['departmentId'],'status'=>0))->sum('num');
			

		}
		//var_dump($list);die;
		/*if($search_name){
			$search['title'] = array('like','%'.$search_name.'%');
		}
		
		$count = db('in_storage_setail')->where($search)->count();
		$order = "id desc";
		$list  = db('in_storage_setail')->where($search)->order($order)->paginate(10,false,['query'=>array('pro'=>$search_name)]);

	    $page = $list->render();*/


	    $this->assign('title', $title);
		$this->assign('tree', $list);
		//$this->assign('page', $page);
		$this->setMeta('入库明细');
		return $this->fetch();
	}
	
	/* 确认采购计划 */
	public function add() {
		$title_arr = $_POST['title_arr'];//提交外购表	
		$title_all = $_POST['title_all'];//需改变状态的	
		$num_arr = $_POST['num_arr'];
		$classify2 = db('classify_2')->select();
		$secendC_id = array();
		/*改变状态*/
		for($i=0;$i<count($title_all);$i++){
			for($j=0;$j<count($classify2);$j++){
				if($title_all[$i] == $classify2[$j]['title']){
					array_push($secendC_id,$classify2[$j]['id']); 
				}
			}
		}
		for($j=0;$j<count($secendC_id);$j++){
			$info = db('purchase')->where(array('secendC'=>$secendC_id[$j],'status'=>0))->select();
			for($m=0;$m<count($info);$m++){
				$update_change = db('purchase')->where(array('id'=>$info[$m]['id']))->update(['status'=>1]);
			}
		}
		/*改变状态*/

		/*添加外购表*/
		for($n=0;$n<count($title_arr);$n++){
			$data['title'] = $title_arr[$n];
			$data['num'] = $num_arr[$n];
			$data['addtime'] = time();
			$add_change = db('out_purchase')->insertGetId($data);
		}
		/*添加外购表*/
		if($update_change && $add_change){
			$infodata['msg'] = 1;
		}else{
			$infodata['msg'] = 0;
		}
		
		return $infodata;	
	}
	/**
	 * 产品删除
	 * @author huajie <banhuajie@163.com>
	 */
	public function remove($id) {
		if (empty($id)) {
			return $this->error('参数错误!');
		}
		//判断该分类下有没有子分类，有则不允许删除
		$child = db('classify_2')->where(array('pid' => $id))->field('id')->select();
		if (!empty($child)) {
			return $this->error('请先删除该分类下的子分类');
		}
		//删除该分类信息
		$res = db('classify')->where(array('id' => $id))->delete();
		if ($res !== false) {
			//记录行为
			//action_log('update_category', 'category', $id, session('user_auth.uid'));
			return $this->success('删除分类成功！');
		} else {
			return $this->error('删除分类失败！');
		}
	}
	
	/**
	 * 对外采购
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function out_buy(){
		$search_name = input('pro');
		$search = array();
		if($search_name){
			$search['title'] = array('like','%'.$search_name.'%');
			$search['type'] = 0;
		}else{
			$search['type'] = 0;
		}
		
		$count = db('out_purchase')->where($search)->count();
		$order = "id desc";
		$list  = db('out_purchase')->where($search)->order($order)->paginate(10,false,['query'=>array('pro'=>$search_name)]);
		$page = $list->render();

		$this->assign('tree',$list);
		$this->assign('page',$page);
		return $this->fetch();
	}
	/**
	 * 确认对外采购
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function sure_outBuy(){
		$id = input('id');
		$type = input('type');
		if($type == 0){
			$info = db('out_purchase')->where(array('id'=>$id))->update(['status'=>1]);
		}else{
			$info = db('out_purchase')->where(array('id'=>$id))->update(['status'=>0]);
		}
		
		if($info){
			$this->redirect('declares/out_buy');
		}else{
			$this->error('失败！');
		}
	}
	/**
	 * 查找规格ajax返回
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function find_spec(){
		$title = input('post.classify2');
		$info = db('classify_2')->where(array('title'=>$title))->find();
		if($info['spec_id']){
			$specinfo = db('spec')->where(array('id'=>$info['spec_id']))->find();
		}else{
			$specinfo['title'] = ''; 
		}
		
		return json($specinfo);

	}
	public function status() {
		$id     = $this->getArrayParam('id');
		$status = input('status', '0', 'trim,intval');

		if (!$id) {
			return $this->error("非法操作！");
		}

		$map['id'] = array('IN', $id);
		$result    = db('Category')->where($map)->setField('status', $status);
		if ($result) {
			return $this->success("设置成功！");
		} else {
			return $this->error("设置失败！");
		}
	}
}


