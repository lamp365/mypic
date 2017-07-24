<?php
namespace shop\controller;

class dish extends \common\controller\basecontroller
{
    public function lists()
    {
        $_GP = $this->request;

        $pindex = max(1, intval($_GP['page']));
        $psize = 20;
        $condition = ' deleted=0 ';
        $sql = "select * from ".table('shop_dish')." where {$condition} order by id DESC ";
        $sql .= " limit ".($pindex - 1) * $psize . ',' . $psize;
        //查询
        $list   = mysqld_selectall($sql);
        foreach($list as &$one){
            $one['number_total'] = mysqld_selectcolumn("select count(id) from ".table('dish_number')." where dish_id={$one['id']} and is_used=0");
        }
        $total = mysqld_selectcolumn('SELECT COUNT(*) FROM ' . table('shop_dish') . " as a WHERE {$condition}");
        $pager = pagination($total, $pindex, $psize);
        include page('dish/lists');
    }

    public function ajax_title()
    {
        $_GP = $this->request;
        if ( !empty($_GP['ajax_id']) ){
            $data = array(
                'title'=>$_GP['ajax_title']
            );
            mysqld_update('shop_dish',$data,array('id'=>$_GP['ajax_id']));
            die(showAjaxMess('200',$_GP['ajax_title']));
        }else{
            die(showAjaxMess('1002','修改失败'));
        }
    }

    public function ajax_total()
    {
        $_GP = $this->request;
        if ( !empty($_GP['ajax_id']) ){
            $data = array(
                'total'=>$_GP['ajax_stock']
            );
            mysqld_update('shop_dish',$data,array('id'=>$_GP['ajax_id']));
            die(showAjaxMess('200',$_GP['ajax_stock']));
        }else{
            die(showAjaxMess('1002','修改失败'));
        }
    }

    /**
     * 发布产品第一步先选择分类
     */
    public function post()
    {
        $_GP = $this->request;
        if(checksubmit('submit')){
            if(empty($_GP['title'])){
                message('游戏币种不能为空！',refresh(),'error');
            }
            if(empty($_GP['marketprice']) || !is_numeric($_GP['marketprice'])){
                message('金额不能为空且为数字！',refresh(),'error');
            }
            $data['title']   = trim($_GP['title']);
            $data['content'] = trim($_GP['content']);
            $data['marketprice'] = trim($_GP['marketprice']);
            if(empty($_GP['id'])){
                mysqld_insert('shop_dish',$data);
            }else{
                mysqld_update('shop_dish',$data,array('id'=>$_GP['id']));
            }
            message('操作成功！',web_url('dish',array('op'=>'lists')),'success');
        }

        $item = array();
        if(!empty($_GP['id'])){
            $item = mysqld_select('select * from '.table('shop_dish')." where id={$_GP['id']}");
        }
        include page('dish/add_dish');
    }

    public function post_dish()
    {
        $_GP = $this->request;
        $id     = intval($_GP['id']);
        if(empty($id)){
            //新添加的
            if(empty($_GP['p1']) || empty($_GP['p2'])){
                $url = web_url('goods',array('op'=>'post'));
                message('请选择分类',$url,'error');
            }

            $piclist = array();
            $item    = array();
        }else{
            //修改的
            $item = mysqld_select("SELECT * FROM " . table('shop_dish') . " WHERE id = :id", array(':id' => $id));
            if (empty($item)) {
                message('抱歉，商品不存在或是已经删除！', '', 'error');
            }
            if(empty($_GP['p1']) && empty($_GP['p2'])){
                $_GP['p1'] = $item['p1'];
                $_GP['p2'] = $item['p2'];
            }
            $piclist = mysqld_select("SELECT * FROM " . table('shop_dish_piclist') . " where goodid={$id}");
            if(!empty($piclist['picurl'])){
                $piclist = explode(',',$piclist['picurl']);
            }else{
                $piclist = array();
            }

        }
        //运费模板
        $disharea = mysqld_selectall("SELECT * FROM " . table('dish_list') . "  where deleted=0 and enabled =1 ORDER BY displayorder DESC");
        //获取品牌
        $brandlist     = getBrandByCategory(0,0,0);
        //获取商品模型
        $gtype_list    = getGoodtypeByCategory();

        $cat_name1     = mysqld_select("select name from ".table('shop_category')." where id={$_GP['p1']}");
        $cat_name2     = mysqld_select("select name from ".table('shop_category')." where id={$_GP['p2']}");

        include page('dish/dish_add');
    }

