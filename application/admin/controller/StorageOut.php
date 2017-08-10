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

class StorageOut extends Admin {

	public function _initialize() {
		parent::_initialize();
		$this->getContentMenu();
	}
	//产品列表
	public function index() {
		//$map  = array('pid' => array('eq', 0));
		$list = db('product')->order('id','desc')->paginate(10);
		$page = $list->render();
		$this->assign('tree', $list);
		$this->assign('page', $page);
		$this->setMeta('产品列表');
		$classify1 = db('classify')->select();
		$p_Classify = db('ClassifyModel1')->where(array('area_id'=>'wy','status'=>0))->select();

		for($i=0;$i<count($p_Classify);$i++){
			for($j=0;$j<count($classify1);$j++){
				if($p_Classify[$i]['pid'] ==  $classify1[$j]['id']){
					$p_Classify[$i]['title'] = $classify1[$j]['title'];
				}
			}
		}
		$this->assign('p_Classify', $p_Classify);
		$this->assign('info', null);
		
        return $this->fetch('index');
        
	}
	//入库明细列表
	public function detail(){
		$list = db('in_storage_setail')->order('id','desc')->paginate(10);
		$page = $list->render();
		$this->assign('tree', $list);
		$this->assign('page', $page);
		$this->setMeta('入库明细');
		return $this->fetch();
	}
	
