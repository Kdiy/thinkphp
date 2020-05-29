<?php
//短信模板model
namespace app\library\lib\sdk;
use think\Model;
class sms extends Model{
    protected $send_limit = 60;	 #发送间隔60秒
    protected $send_max = 10;	 #当天最大发送数量
    protected $code_expire = 900;	#短信验证码有效期
    
    protected $tb0 = 'message_history';
    
    // 验证码类短信检查验证码是否过期和被使用
    public function code_sms_valid($sms_info){
        if($sms_info['state'] == 1){
            return '验证码已被使用';
        }
        if(TIMESTAMP - $sms_info['dateline'] > $this->code_expire){
            return '验证码已过期,请重新获取';
        }
        return true;
    }
    
    // 验证码类获取短信内容
    public function code_sms_info($mobile,$template_id,$code){
        $condition['send_mobile'] = $mobile;
        $condition['template_id'] = $template_id;
        $condition['send_param'] = $code;
        #$condition['state'] = 0;
        return $this->send_info($condition);
    }
    // 验证码类短信使用后作废
    public function code_sms_used($sms_info){
        return db($this->tb0)->where('id',$sms_info['id'])->update(['state'=>1]);
    }
    
    
    // 获取短信发送内容
    public function send_info($condition){
        return db($this->tb0)->where($condition)->order('id desc')->find();
    }
    
    // 获取最近一次获取短信的时间
    public function last_send($mobile,$template_id){
        $condition['send_mobile'] = $mobile;
        $condition['template_id'] = $template_id;
        return db($this->tb0)->where($condition)->order('id desc')->value('dateline');
    }
    // 获取当天获取短信的次数
    public function day_send($mobile,$template_id){
        $condition['send_mobile'] = $mobile;
        $condition['template_id'] = $template_id;
        $condition['dateline'] = ['>=',strtotime(date('Y-m-d'))];
        return db($this->tb0)->where($condition)->count();
    }
    /**
     * @desc: 发送单条短息
     * @param: $mobile
     * @param: $template_id
     * @param: $param Array
     */
    public function send_sms($mobile,$template_id,$params,$desc){
        $sms_model = L('sdk/alicloud');
        try{
            if(!$sms_model->isPhone($mobile)){
                throw new \Exception('手机号码格式不正确');
            }
            $last_send = $this->last_send($mobile,$template_id);
            
            if($last_send && (TIMESTAMP - $last_send < $this->send_limit)){
                throw new \Exception('获取验证码过于频繁');
            }
            $day_send = $this->day_send($mobile,$template_id);
            if($day_send >= $this->send_max){
                throw new \Exception('超出当天最大发送数量');
            }
            
            $result = $sms_model->SendMsg($mobile,$params,$template_id);
            
            if($result['Code'] != 'OK'){
                throw new \Exception($result['Message']);
            }
            
            $data['dateline'] = TIMESTAMP;
            $data['send_mobile'] = $mobile;
            $data['template_id'] = $template_id;
            $data['send_param'] = join(',',$params);
            $data['send_desc'] = $desc;
            $template_info = M()->table('message_template')->_find(array('template_id'=>$template_id));
            $data['template_content'] = $template_info['template_content'];
            M()->table('message_history')->_add($data);
            return true;
            
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
    
}