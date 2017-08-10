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

//use \think\Model;

class Qlx extends Admin
{

    public function _initialize()
    {
        parent::_initialize();
        $this->getContentMenu();
    }

    public function _empty(){
        $this->error('方法不存在');
    }

    public function index()
    {
        $data = input();
        if (isset($data['ss'])) {
            $this->assign('meta', $data['ss']);
        }
        $list = db('qlxmanagement')->where('pid=0')->order('price desc,id asc')->column('*', 'id');

        foreach ($list as $key => $user) {
            $map0 = array('keywords' => ['like', $user['keywords'] . '%'], 'status' => 1);//空闲
            $map1 = array('keywords' => ['like', $user['keywords'] . '%'], 'status' => 0);//使用中
            $map2 = array('keywords' => ['like', $user['keywords'] . '%'], 'status' => 2);//预留
            $kk1 = model('Qlxmanagement')->where($map0)->column('keywords');
            $kk2 = model('Qlxmanagement')->where($map1)->column('keywords');
            $kk3 = model('Qlxmanagement')->where($map2)->column('keywords');
            $lists1 = $lists2 = $lists3 = array();
            foreach ($kk1 as $value) {
                $yy = count(explode('|', $value));
                if ($yy == 3) {
                    $lists1[] = count(explode('|', $value));
                }
            }
            foreach ($kk2 as $value) {
                $yy = count(explode('|', $value));
                if ($yy == 3) {
                    $lists2[] = count(explode('|', $value));
                }
            }
            foreach ($kk3 as $value) {
                $yy = count(explode('|', $value));
                if ($yy == 3) {
                    $lists3[] = count(explode('|', $value));
                }
            }
            $list[$key]['kx'] = count($lists1);
            $list[$key]['sy'] = count($lists2);
            $list[$key]['yl'] = count($lists3);
            //总房间数
            $list[$key]['cc'] = count($lists1) + count($lists2) + count($lists3);
            //房间图片
            $imgs = db('picture')->where('id=' . $user['img'])->find();
            $list[$key]['img'] = $imgs['url'];

            $list[$key]['ss'] = isset($data['ss']) ? $data['ss'] : 1;
        }
        // var_dump($list);die;
        if (isset($data['ss'])) {
            $this->setMeta('结算');
        } else {
            $this->setMeta('开台');
        }
        $this->assign('list', $list);
        return $this->fetch();
    }

    //结算快捷查询入口
    public function ajaxindex()
    {
        $data = input('post.');
        $datas['create_card_id'] = $data['cardid'];
        $datas['status'] = 0;
        $roomlist = db('order')->where($datas)->column('*', 'pid');
//        var_dump($roomlist);die;
        if (!empty($roomlist)) {
            return 1;

        } else {
            return 0;
        }
//        return json($roomlist);
//        var_dump($roomlist);die;
    }


    //预定控制器
    public function reserve()
    {
        $data = input('post.');

        if ($data['meta'] == 2) {
            $map['status'] = 0;
            $map['roomid'] = $data['id'];
            $id = db('reserve')->where($map)->find();
            return $id;
        }
        $data['create_time'] = time();
        $data['status'] = 0;
//        var_dump($data);die;
        $id = db('reserve')->insertGetId($data);
        if ($id) {
            $xid = db('Qlxmanagement')->where('id', $data['roomid'])->update(['status' => 2]);
            if ($xid) {
                return array('code' => 1, 'meg' => '预定成功');
//                return $this->success('订单结算成功！', url('index'));
            }
        } else {
            return $this->error('订单结算成功！', url('index'));
        }

    }


