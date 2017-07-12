<?php
/*
分类表相关操作
*/

/**
 * 获得指定分类同级的所有分类以及该分类下的子分类
 *
 * @access  public
 * @param   integer     $cat_id     分类编号
 * @return  array
 $advs = mysqld_selectall("select * from " . table('shop_adv') . " where enabled=1  order by displayorder desc");

$children_category = array();
$category = mysqld_selectall("SELECT *,'' as list FROM " . table('shop_category') . " WHERE isrecommand=1 and enabled=1 and deleted=0 ORDER BY parentid ASC, displayorder DESC", array(), 'id');
foreach ($category as $index => $row) {
    if (! empty($row['parentid'])) {
        $children_category[$row['parentid']][$row['id']] = $row;
        unset($category[$index]);
    }
}
 */
function get_categories_tree($cat_id = 0,$table='shop_category')
{
    if ($cat_id > 0)
    {
        $sql = 'SELECT parentid  FROM ' . table($table) . " WHERE id = '$cat_id'";
        $result = mysqld_select($sql);
        $parent_id = $result['parentid'];
    }
    else
    {
        $parent_id = 0;
    }

    /*
     判断当前分类中全是是否是底级分类，
     如果是取出底级分类上级分类，
     如果不是取当前分类及其下的子分类
    */
    $sql = 'SELECT count(*) FROM ' . table($table) . " WHERE parentid = '$parent_id' AND enabled = 1 ";
    if ( mysqld_select($sql) || $parent_id == 0)
    {
        /* 获取当前分类及其子分类 */
        $sql = 'SELECT * ' .
                'FROM ' . table($table) .
                "WHERE parentid = '$parent_id' AND enabled = 1 ORDER BY parentid ASC, isrecommand    DESC, displayorder ASC";
        $res = mysqld_selectall($sql);

        foreach ($res AS $row)
        {
            if ($row['enabled'])
            {
                $cat_arr[$row['id']]['id']   = $row['id'];
                $cat_arr[$row['id']]['name'] = $row['name'];
                $cat_arr[$row['id']]['url']  = mobile_url('goodlist', array('cid' => $row['id']),$row['name']);
                if (isset($row['id']) != NULL)
                {
                    $cat_arr[$row['id']]['cat_id'] = get_child_tree($row['id']);
                }
            }
        }
    }
    if(isset($cat_arr))
    {
        return $cat_arr;
    }
}

function get_child_tree($tree_id = 0, $table = 'shop_category')
{
    $three_arr = array();
    $sql = 'SELECT count(*) FROM ' . table($table) . " WHERE parentid = '$tree_id' AND enabled = 1 ";
    if (mysqld_select($sql) || $tree_id == 0)
    {
        $child_sql = 'SELECT* ' .
                'FROM ' . table($table) .
                "WHERE parentid = '$tree_id' AND enabled = 1 ORDER BY parentid ASC, isrecommand      DESC, displayorder ASC";
        $res = mysqld_selectall($child_sql);
        foreach ($res AS $row)
        {
            if ($row['is_show'])

               $three_arr[$row['id']]['id']   = $row['id'];
               $three_arr[$row['id']]['name'] = $row['name'];
               $three_arr[$row['id']]['url']  =  mobile_url('goodlist', array('cid' => $row['id']),$row['name']);
               if (isset($row['id']) != NULL)
               {
                       $three_arr[$row['id']]['cat_id'] = get_child_tree($row['id']);
               }
        }
    }
    return $three_arr;
}


/**
 * @param $cate1  p1数组
 * @param $cate2   p2数组
 * @param $cate3  p3 数组
 * @param $add_extend_ids  扩展ids
 * @param $delete_extend_ids 删除的扩展分类对应的字符串信息里面有 24,65,4,7
 * @param $dishid
 * 可能有删除，有更新，有新添加
 */
