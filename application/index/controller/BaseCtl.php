<?php

namespace app\index\controller;

use think\Controller;

class BaseCtl extends Controller{
	
	protected $age = null;
	
	public function __construct(){
		$this->name = '123';
		
		
	}
	public function test(){
		$this->age = '1234';
	}
}