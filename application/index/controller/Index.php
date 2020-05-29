<?php

namespace app\index\controller;

use think\Controller;



class Index extends BaseCtl{
	public function index(){
		$field = 'member_id as ddd';
		$order = 'ddd desc';
		$condition['ddd'] = 10;
		$data = db('member')->field($field)->where($condition)->order($order)->select();
		return json_encode($data);
		dump(123);
	}
	
	public function test(){
		
	}
}