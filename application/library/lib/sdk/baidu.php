<?php
namespace app\library\lib\sdk;
// 百度ai开放平台
class baidu{
// 	private $appid = '';
// 	private $appsecret = '';
	private $error = '';
	private $errmsg = '';
	private $result = false;
	
	public function get($key){
		return $this->$key;
	}
	public function set($key,$value){
		$this->$key = $value;
	}
	
	public function __construct($config = []){
		$this->appid = $config['appid'];
		$this->appsecret = $config['appsecret'];		
	}
	public function get_access_token(){
		$data = db('settings')->where('field','baiduai_access_token')->find();
        if(!$data['value'] || $data['desc'] + 86400 * 29 < TIMESTAMP){
            $new_data = $this->_get_access_token();
            $save_data['desc'] = TIMESTAMP;
            $save_data['value'] = $new_data['access_token'];
            db('settings')->where('field','baiduai_access_token')->update($save_data);
            return $new_data['access_token'];
        }
        return $data['value'];
		
	}
	// 获取access_token 有效期30天  
	public function _get_access_token(){
		$url = 'https://aip.baidubce.com/oauth/2.0/token';
		$params = [
			'grant_type'	=>'client_credentials',
			'client_id'		=>$this->appid,
			'client_secret'	=>$this->appsecret		
		];
		
		$data = $this->post($url,'post',$params);
		
		if(!$data['access_token']){
			abort(255, '获取access_token失败');
		}
		return $data;
	}
	/**
	 * @desc: 文字识别  ---- 身份证识别
	 * @param: $file_path  			图像地址
	 * @param: $id_card_side   		1 || 2  人脸面 || 国徽面
	 * @param: $detect_direction	boolean  是否检测图像旋转角度
	 * @param: $detect_risk			boolean	 是否开启身份证风险检测				
	 * @return:
			status   true/false
			
	 */
	// 
	public function OCR_API_Idcard($file_path,$id_card_side = 1,$detect_direction = false,$detect_risk = false){
		$new_data = [];
		$new_data['status'] = false;
		
		if(!file_exists($file_path)){
			$this->errmsg = 'file_exists';
			return $new_data;
		}		
		$base_urlencode_img = base64_encode(file_get_contents($file_path));
		
		if(strlen($base_urlencode_img) > 1024 * 1024 * 4){
			$this->errmsg = 'file_to_large';
			return $new_data;
			#return $this->set('error','超出图像大小');
		}
		$id_card_side = $id_card_side == 1 ? 'front' : 'back';
		$body = [
			'image'				=>$base_urlencode_img,
			'id_card_side'		=>$id_card_side,
// 			'detect_direction'	=>$detect_direction,
// 			'detect_risk'		=>$detect_risk
		
		];
		
		$url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/idcard?access_token='.$this->get_access_token();
		
		$data = $this->post($url,'POST',$body);
		
		$result = $data['words_result'];
		
		if($data['image_status']=='normal'){
			$new_data['status'] 	= true;
			if($id_card_side=='front'){
				$new_data['address'] 	= $result['住址']['words'];
				$new_data['id_no'] 		= $result['公民身份号码']['words'];
				$new_data['birthday'] 	= $result['出生']['words'];
				$new_data['name'] 		= $result['姓名']['words'];
				$new_data['sex'] 		= $result['性别']['words'];
				$new_data['nation'] 	= $result['民族']['words'];
			}else{
				$new_data['expire'] 	= $result['失效日期']['words'];
				$new_data['sendtime']	= $result['签发日期']['words'];
				$new_data['sendgov']	= $result['签发机关']['words'];
			}
			
			
		}else{
			$this->errmsg = $data['image_status'];
		}
		
		return $new_data;
	}
	public function get_error(){
		$errmsg = '';
		switch($this->errmsg){
			case 'reversed_side':
				$errmsg = '身份证正反面颠倒';
				break;
			case 'non_idcard':
				$errmsg = '上传的图片中不包含身份证';
				break;
			case 'blurred':
				$errmsg = '证件图片模糊';
				break;
			case 'other_type_card':
				$errmsg = '证照类型有误';
				break;
			case 'over_exposure':
				$errmsg = '身份证关键字段反光或过曝';
				break;
			case 'over_dark':
				$errmsg = '身份证欠曝（亮度过低）';
				break;
			case 'unknown':
				$errmsg = '证件识别失败';
				break;
			case 'file_exists':
				$errmsg = '待识别的文件不存在';
				break;
			case 'file_to_large':
				$errmsg = '证件图像超出限制大小';
				break;
			default:
				$errmsg = '证件识别失败';
				break;
			
		}
		return $errmsg;
	}
	/**
	 * 模拟post进行url请求
	 * @param string $url
	 * @param array $post_data
	 */
	public function post($url = '',$type = 'POST', $post_data = array(),$decode=true) {
	    if (empty($url) || empty($post_data)) {
	        return false;
	    }
	    $o = "";
	    foreach ( $post_data as $k => $v ){
	        $o.= "$k=" . urlencode( $v ). "&" ;
	    }
	    $post_data = substr($o,0,-1);
	    $postUrl = $url;
	    $curlPost = $post_data;
	    $ch = curl_init();//初始化curl
	    
	    $method = $type =='POST' ? 1 : 0;
	    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
	    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
	    curl_setopt($ch, CURLOPT_POST, $method);//提交方式
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	    $data = curl_exec($ch);//运行curl
	    curl_close($ch);
	    
	    return $decode ? json_decode($data,true) : $data;
	}
	
}



?>