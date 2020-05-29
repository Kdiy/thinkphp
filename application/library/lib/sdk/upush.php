<?php
// 友盟推送
// 文档地址 https://developer.umeng.com/docs/67966/detail/68343
namespace app\library\lib\sdk;


class upush{
	protected $appkey           = NULL; 
	protected $appMasterSecret  = NULL;
	protected $timestamp        = NULL;
	protected $validation_token = NULL;
	protected $production_mode  = true;	// 正式环境 
	protected $mipush			= true; // 离线时使用系统推送
	protected $host = "https://msgapi.umeng.com";
	protected $cancelPath = "/api/cancel";
	protected $ios_sound 		= 'chime';	// IOS通知音乐 默认 default
	protected $description 		= '新消息通知';
	
	public function set($key,$val){
		$this->$key = $val;
	}
	public function get($key){
		return $this->$key;
	}
	public function __construct() {
		
		$this->timestamp = strval(time());
		$this->mipush = true;
		$this->setting = M('memcache')->_sys_settins();	
	}
	public function memberNotify($member_id,$title,$content,$action_type,$action_body){
		$member_info = M('member')->_getMemberDeviceToken($member_id);
		if($member_info['device_token']){
			$platform = $member_info['last_login_platform'];
			$device_tokens = [$member_info['device_token']];
			return $this->sendNotification($title,$content,$action_type,$action_body,'',$device_tokens,$platform);
		}
		return false;
	}
	
	// 设置安卓信息
	public function _android(){
		$this->appkey = $this->setting['umeng_appkey_android'];
		$this->appMasterSecret = $this->setting['umeng_appMasterSecret_android'];
	}
	// 设置IOS信息
	public function _ios(){
		$this->appkey = $this->setting['umeng_appkey_ios'];
		$this->appMasterSecret = $this->setting['umeng_appMasterSecret_ios'];
	}
	// 根据after_open类型计算出body中应填的参数名
	public function getTypeKey($key){
		$action_type = [
			'go_app'		=>'',
			'go_url'		=>'url',
			'go_activity'	=>'activity',
			'go_custom'		=>'custom',		
		];
		return $action_type[$key];
	}
	
