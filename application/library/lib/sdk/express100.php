<?php
// 快递100API
namespace app\library\lib\sdk;
class express100{
    
    private $appkey = '';
    private $customer = '';
    private $secret = '';
    private $userid = '';
    private $auto_screen = '';  //自动识别
    private $domain = 'https://poll.kuaidi100.com/';
    private $callback = 'https://zhlren.99vsoft.com/bin/public/callback/express/express100';
    
    private $sign_key = '';     // 签名字符串
    
    public function __construct(){
        
        
        
    }
    /**
     * @desc 校验签名
     * @param string $sign  传过来的签名
     * @param string $params  param里内容 
     * @return boolean
     */
    public function valid($sign,$params){
        if($sign != strtoupper(md5($params.md5($this->sign_key)))){
            throw new \Exception('invalid signature');
        }
    }
    
    /**
     * @desc 即时查询API
     * @param string $expres_code   物流公司编码
     * @param string $express_no    订单号
     * @param string $receive_phone 发货或收货人电话,顺丰快递必须
     */
    public function query($express_code,$express_no,$receive_phone = ''){
        $fetch_url = $this->domain.'poll/query.do';
        
        $post_data['customer'] = $this->customer;
        $params = [
            'com'       =>$express_code,
            'num'       =>$express_no,
            'phone'     =>$receive_phone,
          //'from'      =>'',
          //'to'        =>'',
          //'resultv2'  =>'1',  //开通行政区域解析
        ];
        $params_string = json_encode($params);
        $post_data['param'] = $params_string;
        $post_data['sign']  = $this->makeSign($params_string);
        
        $fetch_data = L('sdk/curl')->post($fetch_url,$post_data);
        
        if($fetch_data['message'] == 'ok'){
            return $fetch_data;
        }
        return false;
    }
    /**
     * @desc 订阅推送
     * @param string $expres_code   物流公司编码
     * @param string $express_no    订单号
     * @param string $receive_phone 发货或收货人电话,顺丰快递必须
     * @return boolean
     */
    public function poll($expres_code,$express_no,$receive_phone = ''){
        $fetch_url = $this->domain.'poll';
        $post_data['schema'] = 'JSON';
        $params = [
            'company'   =>$expres_code,
            'number'    =>$express_no,
            'key'       =>$this->appkey,
            'parameters'=>[
                'callbackurl'   =>$this->callback,
                'salt'          =>md5($this->sign_key),
                'phone'         =>$receive_phone
            ]
        ];
        $params_str = json_encode($params);
        $post_data['param'] = $params_str;
        $fetch_data = L('sdk/curl')->post($fetch_url,$post_data);

        return $fetch_data;     // fetch_data['result'] == true是成功   false 时读fetch_data['message']
//         if($fetch_data['result'] == true){
//             return true;
//         }
//         return false;
        
    }
    /**
     * @desc 订单号自动识别
     * @param string $express_no
     * @return unknown
     */
    public function auto_scene($express_no){
        $fetch_url = $this->domain.'autonumber/auto?num='.$express_no.'&key='.$this->appkey;
        $data = L('sdk/curl')->get($fetch_url);
        return $data;
    }
    
    /**
     * @desc 响应回调数据
     */
    public function success(){
        return  '{"result":"true",	"returnCode":"200","message":"成功"}';
        
    }
    /**
     * @desc 响应回调数据
     * 
     */
    public function error($errmsg = '失败'){
        return  '{"result":"false",	"returnCode":"500","message":"'.$errmsg.'"}';
        
    }
    
    /**
     * @desc 生成即时查询签名
     * @param string $params
     * @return string
     */
    public function makeSign($params = ''){
        return strtoupper(md5($params.$this->appkey.$this->customer));
    }
    
    
}

