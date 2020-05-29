<?php
namespace app\behavior;


class cors {
	public function appInit(&$params){
	    $origin = request()->server('HTTP_ORIGIN');
		//指定url
	    header('Access-Control-Allow-Origin: '.$origin);	
		//通配符跨域
		// header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
		header("Access-Control-Allow-Headers: token,Origin, X-Requested-With, Content-Type, Accept");
		header('Access-Control-Allow-Methods: POST,GET');
		header('Access-Control-Allow-Credentials:true');
        if (request()->isOptions()) {
            exit();
        }
    } 


}

?>