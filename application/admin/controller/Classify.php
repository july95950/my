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

class Classify extends Admin {

	public function _initialize() {
		parent::_initialize();
		$this->getContentMenu();
	}
	//一级分类列表
	public function index() {
		$map  = array();
		$title = input('class1');
		if($title){
			$map['title'] = array('like','%'.$title.'%');
		}
		$count = db('Classify')->where($map)->count();
		$order = "id desc";
		$list  = db('Classify')->where($map)->order($order)->paginate(10,false,['query'=>array('class1'=>$title)]);
		$page = $list->render();
		$this->assign('tree', $list);
		$this->assign('page', $page);
		$this->setMeta('一级分类');
		return $this->fetch();
	}
	//二级分类列表
	public function indexs(){
		$map  = array();
		$title = input('class2');
		if($title){
			$map['title'] = array('like','%'.$title.'%');
		}
		$count = db('Classify_2')->where($map)->count();
		$order = "id desc";
		$classify1 = db('classify')->select();
		$list  = db('Classify_2')->where($map)->order($order)->paginate(10,false,['query'=>array('class2'=>$title)]);
		/*for($i=0;$i<count($list);$i++){
			for($j=0;$j<count($classify1);$j++){
				if($list[$i]['pid'] ==  $classify1[$j]['id']){
					$list[$i]['up_title'] = $classify1[$j]['title'];
				}
			}
		}*/
		$page = $list->render();
		//var_dump($list);
		$this->assign('tree', $list);
		$this->assign('page', $page);
		$this->setMeta('二级分类');
		return $this->fetch();
	}
	/* 编辑一级分类 */
	public function edit($id = null, $pid = 0) {
		if (IS_POST) {
			$Classify = model('Classify');
			//提交表单
			$result = $Classify->change();
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
			$info = db('classify')->find($id);
			
			if (false === $info) {
				return $this->error('获取配置信息错误');
			}
			$this->assign('info', $info);
			$this->setMeta('编辑分类');
			return $this->fetch('edit');
		}
	}
	/* 编辑二级分类 */
	public function edits($id = null, $pid = 0) {
		if (IS_POST) {
			$Classify = model('Classify_2');
			//提交表单
			$result = $Classify->change();
			if (false !== $result) {
				//记录行为
				//action_log('update_category', 'category', $id, session('user_auth.uid'));
				return $this->success('编辑成功！', url('index'));
			} else {
				$error = $Classify->getError();
				return $this->error(empty($error) ? '未知错误！' : $error);
			}
		} else {
			$p_Classify = db('classify')->order('id','asc')->column('*', 'id');
			$this->assign('p_Classify', $p_Classify);
			$info = array();
			/* 获取数据 */
			$info = db('classify_2')->find($id);
			
			if (false === $info) {
				return $this->error('获取配置信息错误');
			}
			$spec = db('spec')->select();

			$this->assign('spec',$spec);

			$this->assign('info', $info);
			$this->setMeta('编辑分类');
			return $this->fetch('edits');
		}
	}
	/* 新增一级分类 */
	public function add() {
		$Classify = model('Classify');
		if (IS_POST) {
			//提交表单
			$id = $Classify->change();
			if (false !== $id) {
				//action_log('update_category', 'category', $id, session('user_auth.uid'));
				return $this->success('新增成功！', url('index'));
			} else {
				$error = $Classify->getError();
				return $this->error(empty($error) ? '未知错误！' : $error);
			}
		} else {
			$this->assign('info', null);
			$this->setMeta('新增分类');
			return $this->fetch('edit');
		}
	}
	/* 新增二级分类 */
	public function adds() {
		$Classify_2 = model('Classify_2');
		$title = input('title');
		$class1 = input('pid');
		$spec = input('spec_id');
		$spec_title = db('spec')->where(array('id'=>$spec))->find();
		if (IS_POST) {
			//提交表单
			$id = $Classify_2->change();
			if (false !== $id) {
				$pro = db('Product')->where(array('title'=>$title))->find();
				if(!$pro){
					$data['title'] = $title;
					$data['num'] = 0;
					$data['bar_code'] = '';
					$data['spec'] = $spec_title['title'];
					$data['class1'] = $class1;
					$data['in_time'] = time();

					$in_pro = db('Product')->insertGetId($data);
					if($in_pro > 0){
						$datas['departmentId'] = 12;
						$datas['pro_id'] = $in_pro;
					}
					$in_th = db('threshold')->insertGetId($datas);
					if($in_th > 0){
						return $this->success('新增成功！', url('indexs'));
					}
					
				}
				
			} else {
				$error = $Classify->getError();
				return $this->error(empty($error) ? '未知错误！' : $error);
			}
		} else {
			$p_Classify = db('classify')->order('id','asc')->column('*', 'id');
			$this->assign('p_Classify', $p_Classify);
			//var_dump($p_Classify);
			$spec = db('spec')->select();
			$this->assign('spec',$spec);
			$this->assign('info', null);
			$this->setMeta('新增分类');
			return $this->fetch('edits');
		}
	}
	/**
	 * 删除一级分类
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
	 * 删除二级分类
	 * @author huajie <banhuajie@163.com>
	 */
	public function removes($id) {
		if (empty($id)) {
			return $this->error('参数错误!');
		}
		//判断该分类下有没有子分类，有则不允许删除
		/*$child = db('classify_2')->where(array('pid' => $id))->field('id')->select();
		if (!empty($child)) {
			return $this->error('请先删除该分类下的子分类');
		}*/
		//删除该分类信息
		$res = db('classify_2')->where(array('id' => $id))->delete();
		if ($res !== false) {
			//记录行为
			//action_log('update_category', 'category', $id, session('user_auth.uid'));
			return $this->success('删除分类成功！');
		} else {
			return $this->error('删除分类失败！');
		}
	}
	/**
	 * 通过一级分类ajax返回二级分类
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function find_classily(){
		$id = input('id');
		$info = db('classify_2')->where('pid='.$id)->select();
		return json($info);
	}
	/**
	 * 入库管理列表
	 * @param string $type
	 * @author huajie <banhuajie@163.com>
	 */
	public function storage() {
		//$map  = array('pid' => array('eq', 0));
		$list = db('Classify')->order('id','desc')->column('*', 'id');

		if (!empty($list)) {
			$tree = new \com\Tree();
			$list = $tree->toFormatTree($list);
		}

		$this->assign('tree', $list);
		$this->setMeta('分类列表');
		return $this->fetch();
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