    //购物订单处理
    public function order()
    {
        $data = input('post.');
//        var_dump($data);die;
        $name = session('user_auth.uid');
        $data['order_id'] = 'QLXGW' . date("YmdHi") . rand(1111, 9999);

        $data['operate_id'] = $name;
        $data['create_time'] = time();

        if (isset($data['xid'])) {
            $datas = db('order')->where(array('pid' => $data['xid'], 'status' => 0))->find();
//            var_dump($datas['id']);die;
            $data['status'] = 0;
            $data['pid'] = $datas['id'];
        } else {
            $data['clear_time'] = time();
            $data['status'] = 1;
        }
        //插入到订单数据表并返回ID
        $id = db('order')->insertGetId($data);
        //把购物详情插入到订单详情表
        $list = db('cart')->where(array('operate_id' => $name))->field('title,price,count')->select();
        foreach ($list as $key => $datas) {
            $datas['order_id'] = $id;
            db('order_detail')->insert($datas);
        }

        if (isset($id)) {

            echo 1;
        } else {
            echo 2;
        }
    }

    //数据中心
    public function shuju()
    {
        return $this->fetch();
    }

    //订单生成/结算
    public function clear()
    {
        $qlxmanagement = model('Qlxmanagement');
        if (IS_POST) {
            //提交表单
            $data = input('post.');
//            var_dump($data);die;
            if ($data['meta'] == 0) {
                //更新订单台位表状态
                db('order')->where('id', $data['pid'])->update(['status' => 1, 'remark' => $data['remark'], 'sstime' => $data['sstime'], 'totalprice' => $data['deltotal'], 'clear_time' => time()]);
                if ($data['xqd'] != 0) {
                    //更新订单购物状态
                    db('order')->where('id', $data['xqd'])->update(['status' => 1, 'clear_time' => time()]);
                }
                if ($data['delpid'] != 0) {
                    //更新台房状态
                    $id = db('Qlxmanagement')->where('id', $data['delpid'])->update(['status' => 1]);
                }
                if (false !== $id) {
                    return $this->success('订单结算成功！', url('index'));
                }

            } else {

                $data['status'] = 0;
                $data['create_time'] = time();
                $id = db('order')->insert($data);
                $xid = db('Qlxmanagement')->where('id', $data['pid'])->update(['status' => 0]);
                $xxid = db('reserve')->where('roomid', $data['pid'])->where('status', '0')->update(['status' => '1']);
//                var_dump($data);die;
                if (false !== $id && false !== $xid && false !== $xxid) {

                    //action_log('update_category', 'category', $id, session('user_auth.uid'));
                    return $this->success('新增成功！', url('qlx/index'));
                } else {
                    $error = db('order')->getError();
                    return $this->error(empty($error) ? '未知错误！' : $error);
                }
            }

        } else {
            $price = 0;
            $data = input();
//            var_dump($data);die;
            if ($data['ss'] == 'clear') {
                $ss = 123;
                $list = db('order')->where('pid=' . $data['id'] . ' && status=0')->find();
//                var_dump($list);die;
                $list['meta'] = 0;
                //计算开台距当前时间
                $ss = $this->hours_min(time(), $list['create_time']);
//                var_dump($ss);die;
                $list['time'] = abs($ss['1']);
//                var_dump($list);die;
                //根据id 和 status 去查订单ID  再去订单详情表查询
                //$datas['id']=array('gt',$list['id']);
                $datas['pid'] = $list['id'];
                $datas['status'] = 0;
                $lists = db('order')->where($datas)->select();
//                var_dump($lists);die;
                //订单表pid 结算使用
                $list['delpid'] = $list['pid'];
                //订单详情表id
                $list['xqd'] = 0;
                //计算订单总金额
                $sk = $ss['2'];
                if ($sk == 0) {
                    $sk = 1;
                }
                $list['total'] = abs($sk * $list['price']);
//                                var_dump($list);die;
                $ajaxclear = $listss = array();
                if (!empty($lists)) {
                    //查询订单详情表
                    foreach ($lists as $data) {
                        $list['xqd'] = $data['id'];
                        $price += $data['price'];
                        $listss[] = db('order_detail')->where('order_id=' . $data['id'])->select();
                    }
                    $list['prices'] = $price;
                    $list['total'] = $price + abs($sk * $list['price']);
//                    var_dump($listss);die;
//                    $this->assign('listbuy', $listss);
                }

                $ajaxclear['list'] = $list;
                $ajaxclear['listss'] = $listss;
                return $ajaxclear;

            } else {

                $id = $data['id'];
                $lists = $qlxmanagement->info($id, 'keywords,title,pid');
                $listss = $qlxmanagement->info($lists->pid, 'title,pid');
                $listsss = $qlxmanagement->info($listss->pid, 'title');
                $list['site_id'] = $listsss->title . $listss->title . $lists->title;
                $list['order_id'] = 'QLX' . $list['site_id'] . date("YmdHi") . rand(1111, 9999);
                $yy = substr($lists->keywords, 0, 2);
                $list['price'] = $qlxmanagement->info($yy, 'price')->price;
                $list['id'] = $id;
                $list['create_card_id'] = $list['remark'] = $list['create_time'] = '';
                $list['meta'] = "1";
//                var_dump($list);die;
                return $list;
            }
            $this->assign('list', $list);
            $this->assign('price', $price);
            $this->setMeta('订单');
            return $this->fetch();
        }
    }

