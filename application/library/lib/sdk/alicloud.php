<?php
/*
 * 阿里云短信model   composer require alibabacloud/client
 */

namespace app\library\lib\sdk;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class alicloud{
	
	protected $SignName = '';
	
	protected $access_key = '';
	
	protected $access_token = '';
	
	public function __construct(){

		$settings = M('memcache')->_sys_settins();
		
		$this->access_key = $settings['ali_access_key'];
		$this->access_token = $settings['ali_access_token'];
		$this->SignName = $settings['ali_template_sign'];
		
	}
	public function isPhone($tel){
		return preg_match("/^1[34578]{1}\d{9}$/",$tel) ? true : false;
	}
	/**
	 * @desc: 验证手机号是否可以发送短信
	 * @return boolean  true 可以发送
	 */
	public function hasSendMax($mobile,$template_id){
		
	}
	/**
	 * @desc: 发送短信,单条
	 * @param: tel 手机号码
	 * @param_arr  模板参数   关联数组 array('param'=>'13')
	 * @return $res->Code=='OK'   $res->Message
	 */
	public function SendMsg($tel,$param_arr,$template_id = ''){		
		$query['PhoneNumbers'] 	= $tel;
		$query['RegionId']	 	= 'default';
		$query['SignName']		= $this->SignName;
		$query['TemplateCode']	= $template_id;
		$query['TemplateParam']	= json_encode($param_arr);
		
		$options['query'] = $query;
		AlibabaCloud::accessKeyClient($this->access_key, $this->access_token)
                        ->regionId('cn-hangzhou') // replace regionId as you need
                        ->asDefaultClient();
		try {
			$result = AlibabaCloud::rpc()
				  ->product('Dysmsapi')
				  // ->scheme('https') // https | http
				  ->version('2017-05-25')
				  ->action('SendSms')
				  ->method('POST')
				  ->options($options)
				  ->request();
			return $result->toArray();
		} catch (ClientException $e) {
			return $e->getErrorMessage();
		} catch (ServerException $e) {
			return $e->getErrorMessage();
		}
	}
	/**
	 * @desc: 发送短信,批量 最大发送100条
	 * @param: phone_arr 手机号码 array('17712264410','18551345839')
	 * @param: sign_name_arr 签名 array('夜影科技','夜影科技')
	 * @param: template_id  模板id
	 * @param: tempnam_params 模板参数 array(
								array('code'=>'123456'),
								array('code'=>'123123')
							  );
	 * @return $res->Code=='OK'   $res->Message
	 */
	public function SendMsgBatch($phone_arr,$sign_name_arr,$template_id,$tempnam_params){
		
		AlibabaCloud::accessKeyClient($this->access_key, $this->access_token)
                        ->regionId('cn-hangzhou') // replace regionId as you need
                        ->asDefaultClient();
		$query['RegionId'] 			= 'cn-hangzhou';
		$query['PhoneNumberJson'] 	= json_encode($phone_arr);
		$query['SignNameJson'] 		= json_encode($sign_name_arr);
		$query['TemplateCode'] 		= $template_id;
		$query['TemplateParamJson'] = json_encode($tempnam_params);
		$options['query'] = $query;
		try {
			$result = AlibabaCloud::rpc()
			  ->product('Dysmsapi')
			  // ->scheme('https') // https | http
			  ->version('2017-05-25')
			  ->action('SendBatchSms')
			  ->method('POST')
			  ->host('dysmsapi.aliyuncs.com')
			  ->options($options)
			  ->request();
			return $result->toArray();
		} catch (ClientException $e) {
			return $e->getErrorMessage();
		} catch (ServerException $e) {
			return $e->getErrorMessage();
		}
		
	}
	
	
	
	
	
	
	
	
	
	
	
}


