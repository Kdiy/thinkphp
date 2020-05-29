<?php

namespace app\library;
use think\Model as TpModel;
use think\Db;
class model extends TpModel{
	

	protected $default_tb = '';
	
	public function table($table_name){
		$this->default_tb = $table_name;
		return $this;
	}
	public function _add($data){
		return db($this->default_tb)->insert($data);
	}
	public function _add_get_id($data){
		return db($this->default_tb)->insertGetId($data);
	}
	public function _add_batch($all_data){
		return db($this->default_tb)->insertAll($all_data);
	}
	public function _edit($data,$condition=array()){
		return db($this->default_tb)->where($condition)->update($data);
	}
	public function _del($condition){
		return db($this->default_tb)->where($condition)->delete();
	}
	public function _list($condition,$field='*',$page,$limit,$order=''){
		$list = db($this->default_tb)->where($condition)->field($field)->page($page)->limit($limit)->order($order)->select();
		return $list;
	}
	public function _list_count($condition){
		return db($this->default_tb)->where($condition)->count();
	}
	public function _lists($condition,$field='*',$order=''){
		$list = db($this->default_tb)->where($condition)->field($field)->order($order)->select();
		return $list;
	}
	
	public function _find($condition,$field='*',$order=''){
		return db($this->default_tb)->where($condition)->field($field)->order($order)->find();
	}
	public function _trans($dbs){
		Db::startTrans();
		try{	
			// something
			
			Db::commit();
			return true;
		} catch (\Exception $e) {
			Db::rollback();
			return false;
		}		
	}
	
	
	
}