    public function show_number()
    {
        $_GP = $this->request;
        $is_used = intval($_GP['is_used']);
        $dish_id = $_GP['id'];
        $where   = "1=1";
        if(!empty($dish_id)){
            $where .= " and dish_id={$dish_id}";
        }
        $where .= " and is_used={$is_used}";

        $pindex = max(1, intval($_GP['page']));
        $psize = 20;

        $sql = "select * from ".table('dish_number')." where {$where} order by id DESC ";
        $sql .= " limit ".($pindex - 1) * $psize . ',' . $psize;
        //查询
        $list   = mysqld_selectall($sql);
        foreach($list as &$one){
            $dish = mysqld_select("select title,marketprice from ".table('shop_dish')." where id={$one['dish_id']}");
            $one['title']       = $dish['title'];
            $one['marketprice'] = $dish['marketprice'];
        }
        $total = mysqld_selectcolumn('SELECT COUNT(*) FROM ' . table('dish_number') . " WHERE {$where}");
        $pager = pagination($total, $pindex, $psize);

        //获取所有的游戏币
        $allDish = mysqld_selectall("select title,id from ".table('shop_dish')." where status =1 and deleted =0 ");
        include page('dish/show_number');
    }

    public function add_number()
    {
        $_GP = $this->request;
        if(checksubmit('submit')){
            $_GP['number'] = trim($_GP['number']);
            if(empty($_GP['number'])){
                message('激活码不能为空！',refresh(),'error');
            }
            if(empty($_GP['dish_id'])){
                message('请选择游戏币种！',refresh(),'error');
            }
            //查一下是否已经存在了
            $find = mysqld_select("select * from ".table('dish_number')." where number='{$_GP['number']}'");
            if($find){
                message('该激活码已经存在！',refresh(),'error');
            }
            $data['dish_id']   = trim($_GP['dish_id']);
            $data['number']    = trim($_GP['number']);
            $data['desc']      = trim($_GP['desc']);
            $data['createtime']= time();
            if(empty($_GP['id'])){
                mysqld_insert('dish_number',$data);
            }else{
                mysqld_update('dish_number',$data,array('id'=>$_GP['id']));
            }
            message('操作成功！',web_url('dish',array('op'=>'show_number')),'success');
        }

        $item = array();
        if(!empty($_GP['id'])){
            $item = mysqld_select('select * from '.table('dish_number')." where id={$_GP['id']}");
        }
        if(!empty($_GP['dish_id'])){
            $item['dish_id'] = $_GP['dish_id'];
        }
        //获取所有的游戏币
        $allDish = mysqld_selectall("select title,id from ".table('shop_dish')." where status = 1 and  deleted =0 ");
        include page('dish/add_number');
    }


    public function addbrand()
    {
        $_GP = $this->request;
        if(checksubmit('is_add')){
            if(empty($_GP['brandname'])){
                ajaxReturnData(0,'品牌名不能为空！');
            }
            if(empty($_GP['country_id'])){
                ajaxReturnData(0,'请选择国家！');
            }
            $data['brand']      = $_GP['brandname'];
            $data['country_id'] = $_GP['country_id'];

            if (!empty($_FILES['icon']['name'])) {
                $upload = file_upload($_FILES['icon']);
                if (is_error($upload)) {
                    ajaxReturnData(0,$upload['message']);
                }
                $data['icon'] = $upload['path'];
            }

            mysqld_insert('shop_brand',$data);
            if($last_id = mysqld_insertid()){
                $data['id'] = $last_id;
                ajaxReturnData(1,'操作成功！',$data);
            }else{
                ajaxReturnData(0,'操作失败！');
            }

        }
        $country = mysqld_selectall("select id,name from ".table('shop_country'));
        include page('dish/addbrand');
    }

