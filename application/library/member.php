<?php

namespace app\library;

use think\Model;
use think\Db;
class Member extends Model{
    
    // 拼接第三方登录授权的字段
    public function buildAuthField($provider,$openid, $unionid = ''){
        $condition = [];
        switch ($provider){
            case 'weixin':
                $condition['wx_openid'] = $openid;
                if(!empty($unionid)){
                    $condition['wx_unionid'] = $unionid;
                }
                break;
            case 'apple':
                $condition['apple_openid'] = $openid;
                break;
            case 'qq':
                $condition['qq_openid'] = $openid;
                break;
            case 'sinaweibo':
                $condition['sina_openid'] = $openid;
                break;
            default:
                $condition['member_id'] = 0;
                break;
        }
        return $condition;
        
    }
    
    
    
    
	// 通过手机号+密码注册账号
    /**
     * 
     * @param string $mobile   手机号
     * @param string $password 密码
     * @param string $reg_from 注册来源
     * @param string $invite_from_code  邀请码
     * @param string $unionid   微信unionid
     * @param $openid 授权登陆后openid
     * @param $provider 第三方授权登陆的方式
     * @return Interger|boolean
     */
    public function addMemberByMobile($mobile,$password,$reg_from,$invite_from_code = '',$unionid='',$openid = '',$provider = '',$nickname= ''){
	    
        Db::startTrans();
	    try{
	        if(!empty($provider)){
	            $data = $this->buildAuthField($provider, $openid,$unionid);
	        }
	        $data['member_name'] = $mobile;
	        $data['nickname'] = empty($nickname) ? $mobile : $nickname;
	        $data['mobile'] = $mobile;
	       
	        $data['member_pwd'] =  empty($password) ? md5(substr($mobile,5,6)) : md5($password);
	        
	        $data['reg_time'] = TIMESTAMP;
	        
	        $data['last_login_time'] = TIMESTAMP;
	        
	        // $data['login_counts'] = 1;
	        $ip = get_real_ip();
	        $data['last_login_ip'] = $ip;
	        $amap_address = L('sdk/amap')->geolocation_by_ip($ip);
	        if($amap_address['status']==1){
	            // $data['last_login_address'] = $amap_address['province'].'|'.$amap_address['city'];
	            $data['reg_city'] = $amap_address['province'].'|'.$amap_address['city'];
	            $data['reg_adcode'] = $amap_address['adcode'];
	        }
	        $data['reg_from'] = $reg_from;
	        $member_id = db('member')->insertGetId($data);

	        Db::commit();
	        return $member_id;
	    } catch (\Exception $e) {
	        Db::rollback();
	        return false;
	    }
		
	}
	//根据用户id生成4位邀请码
	public function createInvitationCode($user_id) {
	    static $source_string = 'E5FCDG3HQA4B1NOPIJ2RSTUV67MWX89KLYZ';
	    $num = $user_id;
	    $code = '';
	    while ( $num > 0) {
	        $mod = $num % 35;
	        $num = ($num - $mod) / 35;
	        $code = $source_string[$mod].$code;
	    }
	    if(empty($code[3]))
	        
	        $code = str_pad($code,4,'0',STR_PAD_LEFT);
	        
	        return $code;
	        
	}
	// 解码
	public function decodeInvitationCode($code) {
	    
	    static $source_string = 'E5FCDG3HQA4B1NOPIJ2RSTUV67MWX89KLYZ';
	    
	    if (strrpos($code, '0') !== false)
	        
	        $code = substr($code, strrpos($code, '0')+1);
	        
	        $len = strlen($code);
	        $code = strrev($code);
	        $num = 0;
	        for ($i=0; $i < $len; $i++) {
	            $num += strpos($source_string, $code[$i]) * pow(35, $i);
	        }
	        return $num;
	}
    //查询用户信息
    public function getMemberInfo($condition, $field = '*'){
        $condition['deleted'] = 0;
        return db('member')->where($condition)->field($field)->find();
    }
    // 通过手机号查询会员信息
    public function getMemberInfoByMobile($mobile){
        $condition['mobile'] = $mobile;
        $condition['deleted'] = 0;
        return $this->getMemberInfo($condition);
    }
    // 通过ID查询会员信息
    public function getMemberInfoById($member_id,$field='*'){
        $condition['member_id'] = $member_id;
        $condition['deleted'] = 0;
        return $this->getMemberInfo($condition,$field);
    }
    //新增用户
    public function addMember($data){
        return db('member')->insertGetId($data);
    }
    
    //修改用户信息
    public function editMember($where, $data){
        $data['last_edit'] = TIMESTAMP;
        return db('member')->where($where)->update($data);
    }
  
    //根据openid查询用户信息
    public function getMemberInfoByOpenid($openid, $field = '*')
    {
        return $this->getMemberInfo(array('xcx_openid' => $openid), $field);
    }

 
	// 生成登录日志
	public function loginLog($member_info){
		$edit['last_login_time'] = TIMESTAMP;
		$edit['login_counts'] = $member_info['login_counts'] + 1;
		$ip = get_real_ip();
		$edit['last_login_ip'] = $ip;
		$amap_address = L('sdk/amap')->geolocation_by_ip($ip);
	
		if($amap_address['status']==1){
			$edit['last_login_address'] = $amap_address['province'].'|'.$amap_address['city'];
		}
		return db('member')->where('member_id',$member_info['member_id'])->update($edit);
	}
}

