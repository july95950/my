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

class Course extends Admin {

	public function _initialize() {
		parent::_initialize();
	}

	public function index() {
		$map   = array();
		//$title = trim(input('get.title'));
		$list  = db("course")->where($map)->field(true)->order('id asc')->column('*', 'id');
		int_to_string($list, array('hide' => array(1 => '是', 0 => '否'), 'is_dev' => array(1 => '是', 0 => '否')));

		if (!empty($list)) {
			$tree = new \com\Tree();
			$list = $tree->toFormatTree($list);
		}
		for($i=0;$i<count($list);$i++){
			if($list[$i]['pid'] != 0){
				$title = db("course")->field('title')->where(array('id'=>$list[$i]['pid']))->find();
				$list[$i]['up_title'] = $title['title'];
			}
		}
		// 记录当前列表页的cookie
		Cookie('__forward__', $_SERVER['REQUEST_URI']);
		
		$this->setMeta('课程列表');
		$this->assign('list', $list);
		return $this->fetch();
	}
	/* 单字段编辑 */
	public function editable($name = null, $value = null, $pk = null) {
		if ($name && ($value != null || $value != '') && $pk) {
			db('course')->where(array('id' => $pk))->setField($name, $value);
		}
	}

	/**
	 * 新增菜单
	 * @author yangweijie <yangweijiester@gmail.com>
	 */
	public function add() {
		if (IS_POST) {
			
			$data = input('post.');
			$data['addtime'] = time();

			//判断编号是否重复
			$rs = db('course')->where(array('courseNo'=>$data['courseNo']))->find();
			if($rs){
				$data['courseNo'] = substr($data['courseNo'],1,4);
				$data['courseNo'] = intval($data['courseNo'])+1;
				$data['courseNo'] = strval($data['courseNo']);
				switch(strlen($data['courseNo'])){
					case 1 : $data['courseNo'] = 'C000'.$data['courseNo'];break;
					case 2 : $data['courseNo'] = 'C00'.$data['courseNo'];break;
					case 3 : $data['courseNo'] = 'C0'.$data['courseNo'];break;
					default : $data['courseNo'] = 'C'.$data['courseNo'];break;
				}
			}

			$id = db('course')->insert($data);
			if ($id) {
				session('admin_menu_list', null);
				//记录行为
				//action_log('update_menu', 'Menu', $id, session('user_auth.uid'));
				return $this->success('新增成功', Cookie('__forward__'));
			} else {
				return $this->error('新增失败');
			}
		} else {
			$this->assign('info', array('pid' => input('pid')));
			$menus = db('course')->select();

			$maxNum = db('course')->order('id desc')->find();
			$courseNo = $maxNum['courseNo'];
			$courseNo = substr($courseNo,1,4);
			$courseNo = intval($courseNo)+1;
			$courseNo = strval($courseNo);
			switch(strlen($courseNo)){
				case 1 : $courseNo = 'C000'.$courseNo;break;
				case 2 : $courseNo = 'C00'.$courseNo;break;
				case 3 : $courseNo = 'C0'.$courseNo;break;
				default : $courseNo = 'C'.$courseNo;break;
			}

			//dump($courseNo);
			$tree  = new \com\Tree();
			$menus = $tree->toFormatTree($menus);
			if (!empty($menus)) {
				$menus = array_merge(array(0 => array('id' => 0, 'title_show' => '顶级菜单')), $menus);
			} else {
				$menus = array(0 => array('id' => 0, 'title_show' => '顶级菜单'));
			}
			$this->assign('courseNo', $courseNo);
			$this->assign('Menus', $menus);
			$this->setMeta('新增课程');
			return $this->fetch('edit');
		}
	}

	/**
	 * 编辑配置
	 * @author yangweijie <yangweijiester@gmail.com>
	 */
	public function edit($id = 0) {
		if (IS_POST) {
			
			$data = input('post.');
			if (db('course')->update($data, array('id' => $data['id'])) !== false) {
				session('admin_menu_list', null);
				//记录行为
				//action_log('update_menu', 'Menu', $data['id'], session('user_auth.uid'));
				return $this->success('更新成功',url('course/index'));
			} else {
				return $this->error('更新失败');
			}
		} else {
			$info = array();
			/* 获取数据 */
			$info  = db('course')->field(true)->find($id);
			$menus = db('course')->field(true)->select();
			$tree  = new \com\Tree();
			$menus = $tree->toFormatTree($menus);

			$menus = array_merge(array(0 => array('id' => 0, 'title_show' => '顶级菜单')), $menus);
			$this->assign('Menus', $menus);
			if (false === $info) {
				return $this->error('获取后台菜单信息错误');
			}
			$this->assign('info', $info);
			$this->setMeta('编辑后台菜单');
			return $this->fetch();
		}
	}


	/**
	 * 删除后台菜单
	 * @author yangweijie <yangweijiester@gmail.com>
	 */
	public function del() {
		$id = $this->getArrayParam('id');

		if (empty($id)) {
			return $this->error('请选择要操作的数据!');
		}

		$map = array('id' => array('in', $id));
		if (db('course')->where($map)->delete()) {
			session('admin_menu_list', null);
			//记录行为
			//action_log('update_menu', 'Menu', $id, session('user_auth.uid'));
			return $this->success('删除成功',url('course/index'));
		} else {
			return $this->error('删除失败！');
		}
	}



}