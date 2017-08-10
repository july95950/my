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

class Inventory extends Admin {

	public function _initialize() {
		parent::_initialize();
		$this->getContentMenu();
	}
	//产品列表
	public function index() {
		//$map  = array('pid' => array('eq', 0));
		// Step 1" 预先取出本部门设定好的全部阈值
		$threshold = db('threshold')->where('departmentId=12')->select();

		// Step2 每次分页后当前的各个物品，依次在阈值数组里尝试匹配
		$list = db('product')->order('id','desc')->paginate(10);
		/*for($i=0;$i<count($list);$i++){
			
		}*/
		$page = $list->render();
		$this->assign('tree', $list);
		$this->assign('page', $page);
		$this->setMeta('产品列表');
		return $this->fetch();
	}
	//阈值管理列表页
	public function threshold(){
		$list = db('product')->order('id','desc')->paginate(10);
		$page = $list->render();
		$this->assign('tree', $list);
		$this->assign('page', $page);
		$this->setMeta('产品列表');
		return $this->fetch();
	}
	//阈值设置
	public function th_set(){
		$id = input('id');
		$down = input('down');
		$pro  = db('Product')->where(array('id'=>$id))->find();
		$info = db('threshold')->where(array('departmentId'=>12,'pro_id'=>$id))->find();
		
		if($info){
			$up_th  = db('threshold')->where(array('departmentId'=>12,'pro_id'=>$id))->update(['down'=>$down]);
			$up_pro = db('Product')->where(array('id'=>$id))->update(['down'=>$down]);
			if(false !== $up_th && false !== $up_pro){
				$data['code'] = 1;
				$data['msg'] = '设置成功！';
			}else{
				$data['code'] = -1;
				$data['msg'] = '设置失败！';
			} 
		}else{
			$datas['departmentId'] = 12;
			$datas['down'] = $down;
			$datas['pro_id'] = $id;
			$add_th = db('threshold')->insertGetId($datas);
			$up_pro = db('Product')->where(array('id'=>$id))->update(['down'=>$down]);
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
	//入库明细列表
	public function detail(){
		$search_name = input('pro');
		$search = array();
		if($search_name){
			$search['title'] = array('like','%'.$search_name.'%');
		}
		
		$count = db('in_storage_setail')->where($search)->count();
		$order = "id desc";
		$list  = db('in_storage_setail')->where($search)->order($order)->paginate(10,false,['query'=>array('pro'=>$search_name)]);

	    $page = $list->render();



		$this->assign('tree', $list);
		$this->assign('page', $page);
		$this->setMeta('入库明细');
		return $this->fetch();
	}
	/* 编辑产品 */
	public function edit($id = null, $pid = 0) {
		if (IS_POST) {
			$Product = model('Product');
			//提交表单
			$result = $Product->changeEdit();

			$InStorageSetail = model('InStorageSetail');
			//提交表单添加明细
			$results = $InStorageSetail->changeEdit();
			if (false !== $result) {
				//记录行为
				//action_log('update_category', 'category', $id, session('user_auth.uid'));
				return $this->success('编辑成功！', url('index'));
			} else {
				$error = $Classify->getError();
				return $this->error(empty($error) ? '未知错误！' : $error);
			}
		} else {
			$info = array();
			/* 获取数据 */
			$info = db('product')->find($id);
			$p_Classify = db('ClassifyModel1')->where(array('area_id'=>'wy','status'=>0))->select();
			$classify1 = db('classify')->select();
			for($i=0;$i<count($p_Classify);$i++){
				for($j=0;$j<count($classify1);$j++){
					if($p_Classify[$i]['pid'] ==  $classify1[$j]['id']){
						$p_Classify[$i]['title'] = $classify1[$j]['title'];
					}
				}
			}
			$this->assign('p_Classify', $p_Classify);

			$s_Classify = db('ClassifyModel2')->where(array('area_id'=>'wy','status'=>0))->select();
			$classify2 = db('classify_2')->select();
			for($m=0;$m<count($s_Classify);$m++){
				for($n=0;$n<count($classify2);$n++){
					if($s_Classify[$m]['sid'] ==  $classify2[$n]['id']){
						$s_Classify[$m]['title'] = $classify2[$n]['title'];
					}
				}
			}
			$this->assign('s_Classify', $s_Classify);

			if (false === $info) {
				return $this->error('获取配置信息错误');
			}
			$spec = db('spec')->select();
			$this->assign('spec',$spec);
			$this->assign('info', $info);
			$this->setMeta('编辑分类');
			return $this->fetch('edit');
		}
	}
	
	/* 新增产品入库 同时生成入库记录 */
	public function add() {
		
		if (IS_POST) {
			//var_dump($_POSt);die;
			$Product = model('Product');
			//提交表单增加到库存
			
			$id = $Product->addproduct('wy');

			$InStorageSetail = model('InStorageSetail');
			//提交表单添加明细
			$ids = $InStorageSetail->changess('wy');
			if (false !== $id && false !== $ids) {
				//action_log('update_category', 'category', $id, session('user_auth.uid'));
				return $this->success('新增成功！', url('index'));
			} else {
				$error = $Classify->getError();
				return $this->error(empty($error) ? '未知错误！' : $error);
			}
		} else {
			$classify1 = db('classify')->select();

			$this->assign('classify1', $classify1);
			$spec = db('spec')->select();
			$this->assign('spec',$spec);
			$this->assign('info', null);
			$this->setMeta('入库产品');
			return $this->fetch('edit');
		}
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
	/**产品入库**/
	public function storage_in(){
		$title = $_POST['title'];
		$num = $_POST['num'];
		$spec = $_POST['spec'];
		$in_price = $_POST['in_price'];
		$out_price = $_POST['out_price'];
		$class1 = $_POST['class1'];
		//$class2 = $_POST['class2'];
		$in_time = $_POST['in_time'];
		$bar_code = $_POST['bar_code'];

		//$info = db('Product')->select();

		for($i=0;$i<count($title);$i++){
			$info = db('Product')->where(array('title'=>$title[$i]))->find();
			if($info){
				$data['num'] =  (int)$num[$i] + $info['num'] ;
				$data['title'] = $title[$i];
				$data['spec'] = $spec[$i];
				$data['in_price'] = $in_price[$i];
				$data['out_price'] = $out_price[$i];
				$data['class1'] = $class1[$i];
				//$data['class2'] = $class2[$i];
				if(empty($in_time[$i])){
					$data['in_time'] = time();
				}else{
					$data['in_time'] = strtotime($in_time[$i]);
				}
				
				$data['bar_code'] = $bar_code[$i];
				$in_pro = db('Product')->where(array('title'=>$data['title']))->update($data);
				$data['uid'] = session('user_auth.uid');
				$datas['title'] = $title[$i];
				$datas['spec'] = $spec[$i];
				$datas['in_price'] = $in_price[$i];
				$datas['out_price'] = $out_price[$i];
				$datas['num'] = (int)$num[$i];
				$datas['area_id'] = 'xz';
				if(empty($in_time[$i])){
					$datas['in_time'] = time();
				}else{
					$datas['in_time'] = strtotime($in_time[$i]);
				}
				$datas['uid'] = session('user_auth.uid');

				$in_detail = db('InStorageSetail')->insertGetId($datas);
				if($in_pro>0 && $in_detail>0){
						$infodata['code'] = 1;
						$infodata['msg'] = '成功入库';
				}
			}else{

				$data['num'] =  (int)$num[$i];
				$data['title'] = $title[$i];
				$data['spec'] = $spec[$i];
				$data['in_price'] = $in_price[$i];
				$data['out_price'] = $out_price[$i];
				$data['class1'] = $class1[$i];
				//$data['class2'] = $class2[$i];
				if(empty($in_time[$i])){
					$data['in_time'] = time();
				}else{
					$data['in_time'] = strtotime($in_time[$i]);
				}
				
				$data['bar_code'] = $bar_code[$i];
				$in_pro = db('Product')->insertGetId($data);

				$data['uid'] = session('user_auth.uid');
				$datas['title'] = $title[$i];
				$datas['spec'] = $spec[$i];
				$datas['in_price'] = $in_price[$i];
				$datas['out_price'] = $out_price[$i];
				$datas['num'] = (int)$num[$i];
				$datas['area_id'] = 'xz';
				if(empty($in_time[$i])){
					$datas['in_time'] = time();
				}else{
					$datas['in_time'] = strtotime($in_time[$i]);
				}

				$in_detail = db('InStorageSetail')->insertGetId($datas);
				$data_f['departmentId'] = 12;
				$data_f['pro_id'] = $in_pro;
				$data_f['down'] = 1;
				$data_f['up'] = 1;
				$thresholds = db('threshold')->insertGetId($data_f);

				if($in_pro>0 && $in_detail>0 && $thresholds>0){
						$infodata['code'] = 1;
						$infodata['msg'] = '成功入库';
				}
			}
			
		}
		return json($infodata);
	}
	/*产品出库 同时生成出库记录*/
	public function out_storage(){
		$classify1 = db('classify')->select();
		$this->assign('classify1', $classify1);
		$list = db('product')->order('id','desc')->paginate(10);
		$page = $list->render();
		$department = db('department')->where('status=1')->select();
		$this->assign('department',$department);
		$this->assign('tree', $list);
		$this->assign('page', $page);
		$this->assign('info', null);
		$this->setMeta('产品列表');
		return $this->fetch();
	}
	/**
	 * 查找二级分类AJAX返回
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function find_classily(){
		$id = input('post.id');
		$info = db('Classify_2')->where(array('pid'=>$id))->select();
		return json($info);

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
	/**
	 * 搜索出库产品AJAX返回
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function find_product(){
		$classify = $_POST['classfy'];
		$title = $_POST['title'];
		$search = array();
		if($classify && !$title){
			$info = db('Product')->where(array('title'=>$classify))->select();
			
		}
		if(!$classify && $title){
			$search['title'] = array('like','%'.$title.'%');
			$info = db('Product')->where($search)->select();
			
		}
		if($classify && $title){
			$search['title'] = array('like','%'.$title.'%');
			$info = db('Product')->where($search)->where(array('title'=>$classify))->select();
			
		}
		if(!$classify && !$title){
			$info = db('Product')->select();
			
		}
		
		return json($info);

	}

	/**
	 * 进行出库操作AJAX返回结果
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function out_storages(){
		$department = $_POST['department'];
		$title = $_POST['title'];
		$num = $_POST['num'];
		$spec = $_POST['spec'];
		$person = $_POST['person'];
		$time = $_POST['time'];
		$Product = model('Product');
		$info = db('Product')->select();
		$detail_arr = array();
		for($i=0;$i<count($title);$i++){
			for($j=0;$j<count($info);$j++){
				if($title[$i] == $info[$j]['title']){
					$num_all = $info[$j]['num'];
					if($num[$i] > $num_all){
						$data['msg'] = '库存不足';
						$data['title'] = $title[$i];
						$data['code'] = -1;
						return json($data);
					}else{
						
						//从总库存中减去相应库存
						$result = $Product->outproduct($title[$i],$num[$i]);
						$datas['title'] = $title[$i];
						$datas['num'] = $num[$i];
						$datas['spec'] = $spec[$i];
						$datas['out_person'] = $person;
						$datas['area'] = $department;
						if($time == '' && $time == null){
							$datas['out_time'] = time();
						}else{
							$datas['out_time'] = strtotime($time);
						}
						//插入出库明细表
						$results = db('outStoDetail')->insertGetId($datas);
						array_push($detail_arr,$results);
						
						if(false !== $result && false !== $results){
							$data['msg'] = '成功出库';
							$data['title'] = '';
							$data['code'] = 1;
						}
					}					
				}
			}
		}
		$detail_id = implode(',',$detail_arr);
		$data_detail['id_num'] = 'LQ'.time();
		$data_detail['person'] = $person;
		$data_detail['in_time'] = time();
		$data_detail['detail_id'] = $detail_id;
		$data_detail['departmentId'] = $department;
		$dp_in_sto = db('dp_in_sto')->insertGetId($data_detail);
		$map['out_person'] = $person;

		if($time == '' && $time == null){
			$map['out_time'] = time();
		}else{
			$map['out_time'] = strtotime($time);
		}
		$map['pro_id'] = implode(',',$detail_arr);
		
		$in_order = db('outStoOrder')->insertGetId($map);
		if($in_order > 0){
			$data['info'] = 1;
		}else{
			$data['info'] = 0;
		}
		return json($data);

	}
	//出库明细
	public function out_detail(){
		$search_name = input('pro');
		$search = array();
		if($search_name){
			$search['title'] = array('like','%'.$search_name.'%');
		}
		
		$count = db('out_sto_detail')->where($search)->count();
		$order = "id desc";
		$list  = db('out_sto_detail')->where($search)->order($order)->paginate(10,false,['query'=>array('pro'=>$search_name)]);

	    $page = $list->render();



		$this->assign('tree', $list);
		$this->assign('page', $page);
		$this->setMeta('出库明细');
		return $this->fetch();
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