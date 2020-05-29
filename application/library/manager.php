<?php

namespace app\library;

class manager extends BaseModel{
	
    public function login($username,$password){
        $manager_info = db('manager')->where('username',$username)->find();
        if(!$manager_info){
            throw new \Exception('用户名不存在');
        }
        $user_password = $manager_info['password'];
        $hash_password = $this->manager_hash($password,$manager_info['encryption']);
        if($user_password != $hash_password){
            throw new \Exception('账号密码错误');
        }
        $token = $this->get_token($manager_info['manager_id'],$manager_info['username']);
        if(!$token){
            throw new \Exception('生成客户端访问令牌失败');
        }
        $data['manager_info'] = $manager_info;
        $data['token'] = $token;
        return $data;
    }
	public function manager_hash($password,$code){
	    return md5($code.$password);
	}
	public function randomkeys($length = 6)  {
	    $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
	    for($i=0;$i<$length;$i++){
	        $key .= $pattern{mt_rand(0,35)};    //生成php随机数
	    }
	    return $key;
	}
	private function create_token($manager_id,$manager_name,$client,$group_uid){
	    $string = $manager_id.$manager_name.$client.$group_uid.TIMESTAMP.$this->randomkeys();
	    return md5($string);
	}
	private function get_token($manager_id,$manager_name,$client = 'web',$group_uid = 1){
	    $token = $this->create_token($manager_id, $manager_name, $client, $group_uid);
	    $data['dateline'] = TIMESTAMP;
	    $data['token'] = $token;
	    $data['manager_id'] = $manager_id;
	    $data['manager_client'] = $client;
	    $data['manager_name'] = $manager_name;
	    $data['group_uid'] = $group_uid;
	    $res = $this->_add_token($data);
	    return $res ? $token : false;
	}
	// 生成token
	public function _add_token($token_info){
	    return db('manager_token')->insert($token_info);
	}
	// 获取token信息
	public function token_info($token){
	    return db('manager_token')->where('token',$token)->find();
	}
}