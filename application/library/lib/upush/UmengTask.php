<?php
namespace app\library\lib\upush;

class UmengTask{
	protected $appkey = null;
	
	public function set($key,$val){
		$this->$key = $val;
	}
	public function index(){
		$this->task_cancel(1);
	}
	
	public function task_cancel($task_id){
		dump($this->data);die;
		$url = $this->host . $this->cancelPath;
		$data['appkey'] = '';
		$data['timestamp'] = '';
		$data['task_id'] = '';
		$postBody = json_encode($data);
		
		$this->fetch($url,$postBody);
		
	}
	
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