<?php
namespace app\library;
use think\Db;
class model{
	
    protected $default_tb = '';
    protected $cache_time = 7200;   //缓存有效时间
    
    public function __construct($tb_name = ''){
        $this->default_tb = $tb_name;
    }
    /**
     * @desc insert一条数据
     * @param array $data
     * @return bool
     */
	public function _add(array $data) : bool
	{
		return db($this->default_tb)->insert($data);
	}
	/**
	 * @desc insert完成后返回主键id
	 * @param array $data
	 * @return int|NULL
	 */
	public function _add_get_id(array $data) : int
	{
		return db($this->default_tb)->insertGetId($data);
	}
	/**
	 * @desc 批量insert数据
	 * @param array $all_data
	 * @return boolean
	 */
	public function _add_batch(array $all_data) : bool
	{
		return db($this->default_tb)->insertAll($all_data);
	}
	/**
	 * @desc update一条数据
	 * @param array $condition 条件
	 * @param array $data      需要更新的内容
	 * @return bool
	 */
	public function _edit(array $condition,array $data) : bool
	{
		return db($this->default_tb)->where($condition)->update($data);
	}
	/**
	 * @desc 执行delete语句
	 * @param array $condition 条件
	 * @return bool
	 */
	public function _del(array $condition) : bool
	{
		return db($this->default_tb)->where($condition)->delete();
	}
	/**
	 * @desc 执行软删除
	 * @param array $condition 条件
	 * @return bool
	 */
	public function _soft_del(array $condition) : bool
	{
	    return db($this->default_tb)->where($condition)->update(['deleted'=>1]);
	}
	
	
	/**
	 * @desc 获取分页的数据列表
	 * @param array $condition  条件
	 * @param string $field     指定字段
	 * @param number $page      页码
	 * @param number $limit     每页数量
	 * @param string $order     排序条件
	 * @return array
	 */
	public function _list(array $condition, $field = '*', $page = 1, $limit = 1, $order = '') : array
	{
		$list = db($this->default_tb)->where($condition)
		->field($field)->page($page)
		->limit($limit)->order($order)
		->select();
		return $list;
	}
	/**
	 * @desc 获取筛选后的数量
	 * @param array $condition 条件
	 * @return int
	 */
	public function _list_count(array $condition) : int
	{
		return db($this->default_tb)->where($condition)->count();
	}
	/**
	 * @desc 获取没有分页的数据信息
	 * @param array $condition 筛选条件
	 * @param string $field    列名
	 * @param string $order    排序条件
	 * @param boolean $cache   是否从缓存中获取      
	 * @return array
	 */
	public function _lists(array $condition, $field = '*', $order = '', $cache = false) : array
	{
	    if($cache){
	        return db($this->default_tb)->where($condition)->field($field)->order($order)
	        ->cache($this->cache_time)->select();
	    }
		return db($this->default_tb)->where($condition)->field($field)->order($order)->select();
	}
    /**
     * @desc 查找一条数据
     * @param array $condition  筛选条件
     * @param string $field     指定字段名
     * @param string $order     排序条件
     * @param string $cache     是否读取缓存
     * @return array
     */	
	public function _find(array $condition, $field = '*', $order = '', $cache = false) : array
	{
	    if($cache){
	        return db($this->default_tb)->where($condition)->field($field)->order($order)
	        ->cache($this->cache_time)->find();
	    }
		return db($this->default_tb)->where($condition)->field($field)->order($order)->find();
	}
	/**
	 * @desc 执行一条事务
	 * @param array $sqls          待执行的多条sql语句
	 * @param function $success    成功后的回调 
	 * @param function $error      失败后的回调   function($e){ $e->getCode();$e->getMessage() }
	 * @example   
	 *     @sql = [
	 *         'insert into tbname1 (field1,field2) values(val1,val2)',
	 *         'insert into tbname2 (field1,field2) values(val1,val2)',
	 *     ]
	 *     M('')->_trans($sql,function(){
	 *         return true;
	 *     },function($e){
	 *         var_dump($e->getCode);
	 *         var_dump($e->getMessage());
	 *     });
	 * @return bool
	 * 
	 */
	public function _trans(array $sqls, $success = NULL, $error = NULL) : bool
	{
		Db::startTrans();
		try{	
			foreach ($sqls as $k=>$v){
			     Db::query($v);
			}
			Db::commit();
			return $success();
		} catch (\Exception $e) {
			Db::rollback();
			return $error($e);
		}		
	}
	
	
	
}