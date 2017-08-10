<?php
// +----------------------------------------------------------------------
// | JunYuCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.213idc.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: tian <tian@213idc.cn> <http://www.213idc.cn>
// +----------------------------------------------------------------------

namespace app\admin\controller;
use app\common\controller\Admin;

class Commodity extends Admin {

	public function _initialize() {
		parent::_initialize();
		$this->getContentMenu();
	}

	public function index() {
		$map  = array('status' => array('gt', -1));
		$list = db('Commodity')->where($map)->order('sort asc,id asc')->column('*', 'id');

		if (!empty($list)) {
			$tree = new \com\Tree();
			$list = $tree->toFormatTree($list);
		}

		$this->assign('tree', $list);
		$this->setMeta('商品列表');
		return $this->fetch();
	}

	/* 单字段编辑 */
	public function editable($name = null, $value = null, $pk = null) {
		if ($name && ($value != null || $value != '') && $pk) {
			db('Commodity')->where(array('id' => $pk))->setField($name, $value);
		}
	}

	/* 编辑分类 */
	public function edit($id = null, $pid = 0) {
		if (IS_POST) {
			$category = model('Commodity');
			//提交表单
			$result = $category->change();
			if (false !== $result) {
				//记录行为
				//action_log('update_category', 'category', $id, session('user_auth.uid'));
				return $this->success('编辑成功！', url('index'));
			} else {
				$error = $category->getError();
				return $this->error(empty($error) ? '未知错误！' : $error);
			}
		} else {
			$cate = '';
			if ($pid) {
				/* 获取上级分类信息 */
				$cate = db('Commodity')->find($pid);
				if (!($cate && 1 == $cate['status'])) {
					return $this->error('指定的上级分类不存在或被禁用！');
				}
			}
			/* 获取分类信息 */
			$info = $id ? db('Commodity')->find($id) : '';

			$this->assign('info', $info);
			$this->assign('category', $cate);
			$this->setMeta('编辑分类');
			return $this->fetch();
		}
	}
	/* 新增分类 */
	public function add($pid = 0) {
		$Category = model('Commodity');

		if (IS_POST) {
			//提交表单
			$id = $Category->change();
			if (false !== $id) {
				//action_log('update_category', 'category', $id, session('user_auth.uid'));
				return $this->success('新增成功！', url('index'));
			} else {
				$error = $Category->getError();
				return $this->error(empty($error) ? '未知错误！' : $error);
			}
		} else {
			$cate = array();
			if ($pid) {
				/* 获取上级分类信息 */
				$cate = $Category->info($pid, 'id,name,title,status');
				if (!($cate && 1 == $cate['status'])) {
					return $this->error('指定的上级分类不存在或被禁用！');
				}
			}
			/* 获取分类信息 */
			$this->assign('info', null);
			$this->assign('category', $cate);
			$this->setMeta('新增分类');
			return $this->fetch('edit');
		}
	}
	/**
	 * 删除一个分类
	 * @author huajie <banhuajie@163.com>
	 */
	public function remove($id) {
		if (empty($id)) {
			return $this->error('参数错误!');
		}
		//判断该分类下有没有子分类，有则不允许删除
		$child = db('Commodity')->where(array('pid' => $id))->field('id')->select();
		if (!empty($child)) {
			return $this->error('请先删除该分类下的子分类');
		}
		//判断该分类下有没有内容
		$document_list = db('Document')->where(array('category_id' => $id))->field('id')->select();
		if (!empty($document_list)) {
			return $this->error('请先删除该分类下的文章（包含回收站）');
		}
		//删除该分类信息
		$res = db('Commodity')->where(array('id' => $id))->delete();
		if ($res !== false) {
			//记录行为
			action_log('update_category', 'category', $id, session('user_auth.uid'));
			return $this->success('删除分类成功！');
		} else {
			return $this->error('删除分类失败！');
		}
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
		$list = db('Commodity')->where($map)->field('id,pid,title')->select();
		//移动分类时增加移至根分类
		if ($type == 'move') {
			//不允许移动至其子孙分类
			$list = tree_to_list(list_to_tree($list));

			$pid = db('Commodity')->getFieldById($from, 'pid');
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
		$res  = db('Commodity')->where(array('id' => $from))->setField('pid', $to);
		if ($res !== false) {
			return $this->success('分类移动成功！', url('index'));
		} else {
			return $this->error('分类移动失败！');
		}
	}

	public function status() {
		$id     = $this->getArrayParam('id');
		$status = input('status', '0', 'trim,intval');

		if (!$id) {
			return $this->error("非法操作！");
		}

		$map['id'] = array('IN', $id);
		$result    = db('Commodity')->where($map)->setField('status', $status);
		if ($result) {
			return $this->success("设置成功！");
		} else {
			return $this->error("设置失败！");
		}
	}
}