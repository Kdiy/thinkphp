<?php

namespace app\library;

// 地区
class area
{




    protected $tb0 = 'area';
    // del
 	public function _del(array $condtion)
 	{
 		return $this->_edit($condtion,['deleted'=>1]);
 	}
    // edit
 	public function _edit(array $condtion, array $data)
 	{
 	  return db($this->tb0)->where($condtion)->update($data);
 	}
    // find
 	public function _find(array $condtion, $field = '*')
 	{
 	  return db($this->tb0)->where($condtion)->field($field)->find();
 	}
    // list
 	public function _list(array $condtion, $page = 1, $limit = 10, $field = '*', $order = '')
 	{
 	  return db($this->tb0)->where($condtion)->field($field)->page($page)->limit($limit)->order($order)->select();
 	}
    // count
 	public function _listCount(array $condtion)
 	{
 	  return db($this->tb0)->where($condtion)->count();
 	}
 

 }

 ?>