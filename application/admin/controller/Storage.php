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

class Storage extends Admin {

	public function _initialize() {
		parent::_initialize();
		$this->getContentMenu();
	}
	//产品列表
	public function index() {

		$search_name = input('pro');
		$id_info = db('department')->where(array('name'=>'物业'))->find();
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
	//阈值管理列表页
	public function threshold(){
		$list = db('dp_product')->where(array('departmentId'=>10))->order('id','desc')->paginate(10);
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
		$pro  = db('dp_product')->where(array('id'=>$id))->find();
		$info = db('threshold')->where(array('departmentId'=>10,'pro_id'=>$id))->find();
		
		if($info){
			$up_th  = db('threshold')->where(array('departmentId'=>10,'pro_id'=>$id))->update(['down'=>$down]);
			$up_pro = db('dp_product')->where(array('id'=>$id))->update(['down'=>$down]);
			if(false !== $up_th && false !== $up_pro){
				$data['code'] = 1;
				$data['msg'] = '设置成功！';
			}else{
				$data['code'] = -1;
				$data['msg'] = '设置失败！';
			} 
		}else{
			$datas['departmentId'] = 10;
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
	//确认入库
	public function sure_in(){
		$search_name = input('pro');
		$type = input('type');
		
		$id_info = db('department')->where(array('name'=>'物业'))->find();
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
					$this->success('入库成功',url('index'));
				}			
			}
		}else{
			$this->success('您已确认过，不可重复确认！',url('sure_in'));
		}
		
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

	//采购计划
    public function purchase(){
        if(IS_POST){
            $data = input('post.data');
            $data = json_decode($data,true);
            //return $data;
            for($i=0;$i<count($data);$i++){
                $data[$i]['addtime'] = time();
                $data[$i]['departmentId'] = 10;
                $secendC = db('classify_2')->where('id='.$data[$i]['secendC'])->find();
                $spec = db('spec')->where('id='.$secendC['spec_id'])->find();
                $data[$i]['spec'] = $spec['id'];

                $result = db('purchase')->insert($data[$i]);
                if(!$result){
                    return $this->error($link->getError());
                }
            }
            return $this->success("计划添加成功！");
        }
        $classify = db('classify')->select();
        //dump($classify);
        $branch = db('branch')->where('id=1 and status=1')->find();
        $department = db('department')->where('id=10 and status=1')->find();
        //已提交的计划
        $purchase = db('purchase')->where('departmentId=10')->select();
        if($purchase){
            for($j=0;$j<count($purchase);$j++){
                $firstC = db('classify')->where('id='.$purchase[$j]['firstC'])->find();
                $purchase[$j]['firstC'] = $firstC['title'];
                $secendC = db('classify_2')->where('id='.$purchase[$j]['secendC'])->find();
                $purchase[$j]['secendC'] = $secendC['title'];
                $spec = db('spec')->where('id='.$purchase[$j]['spec'])->find();
                $purchase[$j]['spec'] = $spec['title'];
            }
        }
        //dump($purchase);
        $this->assign('branch',$branch);
        $this->assign('department',$department);
        $this->assign('classify',$classify);
        $this->assign('purchase',$purchase);
        $this->setMeta("采购计划");
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
	/* 新增产品入库 同时生成入库记录 */
	public function add() {
		
		if (IS_POST) {
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
			$p_Classify = db('ClassifyModel1')->where(array('area_id'=>'wy','status'=>0))->select();

			for($i=0;$i<count($p_Classify);$i++){
				for($j=0;$j<count($classify1);$j++){
					if($p_Classify[$i]['pid'] ==  $classify1[$j]['id']){
						$p_Classify[$i]['title'] = $classify1[$j]['title'];
					}
				}
			}
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
		$info = db('ClassifyModel2')->where(array('classify1_id'=>$id))->select();
		$classify2 = db('classify_2')->select();
		for ($i=0; $i < count($info); $i++) { 
			for ($j=0; $j < count($classify2); $j++) { 
				if($info[$i]['sid'] == $classify2[$j]['id']){
					$info[$i]['title'] = $classify2[$j]['title'];
				}
			}
		}
		return json($info);

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