    public function do_post()
    {
        $_GP = $this->request;
        $service = new \service\shop\goodsService();
        $res = $service->check_data_beforadd($_GP);
        if(!$res){
            message($service->getError());
        }

        $data = array(
            'p1'             => intval($_GP['pcate']),
            'p2'             => intval($_GP['ccate']),
            'brand'          => intval($_GP['brand']),
            'status'         => $_GP['status'],
            'displayorder'   => intval($_GP['displayorder']),
            'total'          => intval($_GP['total']),
            'totalcnf'       => intval($_GP['totalcnf']),
            'issendfree'     => intval($_GP['issendfree']),  //免邮
            'transport_id'   => intval($_GP['transport_id']),  //运费id

            'title'          =>  $_GP['title'],
            'description'    => $_GP['description'],
            'content'        => changeUeditImgToAli($_GP['content']),

            'productsn'      => $_GP['productsn'],
            'marketprice'    => $_GP['marketprice'],
            'productprice'   => $_GP['productprice'],
            'commision'      => number_format($_GP['commision']/100,2),   //佣金存进去 是已经除以100的

            'timeprice'      => $_GP['timeprice'],
            'istime'         => $_GP['istime'],
            'timestart'      => strtotime($_GP['timestart']),
            'timeend'        => strtotime($_GP['timeend']),

            'gtype_id'       => intval($_GP['gtype_id']),
            'createtime'   => TIMESTAMP,
            'type'         => $_GP['type'],					//商品类型
            'isnew'        => intval($_GP['isnew']),       //是否新品
            'isfirst'      => intval($_GP['isfirst']),       //是否广告
            'ishot'        => intval($_GP['ishot']),         //是否热卖
            'isjingping'   => intval($_GP['isjingping']),    //是否精品
            'isdiscount'   => intval($_GP['isdiscount']),    //是否是活动的
            'isrecommand'  => intval($_GP['isrecommand']),   //首页推荐

            'team_buy_count' => intval($_GP['team_buy_count']),
            'draw'           => intval($_GP['draw']),
            'draw_num'       => intval($_GP['team_draw_num']),


        );


        if (!empty($_FILES['thumb']['tmp_name'])) {
            $upload = file_upload($_FILES['thumb']);
            if (is_error($upload)) {
                message($upload['message']);
            }
            $data['thumb'] = $upload['path'];
        }
        if (empty($_GP['id'])) {
            mysqld_insert('shop_dish', $data);
            $id = mysqld_insertid();
            if ( empty($id) ){
                message('操作失败!');
            }
        } else {
            unset($data['createtime']);
            mysqld_update('shop_dish', $data, array('id' => $_GP['id']));
            $id = $_GP['id'];
        }

        $goodsService  = new \service\shop\goodsService();
        //添加产品的时候 加入图片
        $goodsService->actGoodsPicture($id,$_GP);
        // 处理商品规格 以及属性
//        $goodsService->actGoodsAttr($id,$_GP['attritem']);
        $goodsService->actGoodsSpec($id,$_GP['specitem']);

        message('商品操作成功！', web_url('dish', array(
            'op' => 'lists',
        )), 'success');

    }

