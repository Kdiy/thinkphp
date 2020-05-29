<?php
//用户model

namespace app\library;
use think\Model;
use think\Db;
class member_token extends Model{
	public function _get_token($member_id, $member_name,$nickname,$client='xcx') {
        // $model_mb_user_token = Model('mb_user_token');

        //重新登录后以前的令牌失效
        //暂时停用
        //$condition = array();
        //$condition['member_id'] = $member_id;
        //$condition['client_type'] = $_POST['client'];
        //$model_mb_user_token->delMbUserToken($condition);

        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0,999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;
		$mb_user_token_info['nickname'] = $nickname;
        $result = $this->addMbUserToken($mb_user_token_info);
		return $result ? $token : null;
    }
	
	/**
	 * 查询
     *
	 * @param array $condition 查询条件
     * @return array
	 */
    public function getMbUserTokenInfo($condition) {
		//改为联查模式,否则会出现删除用户后该token仍然可以使用的问题
        return db('member_token')->alias('A')->join('member B','A.member_id=B.member_id')->order('A.token_id desc')->field('A.*,B.nickname')->where($condition)->find();
    }

    public function getMbUserTokenInfoByToken($token) {
        if(empty($token)) {
            return null;
        }
        return $this->getMbUserTokenInfo(array('token' => $token));
    }
    public function updateMemberOpenId($token, $openId)
    {
        return $this->where(array(
            'token' => $token,
        ))->update(array(
            'openid' => $openId,
        ));
    }
	/**
	 * 新增
	 *
	 * @param array $param 参数内容
	 * @return bool 布尔类型的返回结果
	 */
	public function addMbUserToken($param){
        return db('member_token')->insert($param);	
	}
	
	/**
	 * 删除
	 *
	 * @param int $condition 条件
	 * @return bool 布尔类型的返回结果
	 */
	public function delMbUserToken($condition){
        return db('member_token')->where($condition)->delete();
	}	
	
	
	


}