function operateCategoryExtend($cate1,$cate2,$cate3,$add_extend_ids,$delete_extend_ids,$dishid){
    if(!empty($delete_extend_ids)){  //删除
        $delete_extend_ids = explode(',',$delete_extend_ids);
        foreach($delete_extend_ids as $id){
            mysqld_delete('shop_category_extend',array('id'=>$id));
        }
    }

    $keys = array_keys($cate1);
    if(count($cate1) == 1 && $cate1[0] == 0){  //说明选择分类没有选择
        return ;
    }
    foreach($keys as $k){
        $id = empty($add_extend_ids) ? '' : $add_extend_ids[$k];
        $p1 = $cate1[$k];
        $p2 = $cate2[$k];
        $p3 = $cate3[$k];
        if($p1 == 0){  //
            continue;
        }
        $data = array(
            'p1'=>$p1,
            'p2'=>$p2,
            'p3'=>$p3,
            'dishid'=>$dishid
        );
        if(!empty($id)){ //修改
            mysqld_update('shop_category_extend',$data,array('id'=>$id));
        }else{ //新插入
            mysqld_insert('shop_category_extend',$data);
            if(!mysqld_insertid()){
                message('插入扩展分类出问题了!','','error');
            }
        }
    }
}



/**
 * 获取分类树  shopCategoryTree($list,$data);  会返回list
 * 带有层级结构
 * @param $list
 * @param $data
 * @param int $pid
 * @param int $level
 */
function shopCategoryTree(&$list,$data, $pid = 0, $level = 1){
    if (!is_null($pid)) {
        foreach ($data as $tmp) {
            if ($tmp['parentid'] == $pid) {
                $list[$tmp['id']]['main']  = $tmp;
                $list[$tmp['id']]['level'] = $level;
                $list[$tmp['id']]['child'] = array();
                shopCategoryTree($list[$tmp['id']]['child'], $data,$tmp['id'], $level + 1);
            }
        }
    }
}

/**
 * 获取分类树  $arr = selectCategoryTree($data);
 * 没有层级结构但是方便 下拉框做展示
 * @param $list
 * @param int $pid
 * @param int $level
 * @param string $html
 * @return array
 */
function selectCategoryTree(&$list,$pid=0,$level=0,$html='--'){
    static $tree = array();
    foreach($list as $v){
        if($v['parentid'] == $pid){
            $v['sort'] = $level;
            $v['html'] = str_repeat($html,$level);
            $tree[] = $v;
            selectCategoryTree($list,$v['id'],$level+1,$html);
        }
    }
    return $tree;
}

/**
 * 根据id获取分类的信息
 * @param $id
 * @param string $returnField
 * @return bool|mixed
 */
function getCategoryDataById($id,$returnField = ''){
    $category = mysqld_select("SELECT *  FROM " . table('shop_category') . "  where id={$id}");
    if(!empty($returnField)){
        return $category[$returnField];
    }else{
        return $category;
    }
}
/**
 * 获取所有的父级分类
 * @param string $filed
 * @return array
 */
function getCategoryAllparent($filed = "id,name"){
    $category = mysqld_selectall("SELECT {$filed}  FROM " . table('shop_category') . "  where parentid=0 and  deleted=0  ORDER BY parentid ASC, displayorder ASC");
    return $category;
}

/**
 * 通过pid获取所有的子类
 * @param $parentid
 * @param string $filed
 * @return array
 */
function getCategoryByParentid($parentid,$filed = "id,name"){
    if(empty($parentid)){
        return array();
    }
    $category = mysqld_selectall("SELECT {$filed}  FROM " . table('shop_category') . "  where parentid={$parentid} and  deleted=0 order by displayorder ASC");
    return $category;
}


/**
 * 按照分类获取品牌
 * @param $p1
 * @param $p2
 * @param $p3
 * @param string $field
 * @return array
 */
function getBrandByCategory($p1,$p2,$p3,$field="id,brand"){
    $where = '1=1';
    if(!empty($p1)){
        $where .= " and p1={$p1}";
    }
    if(!empty($p2)){
        $where .= " and p2={$p2}";
    }
    if(!empty($p3)){
        $where .= " and p3={$p3}";
    }
    $brand = mysqld_selectall("select {$field} from ".table('shop_brand')." where  {$where} and deleted =0");
    return $brand;
}


/**
 * 按照分类获取商品模型
 * @param $p1
 * @param $p2
 * @param $p3
 * @param string $field
 * @return array
 */
function getGoodtypeByCategory($p1=0,$p2=0,$field="id,gtype_name"){
    $where = '1=1';
    if(!empty($p1)){
        $where .= " and p1={$p1}";
    }
    if(!empty($p2)){
        $where .= " and p2={$p2}";
    }
    $gtype = mysqld_selectall("select {$field} from ".table('goodstype')." where  {$where} and status =1");
    return $gtype;
}