	/* 产品出库 同时生成出库记录 */
	public function add() {
		
		if (IS_POST) {
			$ProductOut = model('ProductOut');
			//提交表单出库减少库存
			$id = $ProductOut->addproduct();

			$OutStorageSetail = model('OutStorageSetail');
			//提交表单出库明细
			$ids = $OutStorageSetail->change();
			if (false !== $id && false !== $ids) {
				//action_log('update_category', 'category', $id, session('user_auth.uid'));
				return $this->success('新增成功！', url('index'));
			} else {
				$error = $Classify->getError();
				return $this->error(empty($error) ? '未知错误！' : $error);
			}
		} else {
			$p_Classify = db('classify')->order('id','asc')->column('*', 'id');
			$this->assign('p_Classify', $p_Classify);
			$spec = db('spec')->select();
			$this->assign('spec',$spec);
			$this->assign('info', null);
			$this->setMeta('新增分类');
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
	/**
	 * 查找二级分类AJAX返回
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function find_classily(){
		$id = input('post.id');
		$classify_info = db('classify_2')->where('pid='.$id)->select();
		return json($classify_info);

	}
	/**
	 * 查找商品信息AJAX返回
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function find_search(){
		$id = input('post.id');
		$info = db('Product')->where('id='.$id)->find();
		return json($info);

	}
	/**
	 * 搜索出库产品AJAX返回
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function find_product(){
		$classify = $_POST['classfy'];
		$title = $_POST['title'];
		if($classify && !$title){
			$info = db('ClassifyModel2')->where('id='.$classify)->find();
			$infos = db('Product')->where('class2='.$info['sid'])->select();
			$data['code'] = 1;
			$data['body'] = $infos;
		}
		if($title && !$classify){
			$infos = db('Product')->where('title','like','%'.$title.'%')->select();
			$data['code'] = 1;
			$data['body'] = $infos;
		}
		if($classify && $title){
			$info = db('ClassifyModel2')->where('id='.$classify)->find();
			$infos = db('Product')->where(array('title' => ['like', '%'.$title.'%'],'class2'=>$info['sid']))->select();
		    $data['code'] = 1;
			$data['body'] = $infos;
		}
		if(!$classify && !$title){
			$data['code'] = 0;
			$data['msg'] = '请选择或出入搜索条件';
		}
		return json($data);

	}
	/**
	 * 进行出库操作AJAX返回结果
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function storage_out(){
		$title = $_POST['title'];
		$num = $_POST['num'];
		$spec = $_POST['spec'];
		$person = $_POST['person'];
		$time = $_POST['time'];
		$Product = model('Product');
		$info = db('Product')->select();
		$order = array();
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
						$result = $Product->outproduct($title[$i],$num[$i]);
						$datas['title'] = $title[$i];
						$datas['num'] = $num[$i];
						$datas['spec'] = $spec[$i];
						$datas['out_person'] = $person;
						if($time == '' && $time == null){
							$datas['out_time'] = time();
						}else{
							$datas['out_time'] = strtotime($time);
						}
						$results = db('outStoDetail')->insertGetId($datas);
						array_push($order,$results);
						if(false !== $result && false !== $results){
							$data['msg'] = '成功出库';
							$data['title'] = '';
							$data['code'] = 1;
						}
					}					
				}
			}
		}
		$map['out_person'] = $person;
		if($time == '' && $time == null){
			$map['out_time'] = time();
		}else{
			$map['out_time'] = strtotime($time);
		}
		$map['pro_id'] = implode(',',$order);
		$in_order = db('outStoOrder')->insertGetId($map);
		if($in_order > 0){
			$data['info'] = 1;
		}else{
			$data['info'] = 0;
		}
		return json($data);

	}
	/**
	 * 操作分类初始化
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function operate($type = 'move', $from = '') {
		//检查操作参数
		if ($type == 'move') {
			$operate = '移动';
		} elseif ($type == 'merge') {
			$operate = '合并';
		} else {
			return $this->error('参数错误！');
		}

		if (empty($from)) {
			return $this->error('参数错误！');
		}
		//获取分类
		$map  = array('status' => 1, 'id' => array('neq', $from));
		$list = db('Category')->where($map)->field('id,pid,title')->select();
		//移动分类时增加移至根分类
		if ($type == 'move') {
			//不允许移动至其子孙分类
			$list = tree_to_list(list_to_tree($list));

			$pid = db('Category')->getFieldById($from, 'pid');
			$pid && array_unshift($list, array('id' => 0, 'title' => '根分类'));
		}

		$this->assign('type', $type);
		$this->assign('operate', $operate);
		$this->assign('from', $from);
		$this->assign('list', $list);
		$this->setMeta($operate . '分类');
		return $this->fetch();
	}
	/**
	 * 移动分类
	 * @author huajie <banhuajie@163.com>
	 */
	public function move() {
		$to   = input('post.to');
		$from = input('post.from');
		$res  = db('Category')->where(array('id' => $from))->setField('pid', $to);
		if ($res !== false) {
			return $this->success('分类移动成功！', url('index'));
		} else {
			return $this->error('分类移动失败！');
		}
	}
	/**
	 * 合并分类
	 * @author huajie <banhuajie@163.com>
	 */
	public function merge() {
		$to    = input('post.to');
		$from  = input('post.from');
		$Model = model('Category');
		//检查分类绑定的模型
		$from_models = explode(',', $Model->getFieldById($from, 'model'));
		$to_models   = explode(',', $Model->getFieldById($to, 'model'));
		foreach ($from_models as $value) {
			if (!in_array($value, $to_models)) {
				return $this->error('请给目标分类绑定' . get_document_model($value, 'title') . '模型');
			}
		}
		//检查分类选择的文档类型
		$from_types = explode(',', $Model->getFieldById($from, 'type'));
		$to_types   = explode(',', $Model->getFieldById($to, 'type'));
		foreach ($from_types as $value) {
			if (!in_array($value, $to_types)) {
				$types = config('document_model_type');
				return $this->error('请给目标分类绑定文档类型：' . $types[$value]);
			}
		}
		//合并文档
		$res = db('Document')->where(array('category_id' => $from))->setField('category_id', $to);

		if ($res !== false) {
			//删除被合并的分类
			$Model->delete($from);
			return $this->success('合并分类成功！', url('index'));
		} else {
			return $this->error('合并分类失败！');
		}
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