    //时间戳计算公共函数
    function hours_min($start_time, $end_time)
    {
        if (strtotime($start_time) > strtotime($end_time)) list($start_time, $end_time) = array($end_time, $start_time);
        $sec = $start_time - $end_time;
        $sec = round($sec / 60);
        $min = str_pad($sec % 60, 2, 0, STR_PAD_LEFT);
        $hours_min = floor($sec / 60);


        if ($min <= 1) {
            $times['2'] = $hours_min;
        } else {
            $times['2'] = $hours_min + 1;
        }
        $min != 0 && $hours_min .= '小时' . $min . '分钟';
        $times['1'] = $hours_min;
        return $times;
    }

    //采购计划
    public function purchase()
    {
        if (IS_POST) {
            $data = input('post.data');
            $data = json_decode($data, true);
            //return $data;
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['addtime'] = time();
                $data[$i]['departmentId'] = 1;
                $secendC = db('classify_2')->where('id=' . $data[$i]['secendC'])->find();
                $spec = db('spec')->where('id=' . $secendC['spec_id'])->find();
                $data[$i]['spec'] = $spec['id'];

                $result = db('purchase')->insert($data[$i]);
                if (!$result) {
                    return $this->error($link->getError());
                }
            }
            return $this->success("添加成功！");
        }
        $classify = db('classify')->select();
        //dump($classify);
        $branch = db('branch')->where('id=1 and status=1')->find();
        $department = db('department')->where('id=1 and status=1')->find();
        //已提交的计划
        $purchase = db('purchase')->where('departmentId=1')->select();
        if ($purchase) {
            for ($j = 0; $j < count($purchase); $j++) {
                $firstC = db('classify')->where('id=' . $purchase[$j]['firstC'])->find();
                $purchase[$j]['firstC'] = $firstC['title'];
                $secendC = db('classify_2')->where('id=' . $purchase[$j]['secendC'])->find();
                $purchase[$j]['secendC'] = $secendC['title'];
                $spec = db('spec')->where('id=' . $purchase[$j]['spec'])->find();
                $purchase[$j]['spec'] = $spec['title'];
            }
        }
        //dump($purchase);
        $this->assign('branch', $branch);
        $this->assign('department', $department);
        $this->assign('classify', $classify);
        $this->assign('purchase', $purchase);
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