	private function buildModel($device_tokens,$platform){
		//if($device_tokens === false){
		//	throw new \Exception('can not get device_token');
		//}
		$batch = $device_tokens ? false : true;
		$count = count($device_tokens);
		if($platform == 'android'){
			$this->_android();
			if($count == 0){
				return 'android/AndroidBroadcast';	//广播
			}elseif($count == 1){
				return 'android/AndroidUnicast';	//单播
			}else{
				return 'android/AndroidListcast';	//列播
			}
		}elseif($platform == 'ios'){
			$this->_ios();
			if($count == 0){
				return 'ios/IOSBroadcast';
			}elseif($count == 1){
				return 'ios/IOSUnicast';
			}else{
				return 'ios/IOSListcast';
			}	
		}
	}
	// 发送消息
	public function sendNotification($title,$content,$action_type = 'go_app',$action_body = [],$ticker = '',$device_tokens = [],$platform = 'android'){
		if($platform == 'android'){
			$this->_android();
			return $this->sendAndroidNotification($title,$content,$action_type,$action_body,$ticker,$device_tokens);
		}elseif($platform == 'ios'){ 
			$this->_ios();
			return $this->sendIOSNotification($content,$action_type,$action_body,$device_tokens);
		}
	}

	
	/**
	 * @desc: 安卓广播和点播
	 * @params: $title 		标题
	 * @params: $content	内容
	 * @params: $action_type 点击后的操作类型
	 * @params: $action_body  点击后的操作内容 Array类型
	 * @params: $ticker		通知栏提示文字
	 * @params: $device_tokens Array,如果是广播不需要传入此参数
	 * @params: $start_time 计划任务时间 时间戳
	 */
	public function sendAndroidNotification($title,$content,$action_type = 'go_app',$action_body = [],$ticker = '',$device_tokens = [],$start_time = 0){
		$class = $this->buildModel($device_tokens,'android');
		
		try {
			$model = L('upush/'.$class);			
			$model->setAppMasterSecret($this->appMasterSecret);
			$model->setPredefinedKeyValue("appkey",           $this->appkey);
			
			$model->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$model->setPredefinedKeyValue("ticker",           $ticker ? $ticker : $title);
			$model->setPredefinedKeyValue("title",            $title);
			$model->setPredefinedKeyValue("text",             $content);
			$model->setPredefinedKeyValue("description",      $this->description);
			if($start_time > $this->timestamp){
				
				$model->setPredefinedKeyValue("start_time",   date('Y-m-d H:i:s',$start_time));
			}
			$model->setPredefinedKeyValue("mipush",       	  $this->mipush);
			$model->setPredefinedKeyValue("device_tokens",	  join(',',$device_tokens));
			$keys = $this->getTypeKey($action_type);
			
			switch($action_type){
				case 'go_activity':
					$model->setPredefinedKeyValue("after_open",       $action_type);
					$model->setPredefinedKeyValue('mi_activity',$action_body['android']);
					$model->setPredefinedKeyValue('activity',$action_body['android']);
					break;
				case 'go_url':
					$model->setPredefinedKeyValue("after_open",       $action_type);
					$model->setPredefinedKeyValue('url',       	  $action_body['body']);
					break;
				case 'go_topic':
				case 'go_user':
				case 'go_block':
				case 'go_post':
					$model->setPredefinedKeyValue("after_open",       'go_custom');
					$model->setPredefinedKeyValue('custom',	json_encode($action_body));
					break;
				case 'go_app':
					$model->setPredefinedKeyValue("after_open",       $action_type);
					break;
				default:
					$model->setPredefinedKeyValue("after_open",       $action_type);
					$model->setPredefinedKeyValue($keys,       	  $action_body['body']);
					break;
			}
			
			$model->setPredefinedKeyValue("production_mode", $this->production_mode);
			// [optional]Set extra fields
			// $model->setExtraField("key1", "val1");
			$result = $model->send();
			
			return $result;
		} catch (\Exception $e) {
		
			trace($e->getMessage(),'upush');
			throw new \Exception($e->getMessage());			
		}
		
	}
	/**
	 * @desc: IOS广播和点播
	 * @params: $content	内容
	 * @params: $action_type 点击后的操作类型
	 * @params: $action_body  点击后的操作内容 Array类型
	 * @params: $device_tokens  Array,如果是广播不需要传入此参数
	 * @params: $start_time 计划任务时间 时间戳
	 */
	public function sendIOSNotification($content,$action_type = 'go_app',$action_body = [],$device_tokens = [],$start_time = '') {
		$class = $this->buildModel($device_tokens,'ios');
		
		try {			
			$model = L('upush/'.$class);
			$model->setAppMasterSecret($this->appMasterSecret);
			$model->setPredefinedKeyValue("appkey",           $this->appkey);
			
			$model->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$model->setPredefinedKeyValue("device_tokens",	  join(',',$device_tokens));
			$model->setPredefinedKeyValue("alert", 			  $content);  // alert string类型为IOS10之前 之后为 json格式{title:'标题',subtitle:'副标题',body:'内容'}
			$model->setPredefinedKeyValue("badge", 			  0);				//显示在App左上角的角标数,需要自己统计
			$model->setPredefinedKeyValue("sound", 			  $this->ios_sound);
			// Set 'production_mode' to 'true' if your app is under production mode
			$model->setPredefinedKeyValue("production_mode",  $this->production_mode);	 
			// Set customized fields
			if($start_time){
				$model->setPredefinedKeyValue("start_time",   date('Y-m-d H:i:s',$start_time));
			}
			$model->setCustomizedField("action", json_encode($action_body));
			
			$result = $model->send();
			return $result;
			
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}
	public function taskCancel($task_id,$platform){
		if($platform == 'android'){
			$this->_android();
		}elseif($platform == 'ios'){
			$this->_ios();
		}
		$url = $this->host . $this->cancelPath;
		$data['appkey'] = $this->appkey;
		$data['timestamp'] = TIMESTAMP;
		$data['task_id'] = $task_id;
		$postBody = json_encode($data);
		$this->fetch($url,$postBody);
	}
	
	// 生成签名
	private function makeSign($url,$postBody){
		return md5("POST" . $url . $postBody . $this->appMasterSecret);
	}
	// 请求API
	private function fetch($url,$postBody){
		$sign = $this->makeSign($url,$postBody);
        $url = $url . "?sign=" . $sign;
		
		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody );
        $result = curl_exec($ch);
		
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrNo = curl_errno($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);
		
		$result = json_decode($result,true);
		$datas = $result['data'];
		if ($httpCode == "0") {
          	 // Time out
           	throw new \Exception("[" . $curlErrNo . "]" . $curlErr);
        } else if ($httpCode != "200") {			
           	// We did send the notifition out and got a non-200 response
           	throw new \Exception('['.$datas['error_code'].'] '.$datas['error_msg']);
        } else {
			if($result['ret'] == 'FAIL'){
				throw new \Exception('['.$datas['error_code'].'] '.$datas['error_msg']);
			}
           	return $datas;
        }
	}
	
	
}