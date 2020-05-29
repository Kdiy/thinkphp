<?php
//curl类
namespace app\library\lib\sdk;
class curl{
	
    
	/**
     * 模拟post进行url请求
     * @param string $url
     * @param array $post_data
     */
    public static function post($url = '',$post_data = array(),$decode=true) {
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
		
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);

        return $decode ? json_decode($data,true) : $data;
    }
	public static function get($url,$decode=true){
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        // $result = json_decode($data, true);
        return $decode ? json_decode($data,true) : $data;
	}
	/**
	 * 使用post提交json形式
	 */
	public function post_payload($url,$data,$decode=true){
		$data = json_encode($data);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		$data = curl_exec($curl);
		curl_close($curl);
		return $decode ? json_decode($data,true) : $data;
	}
	
	public function testAction(){
        $url = 'http://mobile.jschina.com.cn/jschina/register.php';
        $post_data['appid']       = '10';
        $post_data['appkey']      = 'cmbohpffXVR03nIpkkQXaAA1Vf5nO4nQ';
        $post_data['member_name'] = 'zsjs124';
        $post_data['password']    = '123456';
        $post_data['email']    = 'zsjs124@126.com';
        //$post_data = array();
        $res = $this->request_post($url, $post_data);       
        print_r($res);

    }
	
	
	
}
?>