    //台房详情
    public function floor()
    {
        $data = input('');
//        var_dump($data);die;
        $id = isset($data['id']) ? $data['id'] : '';
        $tt['status'] = array('eq', '1');
        $meta = 0;
        //结算参数
        if ($data['ss'] == 'clear') {

            $tt['status'] = 0;
            $meta = 1;
        }
        //预算参数
        if ($data['ss'] == 2) {
            $tt['status'] = 2;
            $meta = 2;
        }
        //快捷结算入口

        $lists = db('qlxmanagement')->where($tt)->column('keywords', 'id');
        $datas[] = '';

        if ($lists) {
            foreach ($lists as $key => $value) {
                $yy = count(explode('|', $value));
                $xx = substr($value, 0, 2);
                if ($id) {
                    if ($xx == $id) {
                        if ($yy == 3) {
                            // $this->success('生成订单中','qlx/clear',['id'=>'5'],1);
                            //$this->redirect('qlx/clear', ['id' => $id,'ss'=>$data['ss']]);
                            $datas[] = $key;
                        }
                    }
                } else {
                    $qq['create_card_id'] = $data['create_card_id'];
                    $qq['status'] = 0;
                    $datas = db('order')->where($qq)->column('pid');
                }

            }
        }
//        if ($data['ty'] == 'fast') {
//
//        }
//         var_dump($datas);die;
        $list = db('qlxmanagement')->where('id', 'in', $datas)->order('id asc')->column('*', 'id');

        foreach ($list as $key => $value) {
            $kk = model('Qlxmanagement')->where('id=' . $value['pid'])->find()->title;
            $list[$key]['ss'] = isset($data['ss']) ? $data['ss'] : 1;
            $list[$key]['kk'] = $kk;
        }
//        var_dump($list);die;
        $this->setMeta('楼层信息');
        $this->assign('list', $list);
        $this->assign('fid', $id);
        $this->assign('meta', $meta);
        return $this->fetch();
    }

    //预定房间取消
    public function orderdel()
    {
        $data = input('post.');
        $id = db('qlxmanagement')->where('id', $data['id'])->where('status', '2')->update(['status' => '1']);
        $xid = db('reserve')->where('roomid', $data['id'])->where('status', '0')->update(['status' => '2']);
        if (isset($id) && isset($xid)) {
            return 1;
        } else {
            return 2;
        }
    }

    //库存管理

    public function inventory()
    {
        //$map  = array('pid' => array('eq', 0));
        $list = db('product')->order('id', 'desc')->where('area_id', 'like', 'QLX')->paginate(10);
        $page = $list->render();
        $this->assign('tree', $list);
        $this->assign('page', $page);
        $this->setMeta('产品列表');
        return $this->fetch();
    }

