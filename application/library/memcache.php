<?php
//客户端缓存model

namespace app\library;
use think\Model;
use think\Cache;
class memcache extends Model{
	
	protected $mem_pre = 'mumuyou_mem_';
	
	protected $mem_group_pre = 'mumuyou_mem_g_';
	
	//清空缓存
	public function clear(){
		return Cache::clear();
	}
	
	
	public function clear_tb_cache($tb_name){
		Cache::rm($this->mem_pre.$tb_name);
		Cache::rm($this->mem_group_pre.$tb_name);
	}
	public function setCache($key,$val,$express = 7200){
	    cache($this->mem_pre.$key,$val,$express);
	}
	public function getCache($key){
	    return cache($this->mem_pre.$key);
	}
	
	//系统设置,特殊处理
	public function _sys_settins(){
		$data = $this->_get_cache('settings');
		if (array_values($data) == $data){
			$new_data = array();
			foreach($data as $k=>$v){
				$new_data[$v['field']] = $v['value'];
			}
			cache($this->mem_pre.'settings',$new_data);
			$data = $new_data;
		}
		return $data;
	}
	
	
	/**
	 * @desc: 数据源分组成 id=>value 类型的数组
	 * @param: $tb_name 表名
	 * @param: index_key 
	 * @param: value_key
	 */
	public function group_tb_cache($tb_name,$index_key,$value_key,$condition = array(),$field='*'){
		$mem_name = $this->mem_group_pre.$tb_name;
		$data = cache($mem_name);
		if(!$data){
			$tb_cache = $this->_get_cache($tb_name,$condition,$field);
			$data = array();
			foreach($tb_cache as $k=>$v){
				$data[$v[$index_key]] = $v[$value_key];
			}
			cache($mem_name,$data);
		}
		return $data;
	}
	public function group_tb($tb_name,$index_key,$value_key,$condition = array(),$field='*'){
		
		$tb_cache = db($tb_name)->where($condition)->field($field)->select();
		$data = array();
		foreach($tb_cache as $k=>$v){
			$data[$v[$index_key]] = $v[$value_key];
		}
		
		return $data;
	}
	public function _get_cache($tb_name,$condition = array(),$field='*',$order=''){
		$cache_name = $this->mem_pre.$tb_name;
		$data = cache($cache_name);
		if(!$data){
			$data = db($tb_name)->where($condition)->field($field)->order($order)->select();
			cache($cache_name,$data);
		}
		return $data;
	}
}


?>