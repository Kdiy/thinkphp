<?php
/**
 * 地区模型
 *
 *
 *
 *
 */
namespace app\library;
use think\Model;
use think\Db;
use think\Cache;

class area extends Model{
    
    // 获取产地信息
    public function getOrigin(){
        $condition['area_deep'] = 1;
        $condition['area_hot'] = 1;
        $order = 'area_sort desc,area_id desc';
        $field = 'area_id,area_alias';
        return db('area_new')->where($condition)->field($field)->order($order)->select();
    }
    
    
    public function getAreaCode($id){
        return db('area_new')->where(array('area_id'=>$id))->cache(true)->value('adcode');
    }
	
	public function getAreaNameById($id){
		$info = db('area_new')->where(array('area_id'=>$id))->cache(true)->find();
		return $info;
	}
	
	/*
	 * 根据id获取地址的详细信息
	 */
	public function getFullAddressById($id,$str=' '){
		$info = db('area_new')->where(array('area_id'=>$id))->find();
		$deep  = intval($info['area_deep']);
		
		$txt = '';
		switch ($deep){
			case 1:
				$txt = $info['area_name'];
			break;
			case 2:
				$parent_info = $this->getAreaNameById($info['area_parent_id']);
				$txt = $parent_info['area_name'].$str.$info['area_name'];
			break;
			case 3:
				$parent_info = $this->getAreaNameById($info['area_parent_id']);
				if($parent_info){
					$pp_info = $this->getAreaNameById($parent_info['area_parent_id']);
					$txt = $pp_info['area_name'].$str.$parent_info['area_name'].$str.$info['area_name'];
				}	
			break;
		} 
		return $txt;
	}
	


    
    /**
     * 获取地址详情
     *
     * @return mixed
     */
    public function getAreaInfo($condition = array(), $fileds = '*') {
        return db($condition)->field($fileds)->find();
    }

    /**
     * 获取一级地址（省级）名称数组
     *
     * @return array 键为id 值为名称字符串
     */
    public function getTopLevelAreas() {
        $data = $this->getCache();

        $arr = array();
        foreach ($data['children'][0] as $i) {
            $arr[$i] = $data['name'][$i];
        }

        return $arr;
    }

    /**
     * 获取获取市级id对应省级id的数组
     *
     * @return array 键为市级id 值为省级id
     */
    public function getCityProvince() {
        $data = $this->getCache();

        $arr = array();
        foreach ($data['parent'] as $k => $v) {
            if ($v && $data['parent'][$v] == 0) {
                $arr[$k] = $v;
            }
        }

        return $arr;
    }

    /**
     * 获取地区缓存
     *
     * @return array
     */
    public function getAreas() {
        return $this->getCache();
    }

    /**
     * 获取全部地区名称数组
     *
     * @return array 键为id 值为名称字符串
     */
    public function getAreaNames() {
        $data = $this->getCache();

        return $data['name'];
    }

    /**
     * 获取用于前端js使用的全部地址数组
     *
     * @return array
     */
    public function getAreaArrayForJson($src = 'cache') {
        if ($src == 'cache') {
            $data = $this->getCache();
        } else {
            $data = $this->_getAllArea();
        }

        $arr = array();
        foreach ($data['children'] as $k => $v) {
            foreach ($v as $vv) {
                $arr[$k][] = array($vv, $data['name'][$vv]);
            }
        }
        return $arr;
    }
    protected function getCache() {
        // 缓存中有数据则返回
        if (cache('area')) {
            $data = cache('area');
            $this->cachedData = $data;
            return $data;
        }

        // 查库
        $data = $this->_getAllArea();
        cache('area', $data);
        $this->cachedData = $data;

        return $data;
    }

    protected $cachedData;

    private function _getAllArea() {
        $data = array();
        $area_all_array = db('area_new')->limit(false)->select();
        foreach ((array) $area_all_array as $a) {
            $data['name'][$a['area_id']] = $a['area_name'];
            $data['parent'][$a['area_id']] = $a['area_parent_id'];
            $data['children'][$a['area_parent_id']][] = $a['area_id'];

            if ($a['area_deep'] == 1 && $a['area_region'])
                $data['region'][$a['area_region']][] = $a['area_id'];
        }
        return $data;
    }

    public function addArea($data = array()) {
        return db('area')->insert($data);
    }

    public function editArea($data = array(), $condition = array()) {
        return db('area')($condition)->update($data);
    }

    public function delArea($condition = array()) {
        return db('area')($condition)->delete();
    }

    /**
     * 递归取得本地区及所有上级地区名称
     * @return string
     */
    public function getTopAreaName($area_id,$area_name = '') {
        $info_parent = $this->getAreaInfo(array('area_id'=>$area_id),'area_name,area_parent_id');
        if ($info_parent) {
            return $this->getTopAreaName($info_parent['area_parent_id'],$info_parent['area_name']).' '.$info_parent['area_name'];
        }
		return '';
    }
    // 获取antd组件三级联动信息
    public function areaTree(){
        $cache_model = M('memcache');
        $name = 'sys_area_tree';
        $data = $cache_model->getCache($name);
        if($data){
            return $data;
        }
        $data = $this->getTreeAreaList();
        $cache_model->setCache($name,$data,0);
        return $data;
    }
   
    /**
     * 递归获取地区的三级联动地址
     *
     * @return mixed
     */
    public function getTreeAreaList($condition = array('area_deep'=>1), $fields = '*') {
        $yiarea =  db('area_new')->where($condition)->field($fields)->select();
        $areaList = array();
        foreach ($yiarea as $key => $val){
            $temp = array();
            $temp['value'] = $val['area_id'];
            $temp['label'] = $val['area_name'];
            $temp['area_sort'] = $val['area_sort'];
            $temp['area_parent_id'] = $val['area_parent_id'];
            $temp['area_deep'] = $val['area_deep'];
			$temp['adcode'] = $val['adcode'];
            $temp['children'] = $this->getChildrenInfo($val['area_id']);
            $areaList[] = $temp;
        }
        return $areaList;
    }
	/**
     * 获取地址列表
     *
     * @return mixed
     */
    public function getAreaList($condition = array(), $fields = '*', $group = '', $page = null) {
        return db('area_new')->where($condition)->field($fields)->page($page)->group($group)->select();
    }

    /**
     * 递归取得本地区所有孩子信息
     * @return array
     */
    public function getChildrenInfo($area_id) {
	
        $result = array();
        $list = $this->getAreaList(array('area_parent_id'=>$area_id));
        if(!empty($list)){
            foreach ($list as $key => $val){
                $temp = array();
                $temp['value'] = $val['area_id'];
                $temp['label'] = $val['area_name'];
                $temp['area_sort'] = $val['area_sort'];
                $temp['area_parent_id'] = $val['area_parent_id'];
                $temp['area_deep'] = $val['area_deep'];
				$temp['adcode'] = $val['adcode'];
                if($temp['area_deep'] == 2){
                    $temp['children'] = $this->getChildrenInfo($val['area_id']);
                }
                $result[] = $temp;
            }
        }
        return $result;
    }

    /**
     * 递归取得本地区所有孩子ID
     * @return array
     */
    public function getChildrenIDs($area_id) {
        $result = array();
        $list = $this->getAreaList(array('area_parent_id'=>$area_id),'area_id');
        if ($list) {
            foreach ($list as $v) {
                $result[] = $v['area_id'];
                $result = array_merge($result,$this->getChildrenIDs($v['area_id']));
            }
        }
        return $result;
    }
}