    /* 新增产品入库 同时生成入库记录 */
    public function addss()
    {
        $data = input('post.');

        if (IS_POST) {
            //var_dump($data);die;
            $Product = model('Product');
            //提交表单增加到库存
//            var_dump(555);die;
            $id = $Product->addproduct('QLX');
//            var_dump(123);
//            var_dump(666);die;
            $InStorageSetail = model('InStorageSetail');
            //提交表单添加明细
//            var_dump($id);die;
            $ids = $InStorageSetail->changess('QLX');
//            var_dump(999);die;
            if (false !== $id) {
//                var_dump(999);die;
                //action_log('update_category', 'category', $id, session('user_auth.uid'));
                return $this->success('新增成功！', url('index'));
            } else {
//                var_dump(888);die;
                $error = $InStorageSetail->getError();
                return $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $classify1 = db('classify')->select();
            $p_Classify = db('ClassifyModel1')->where(array('area_id' => 'QLX', 'status' => 0))->select();

            for ($i = 0; $i < count($p_Classify); $i++) {
                for ($j = 0; $j < count($classify1); $j++) {
                    if ($p_Classify[$i]['pid'] == $classify1[$j]['id']) {
                        $p_Classify[$i]['title'] = $classify1[$j]['title'];
                    }
                }
            }
            $this->assign('p_Classify', $p_Classify);
            $spec = db('spec')->select();
            $this->assign('spec', $spec);
            $this->assign('info', null);
            $this->setMeta('新增分类');
            return $this->fetch('edits');
        }
    }

    //台位管理
    public function management()
    {
        $map = array('status' => array('gt', -1));
        $list = db('qlxmanagement')->where($map)->order('id asc')->column('*', 'id');

        if (!empty($list)) {
            $tree = new \com\Tree();
            $list = $tree->toFormatTree($list);
        }
        //var_dump($list);die;
        $this->assign('tree', $list);
        $this->setMeta('栏目列表');
        return $this->fetch();
    }

    //商品管理
    public function shop()
    {
        $data = input('');
//        var_dump($data);
        $title = isset($data['names']) ? $data['names'] : '';
        $status = isset($data['status']) ? $data['status'] : 0;
        $map['area_id'] = 'QLX';
        if (isset($data['names'])) {
            if ($data['names'] != '') {
                $map['title'] = array('like', '%' . $data['names'] . '%');
            }

        }
        if (isset($data['status'])) {
            if ($data['status'] != 0) {
                $map['pid'] = $data['status'];
            }

        }


        $list = db('product')->where($map)->order('id asc')->paginate(5);

        // 获取分页显示
        $page = $list->appends(array('status' => $status))->render();
        //var_dump($list);die;
        $this->assign('list', $list);
        $this->assign('status', $status);
        $this->assign('page', $page);
        $this->assign('title', $title);
        $this->setMeta('栏目列表');
        return $this->fetch();
    }

    //分类管理

    //一级分类添加
    public function classify($type = 'admin')
    {
        //$map['module'] = $type;
        if (IS_POST) {
            if (empty($_POST['id'])) {
                return $this->error('请选择分类！', url('index'));
            }
            $ClassifyId = $_POST['id'];
            $ClassifyModel1 = model('ClassifyModel1');
            $id = $_POST['id'];
            $info = db('ClassifyModel1')->where(array('area_id' => 'QLX'))->select();
            $pids = array();
            for ($k = 0; $k < count($info); $k++) {
                array_push($pids, $info[$k]['pid']);
            }
            $area_id = 'QLX';
            $status = 1;
            if ($info) {
                $pid = array();
                //循环改变之前表中存在的一级菜单状态
                for ($i = 0; $i < count($info); $i++) {
                    $dels = $ClassifyModel1->changeSta($info[$i]['pid'], $area_id);
                }
                //循环插入本次新选择则的一级菜单
                foreach ($id as $key => $value) {
                    $result = $ClassifyModel1->changes($area_id, $value);
                }
            } else {
                for ($j = 0; $j < count($id); $j++) {
                    $result = $ClassifyModel1->changes($area_id, $id[$j]);
                }
            }
            if (false !== $result) {
                return $this->success('编辑成功！', url('index'));
            } else {
                $error = $Classify->getError();
                return $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $list = db('classify')->select();
            $info = db('ClassifyModel1')->where(array('area_id' => 'QLX', 'status' => 0))->select();
            $classify1 = array();
            for ($k = 0; $k < count($info); $k++) {
                array_push($classify1, $info[$k]['pid']);
            }
            $this->assign('list', $list);
            $this->assign('classify1', $classify1);
            return $this->fetch();
        }

    }

    //二级分类添加
    public function classifys($type = 'admin')
    {
        if (IS_POST) {
            $ClassifyModel2 = model('ClassifyModel2');
            if (empty($_POST['sid'])) {
                return $this->error('请选择分类！', url('index'));
            }
            $sid = $_POST['sid'];
            for ($i = 0; $i < count($sid); $i++) {
                $sid[$i] = explode('&', $sid[$i]);
            }
            $info = db('ClassifyModel2')->where(array('area_id' => 'QLX'))->select();
            $area_id = 'QLX';
            if ($info) {
                //循环改变表中存在的二级菜单状态
                for ($i = 0; $i < count($info); $i++) {
                    $dels = $ClassifyModel2->changeSta($info[$i]['sid'], $area_id);
                }
                //循环插入本次新选择则的二级菜单
                foreach ($sid as $key => $value) {
                    $result = $ClassifyModel2->changes($area_id, $value[0], $value[1]);
                    //$value[0]为一级分类id $value[1]为二级分类id
                }
            } else {
                for ($j = 0; $j < count($sid); $j++) {
                    $result = $ClassifyModel2->changes($area_id, $sid[$j][0], $sid[$j][1]);
                    //$sid[$j][0]为一级分类id $sid[$j][1]为二级分类id
                }
            }
            if (false !== $result) {
                return $this->success('编辑成功！', url('index'));
            } else {
                $error = $Classify->getError();
                return $this->error(empty($error) ? '未知错误！' : $error);
            }

        } else {

            $list1 = db('classify')->select();
            $list1s = db('ClassifyModel1')->where(array('area_id' => 'QLX', 'status' => 0))->select();
            for ($i = 0; $i < count($list1s); $i++) {
                $classify_2 = db('classify_2')->where('pid=' . $list1s[$i]['pid'])->select();
                $list1s[$i]['classify_2'] = $classify_2;
                for ($j = 0; $j < count($list1); $j++) {
                    if ($list1s[$i]['pid'] == $list1[$j]['id']) {
                        $list1s[$i]['title'] = $list1[$j]['title'];
                    }
                }
            }
            //var_dump($list1s);
            $info = db('ClassifyModel2')->where(array('area_id' => 'QLX', 'status' => 0))->select();
            $classify2 = array();
            for ($k = 0; $k < count($info); $k++) {
                array_push($classify2, $info[$k]['sid']);
            }
            $this->assign('list1s', $list1s);
            $this->assign('classify2', $classify2);
            $this->assign('info', null);
            return $this->fetch();
        }
    }


    //购物车
    public function cart()
    {
        $data = input();
        $ope = session('user_auth.uid');
//        var_dump($data);die;
        //通过商品销售页进入购物车
        if (isset($data['goods_id'])) {
            $map = array('operate_id' => $ope, 'goods_id' => $data['goods_id']);
            $lists = db('cart')->where($map)->order('id asc')->find();
            //var_dump($lists['id']);die;
            // $qlxmanagement = model('Commodity');
            $list = db('product')->where(array('id' => $data['goods_id']))->field('title,out_price')->find();
            if (!$lists) {
                $data['title'] = $list['title'];
                $data['price'] = $list['out_price'];
                $data['operate_id'] = session('user_auth.uid');
                $data['create_time'] = time();
                $id = db('cart')->insert($data);
            } else {
                db('cart')->where('id', $lists['id'])->update([
                    'count' => $data['count'],
                    'update_time' => time()]);
            }

        }
        $map = array('operate_id' => $ope);
        $lists = db('cart')->where($map)->order('id asc')->column('*', 'id');


        foreach ($lists as $key => $vo) {
            $lists[$vo['id']]['total'] = round($vo['price'] * $vo['count'], 1);
        }
        //var_dump($lists);die;
        //已开台 台房列表

        $roomlists = db('qlxmanagement')->where('status=0')->column('keywords', 'id');
        if ($roomlists) {
            foreach ($roomlists as $key => $value) {
                $yy = count(explode('|', $value));
                if ($yy == 3) {
                    $datas[] = $key;
                }
            }
            $roomlist = db('qlxmanagement')->where('id', 'in', $datas)->order('id asc')->column('*', 'id');
//            var_dump($roomlist);
            foreach ($roomlist as $key => $value) {
                $kk = model('Qlxmanagement')->where('id=' . $value['pid'])->find();
                $kk1 = model('Qlxmanagement')->where('id=' . $kk->pid)->find()->title;
                $roomlist[$key]['kk'] = $kk1 . $kk->title;
            }
            $this->assign('roomlist', $roomlist);
        }


        $this->assign('list', $lists);


        $this->setMeta('购物列表');
        return $this->fetch();
    }

    /* 单字段编辑 */
    public function editable($name = null, $value = null, $pk = null)
    {
        if ($name && ($value != null || $value != '') && $pk) {
            db('qlx_management')->where(array('id' => $pk))->setField($name, $value);
        }
    }

    /* 编辑分类 */
    public function edit($id = null, $pid = 0)
    {
        if (IS_POST) {
            $qlxmanagement = model('Qlxmanagement');
            //提交表单
            $result = $qlxmanagement->change();
            if (false !== $result) {
                //记录行为
                //action_log('update_category', 'category', $id, session('user_auth.uid'));
                return $this->success('编辑成功！', url('index'));
            } else {
                $error = $qlxmanagement->getError();
                return $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $cate = '';
            if ($pid) {
                /* 获取上级分类信息 */
                $cate = db('qlxmanagement')->find($pid);
                if (!($cate && 1 == $cate['status'])) {
                    return $this->error('指定的上级分类不存在或被禁用！');
                }
            }
            /* 获取分类信息 */

            $field = 'img';
            $this->assign('field', $field);
            $info = $id ? db('qlxmanagement')->find($id) : '';

            $this->assign('info', $info);
            $this->assign('category', $cate);
            $this->setMeta('编辑分类');
            return $this->fetch();
        }
    }

    /* 新增分类 */
    public function add($pid = 0)
    {
        $Category = model('Qlxmanagement');

        if (IS_POST) {

            //提交表单
            $id = $Category->change();
            //var_dump($id);die;
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
                $cate = $Category->info($pid, 'id,name,title,status,keywords');
                if (!($cate && 1 == $cate['status'])) {
                    return $this->error('指定的上级分类不存在或被禁用！');
                }
            }
            /* 获取分类信息 */
            $field = 'img';
            $this->assign('field', $field);
            $this->assign('info', null);
            $this->assign('category', $cate);
            $this->setMeta('新增分类');
            return $this->fetch('edit');
        }
    }

    public function status()
    {
        $id = $this->getArrayParam('id');
        $status = input('status', '0', 'trim,intval');

        if (!$id) {
            return $this->error("非法操作！");
        }

        $map['id'] = array('IN', $id);
        $result = db('qlxmanagement')->where($map)->setField('status', $status);
        if ($result) {
            return $this->success("设置成功！");
        } else {
            return $this->error("设置失败！");
        }
    }

    /**
     * 删除一个分类
     * @author huajie <banhuajie@163.com>
     */
    public function remove($id)
    {
        if (empty($id)) {
            return $this->error('参数错误!');
        }
        //判断该分类下有没有子分类，有则不允许删除
        $child = db('Qlxmanagement')->where(array('pid' => $id))->field('id')->select();
        if (!empty($child)) {
            return $this->error('请先删除该分类下的子分类');
        }
        //判断该分类下有没有内容
//        $document_list = db('Document')->where(array('category_id' => $id))->field('id')->select();
//        if (!empty($document_list)) {
//            return $this->error('请先删除该分类下的文章（包含回收站）');
//        }
        //删除该分类信息
        $res = db('Qlxmanagement')->where(array('id' => $id))->delete();
        if ($res !== false) {
            //记录行为
            // action_log('update_category', 'category', $id, session('user_auth.uid'));
            return $this->success('删除分类成功！');
        } else {
            return $this->error('删除分类失败！');
        }
    }

    //ajax更改购物车操作
    public function redus()
    {
        $data = input('');
        $gw['id'] = $data['id'];
        $gw['operate_id'] = session('user_auth.uid');
        $id = db('cart')->where($gw)->update(['count' => $data['cc']]);
        if ($id) {
            echo 1;
        }

    }

    ////ajax更改购物车删除操作
    public function ajaxdel()
    {
        $data = input('');
        //var_dump($data);die;
        $gw['id'] = $data['id'];
        $gw['operate_id'] = session('user_auth.uid');
        $id = db('cart')->where($gw)->delete();
        if ($id) {
            echo 1;
        } else {
            echo 2;
        }
    }

    //ajax更改购物车删除操作
    public function delall()
    {
        $gw['operate_id'] = session('user_auth.uid');
        $id = db('cart')->where($gw)->delete();
        if ($id) {
            $this->success('清空成功', 'shop', '', '1');
        } else {
            $this->error('购物车已请空', 'shop');
        }
    }

    //入库物品列表
    public function storage() {

        $search_name = input('pro');
        $id_info = db('department')->where(array('name'=>'棋乐轩'))->find();
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
        
        $id_info = db('department')->where(array('name'=>'棋乐轩'))->find();
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
                    $this->success('入库成功',url('storage'));
                }           
            }
        }else{
            $this->success('您已确认过，不可重复确认！',url('sure_in'));
        }
        
    }

    //阈值管理列表页
    public function threshold(){
        $list = db('dp_product')->where(array('departmentId'=>1))->order('id','desc')->paginate(10);
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
        $info = db('threshold')->where(array('departmentId'=>1,'pro_id'=>$id))->find();
        
        if($info){
            $up_th  = db('threshold')->where(array('departmentId'=>1,'pro_id'=>$id))->update(['down'=>$down]);
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

}