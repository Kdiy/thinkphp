<?php
namespace app\library\lib\sdk;
/**
 * @desc: App签名
 *
 */
class signature{
	private $client_time_deviation = 7200;  //客户端允许时间误差  0不计算误差(会降低安全性)
	private $cache_pre = 'mmobbs_signature_';	// 缓存前缀
	private $cache_life = 7200;					// 缓存有效期  0永久 有效时间越长,产生的缓存文件越大
	
	/**
	 * @desc: 生成signature
	 * @param: $timestamp 	时间戳
	 * @param: $nonce_str 	随机字符串
	 * @param: $os   		客户端类型 值android或ios
	 * @param: $version 	签名版本 固定值 '1.0.1'
	 * @return: String
	 */
	public function _makeSignature($timestamp,$nonce_str,$os,$version){
		
		$data['timestamp'] = $timestamp;
		$data['nonceStr'] = $nonce_str;
		$data['os'] = $os;
		$data['version'] = $version;
		$data['appKey'] = APPKEY;
		$rebuild_data = $this->resort($data);
		
		$query_str = http_build_query($rebuild_data);	//防止汉字转义
		
		$base_encode_str = base64_encode($query_str);	//安卓客户端base64_encode有些问题,暂时使用md5
// 		$base_encode_str = md5($query_str);
		
		$signature = $this->sha256($base_encode_str);
		return $signature;
	}
	/**
	 * @desc: 生成signature前校验参数是否合法
	 * @param: $timestamp 	时间戳
	 * @param: $nonce_str 	随机字符串
	 * @param: $os   		客户端类型 值android或ios
	 * @param: $version 	签名版本 固定值 '1.0.1'
	 * @return: Boolean
	 */
	public function makeSignature($timestamp,$nonce_str,$os,$version){
		$client_time_deviation = $this->client_time_deviation;
		if((abs($timestamp - TIMESTAMP) > $client_time_deviation) && $client_time_deviation){		
			throw new \Exception('timstamp is timeout',401);
		}
		return $this->_makeSignature($timestamp,$nonce_str,$os,$version);
	}
	
	/**
	 * @desc: 校验signature
	 * @param: $signature 	待校验的签名字符串
	 * @param: $timestamp 	时间戳
	 * @param: $nonce_str 	随机字符串
	 * @param: $os   		客户端类型 值android或ios
	 * @param: $version 	签名版本 固定值 '1.0.1'
	 * @return: Boolean
	 */
	public function checkSignature($signature,$timestamp,$nonce_str,$os,$version){
		$mem_cache = $this->cache_pre.$signature;
		if(cache($mem_cache)){
			throw new \Exception('signature has been used',401);
		}
		$client_time_deviation = $this->client_time_deviation;
		if((abs($timestamp - TIMESTAMP) > $client_time_deviation) && $client_time_deviation){
			throw new \Exception('signature is timeout',401);
		}
		$_signature = $this->makeSignature($timestamp,$nonce_str,$os,$version);
		if($signature != $_signature){
			throw new \Exception('invalid signature',400);
		}
		cache($mem_cache,TIMESTAMP,$this->cache_life);
		return true;		
	}
	/**
	 * @desc: 对数组进行字典序排序
	 * @param: $array 需要排序的关联数组
	 * @return: Array
	 */
	private function resort($array){
		$keys = array_keys($array);
 		sort($keys);
		$new_data = [];
		foreach($keys as $v){
			$new_data[$v] = $array[$v];
		}
		return $new_data;
	}
	/**
	 * @desc: php实现sha256加密算法
	 * @param: $data 待加密字符串
	 * @param: $rawOutput 是否返回二进制数据
	 * @return: String or BinaryData
	 */
	public function sha256($data,$rawOutput=false){
		if(!is_scalar($data)){
			return false;
		}
		$data = (string)$data;
		$rawOutput = !!$rawOutput;
		return hash('sha256', $data, $rawOutput);
	}
	
	
	
	
}