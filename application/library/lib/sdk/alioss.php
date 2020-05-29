<?php
//文件上传类
namespace app\library\lib\sdk;
// composer require aliyuncs/oss-sdk-php
use OSS\OssClient;
use OSS\Core\OssException;
use app\library\BaseModel;


class alioss extends BaseModel{
	
	public function __construct(){
		parent::__construct();
// 		$this->ossAccessKey = $this->setting['ali_access_key'];
// 		$this->ossAccessToken = $this->setting['ali_access_token'];
		$this->ossAccessKey = config('oss_access_key');
		$this->ossAccessToken = config('oss_access_token');
		$this->ossBucket = OSS_BUCKEY;
		$this->ossRegion = OSS_REGION;
	}
	public function set($key,$value){
        $this->$key = $value;
    }
    public function get($key){
        return $this->$key;
    }
	/**
	 * @desc: 上传文件
	 * @param: $filePath 本地或网络文件地址
	 * @param: $saveName OSS存储的位置和文件名信息,复制远程图片不需要加UPLOAD_PATH
	 * @param: $local	是否为本地文件
	 */
	public function upload($filePath,$saveName,$local=true,$remove=true){
		if(!$local){
			
			$filename = $this->copyLinkImage($filePath, $saveName);
			
			$filePath = BASE_UPLOAD_PATH.$filename;
			if(!$filePath){
				$this->setError('复制远程图片失败');
				return false;
			}
			$saveName = UPLOAD_PATH.$saveName;
		}
		try {
			$ossClient = new OssClient($this->ossAccessKey, $this->ossAccessToken, $this->ossRegion);
			$res = $ossClient->uploadFile($this->ossBucket, $saveName, $filePath);
			// 上传完成后移除本地文件
			if($local && $remove){
				@unlink($filePath);
			}
			
			$this->set('file_name',$res['info']['url']);
			return true;
		} catch (OssException $e) {
			$this->setError($e->getMessage());
			return false;
		}
	}
	// 在oss内复制文件
	public function copyObject($from_object, $to_object)
	{
	    
	    try {
	        $ossClient = new OssClient($this->ossAccessKey, $this->ossAccessToken, $this->ossRegion);
	        $res = $ossClient->copyObject($this->ossBucket, $from_object, $this->ossBucket, $to_object);
	        // 上传完成后移除本地文件
	        
	        
	        $this->set('file_name',$res['info']['url']);
	        return true;
	    } catch (OssException $e) {
	        $this->setError($e->getMessage());
	        return false;
	    }
	    
	    
	}
	
	public function copyLinkImage($url='', $filename){
		if(empty($url)){return false;}		
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$img = curl_exec($ch);
		curl_close($ch);		
		$fp2 = @fopen(BASE_UPLOAD_PATH.$filename, 'a');
		if(!$fp2){
			return false;
		}
		fwrite($fp2, $img);
		fclose($fp2);
		return !file_exists(BASE_UPLOAD_PATH.$filename) ? '' : $filename;
	}
	private function setError($error){
        $this->error = $error;
    }
	
}