    public function comment()
    {
        $_GP = $this->request;
        //订单评论时用的是goods表中的id   详情页面的$_gp['id']是dish表中的id
        $pindex = max(1, intval($_GP['page']));
        $psize = 20;
        $total = 0;
        $where = '';
        if(!empty($_GP['system'])){
            $where  = ' where system='.$_GP['system'];
        }

        if(!empty($_GP['timestart']) && !empty($_GP['timeend'])){
            $timestart = strtotime($_GP['timestart']);
            $timeend   = strtotime($_GP['timeend']);
            $where = "where comment.createtime >= {$timestart} and comment.createtime <= {$timeend}";
        }

        if(!empty($_GP['keyword'])){
            if(!empty($where)){
                $where .= " and";
            }else{
                $where = " where";
            }
            if(is_numeric($_GP['keyword'])){
                //说明要查找产品id
                $where .= " shop_dish.id={$_GP['keyword']}";
            }else{
                //说明模糊查询标题
                $where .= " shop_dish.title like '%{$_GP['keyword']}%'";
            }
        }

        $list = mysqld_selectall("SELECT comment.*,shop_dish.title,shop_dish.id as did FROM " . table('shop_goods_comment') . "   comment  left join " . table('shop_dish') . " shop_dish on shop_dish.gid=comment.goodsid {$where} ORDER BY comment.istop desc,comment.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
//            ppd("SELECT comment.*,shop_dish.title,shop_dish.id as did FROM " . table('shop_goods_comment') . "   comment  left join " . table('shop_dish') . " shop_dish on shop_dish.gid=comment.goodsid {$where} ORDER BY comment.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        $pager = '';
        if(!empty($list)){
            //获取评论对应的图片
            foreach($list as $key=> $row){
                $list[$key]['piclist'] = mysqld_selectall("select img from ". table('shop_comment_img') ." where comment_id={$row['id']}");
            }
            // 获取评论数量
            $total = mysqld_selectcolumn('SELECT COUNT(*) FROM ' . table('shop_goods_comment')." as comment left join  " . table('shop_dish') . " shop_dish on shop_dish.gid=comment.goodsid {$where}" );
            $pager = pagination($total, $pindex, $psize);
        }
        include page('dish_comment');
    }

    public function delete()
    {
        $_GP = $this->request;
        $id = intval($_GP['id']);
        $row = mysqld_select("SELECT id, thumb FROM " . table('shop_dish') . " WHERE id = :id", array(':id' => $id));
        if (empty($row)) {
            message('抱歉，商品不存在或是已经被删除！');
        }
        mysqld_delete("shop_dish", array('id' => $id));

        message('删除成功！', 'refresh', 'success');
    }

    public function delcomment()
    {
        $_GP = $this->request;
        $id = intval($_GP['id']);
        mysqld_delete("shop_goods_comment", array('id' => $id));
        mysqld_delete('shop_comment_piclist',array('comment_id'=>$id));
        message('删除成功！', 'refresh', 'success');
    }

    public function addcomment()
    {
        $_GP = $this->request;
        if(!empty($_GP['type']) && $_GP['type'] == 'new'){
            $dishid = $dish = $pager = $List = '';
            include page('dish_addcomment');

        }else{
            $pindex = max(1, intval($_GP['page']));
            $psize  = 20;
            $total  = 0;

            $dishid = $_GP['dishid'];
            $dish = mysqld_select("select * from ". table('shop_dish'). " where id={$dishid}");
            if(empty($dish)){
                message('查无此宝贝商品',refresh(),'error');
            }

            //提交的表单
            if(!empty($_GP['add_sub']) && $_GP['add_sub'] == 'sub'){
                if(empty($_GP['username']))
                    message('用户名不能为空！',refresh(),'error');
                if(empty($_GP['comment']))
                    message('评论不能为空！',refresh(),'error');

                $face  = '';
                $ispic = 0;
                if($_FILES['face']['error'] != 4){   //等于4没有内容
                    $upload = file_upload($_FILES['face']);
                    if (is_error($upload)) {
                        message($upload['message'], '', 'error');
                    }
                    $face  = $upload['path'];
                    $ispic = 1;
                }


                $data = array(
                    'createtime' => time(),
                    'username'   => $_GP['username'],
                    'comment'    => $_GP['comment'],
                    'rate'       => $_GP['rate'],
                    'goodsid'    => $dish['gid'],
                    'face'       => $face,
                    'ispic'      => $ispic
                );
                if($_GP['system'] == 0){
                    $rand = mt_rand(1,1000);   //随机取得系统设备3是ios 2安卓 1pc
                    $num = $rand%4;
                    if($num == 0)
                        $num = 1;
                }else{
                    $num = $_GP['system'];
                }
                $data['system'] = $num;
                mysqld_insert('shop_goods_comment',$data);
                $lastid = mysqld_insertid();
                $url    = web_url('dish',array('op'=>'addcomment','dishid'=>$dishid));
                if($lastid){
                    if(!empty($_GP['picurl'])){
                        foreach($_GP['picurl'] as $picurl){
                            mysqld_insert('shop_comment_img',array('img'=>$picurl,'comment_id'=>$lastid));
                        }
                    }
                    message('操作成功！',$url,'success');
                }else{
                    message('操作失败！',$url,'error');
                }
            }

            $total = 0;
            $pager = '';
            $list  = mysqld_selectall("SELECT comment.*,shop_dish.title,shop_dish.id as did FROM " . table('shop_goods_comment') . "   comment  left join " . table('shop_dish') . " shop_dish on shop_dish.gid=comment.goodsid where shop_dish.id={$dishid} ORDER BY comment.istop desc, comment.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
//                pp("SELECT comment.*,shop_dish.title,shop_dish.id as did FROM " . table('shop_goods_comment') . "   comment  left join " . table('shop_dish') . " shop_dish on shop_dish.gid=comment.goodsid where shop_dish.id={$dishid} ORDER BY comment.istop desc, comment.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
//                ppd($list);
            if(!empty($list)){
                //获取评论对应的图片
                foreach($list as $key=> $row){
                    $list[$key]['piclist'] = mysqld_selectall("select img from ". table('shop_comment_img') ." where comment_id={$row['id']}");
                }
                // 获取评论数量
                $total = mysqld_selectcolumn('SELECT COUNT(*) FROM ' . table('shop_goods_comment')." where goodsid={$list[0]['goodsid']}");
                $pager = pagination($total, $pindex, $psize);
            }
            include page('dish_addcomment');
        }
    }


    public function topcomment()
    {
        $_GP = $this->request;
        if($_GP['istop'] == 1)
            $istop = 0;    //取消置顶
        else
            $istop = 1;    //置顶评论
        mysqld_update('shop_goods_comment',array('istop'=>$istop),array('id'=>$_GP['id']));
        message('操作成功！',refresh(),'success');
    }

    public function downcomment()
    {
        $_GP = $this->request;
        //下沉沉到中下位置如第三页或者第四页，而不是沉到底，排在最后一页  一页算15个
        $id  = $_GP['id'];
        $gid = $_GP['gid'];
        $data = mysqld_selectall("select id,createtime from ".table('shop_goods_comment')." where goodsid={$gid} order by id desc");

        $num  = count($data)-1;
        $j = 0;
        foreach($data as $row){
            $j++;
            if($row['id'] == $id){
                break;
            }
        }
        $zhong = floor($num / 2);
        $xia   = floor($zhong / 2);
        $key   = $zhong + $xia;
        $time  = $data[$key]['createtime'];
        $res   = mysqld_update("shop_goods_comment",array('createtime'=>$time),array('id'=>$id));
        if($res){
            message("操作成功！",refresh(),'success');
        }else{
            message("操作失败！",refresh(),'error');
        }
    }

    public function open_groupbuy()
    {
        $_GP = $this->request;
        //凑单开关 关闭或者开启
        //先判断是否有虚拟用户
        $member = mysqld_select("select openid from ".table('member')." where dummy=1");
        if(empty($member))
            message("对不起，请到会员管理注册批量的虚拟用户",refresh(),'error');

        if($_GP['act'] == 'open'){
            mysqld_update('shop_dish',array('open_groupbuy'=>1),array('id'=>$_GP['id']));
        }else if($_GP['act'] == 'close'){
            mysqld_update('shop_dish',array('open_groupbuy'=>0),array('id'=>$_GP['id']));
        }
        message('操作成功',refresh(),'success');

    }

    public function replycomment()
    {
        $_GP = $this->request;
        // 评论回复
        $id  = $_GP['id'];
        $reply = $_GP['reply'];

        if (empty($reply)) {
            $reply = NULL;
        }
        $re = '';
        if (!empty($id)) {
            $re = mysqld_update("shop_goods_comment",array('reply'=>$reply),array('id'=>$id));
        }
        if ($re) {
            message("回复成功！",refresh(),'success');
        }else{
            message("回复失败，不能回复重复的内容！",refresh(),'error');
        }

    }

    public function ajax_dishstatus()
    {
        $_GP = $this->request;
        if(empty($_GP['dishid'])){
            die(showAjaxMess(1002,'参数有误'));
        }
        mysqld_update('shop_dish',array('status'=>$_GP['status']),array('id'=>$_GP['dishid']));
        die(showAjaxMess(200,'操作成功！'));
    }
}

