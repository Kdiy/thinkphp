<?php
/**
 * 高德地图
 *
 */
namespace app\library\lib\sdk;

class amap{
    
    protected $config = array(
        'amapKey' => '',
		'android_key' =>'',
		'js_key'	=>'',
		'api_key'	=>'',
        'requestURL' => 'https://restapi.amap.com/v3/',
    );
	/**
	 * @desc: 输入关键词转换为可供地图使用的信息
	 */
	public function formateMapInfo($keyword){
		$curl_model = L('sdk/curl');
        $url = $this->config['requestURL'].'config/district?key='.$this->config['amapKey'].'&keywords='.$keyword.'&subdistrict=0';
		
        $res = $curl_model->get($url);
		$mapInfo = array();
		if($res['status']==1 && count($res['districts'])>0){
			$mapInfo = $res['districts'];
		}
        return $mapInfo;
		
	}
	/**
	 * @desc: pois搜索
	 */
	private function searchByTypeAndKeyWord($keyword='',$city='',$types=120000,$cityLimit='true'){
		$curl_model = L('sdk/curl');
		$url = $this->config['requestURL'].'place/text?key='.$this->config['amapKey'].'&keywords='.$keyword.'&types='.$types.'&city='.$city.'&citylimit='.$cityLimit;
		// dump($url);
        $res = $curl_model->get($url);

		$pois = array();
		if($res['status']==1 && count($res['pois'])>0){
			$pois = $res['pois'];
		}
        return $pois;
	}
	/**
	 * @desc: 搜索指定城市的小区
	 */
	public function searchCommunity($keyword,$adcode,$types=120300){
		return $this->searchByTypeAndKeyWord($keyword,$adcode,$types);		
	}
	
	
	
	
	/**
	 * @desc: 地址转坐标
	 */
    public function addressFormate($address){
        $curl_model = L('sdk/curl');
        $post_data = array(
            'key' =>$this->config['amapKey'],
            'address' =>$address
        );       
        $str = '';
        foreach($post_data as $k=>$v){
            $str.= '&'.$k.'='.$v;
        }
        $url = $this->config['requestURL'].'geocode/geo?parameters'.$str;
        $res = $curl_model->get($url);
        return $res['status']=='1'? $res['geocodes'] : '';
    }
	/**
	 * @desc: 高德逆地址解析 坐标转地址
	 */
    public function pointFormate($longitude,$latitude){
        $curl_model = L('sdk/curl');
        $param = '&key='.$this->config['amapKey'].'&location='.$longitude.','.$latitude;
        $url = $this->config['requestURL'].'geocode/regeo?parameters'.$param;
        $res = $curl_model->get($url);
	
        return $res['status']=='1' ? $res['regeocode'] : '';
    }
	/**
	 * @desc: 高德地址解析 地址转坐标系
	 * @param: address
	 * @return [longitude,latitude]
	 */
    public function addressToPoint($address){
        $info = $this->addressFormate($address);
        if(!empty($info)){
            $point = explode(',',$info[0]['location']);
            return array($point[0],$point[1]);
        }
        return array('','');
    }
	/**
	 * @desc: 高德逆地址解析
	 * @param: longitude
	 * @param: latitude
	 * @return address [string]
	 */
    public function pointToAddress($longitude,$latitude){
		$info = $this->pointFormate($longitude,$latitude);
		if(!empty($info)){
			return $info;
		}
		return '';
	}
	/**
	 * @desc: 根据经纬度计算距离
	 * @param: $lng1
	 * @param: $lat1
	 * @param: $lng2
	 * @param: $lat2
	 * @param: $decimal=1	保留小数点后几位
	 */
	public function getDistanceByPoint($lng1,$lat1,$lng2,$lat2,$decimal=1){
		$radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
		$radLat2=deg2rad($lat2);
		$radLng1=deg2rad($lng1);
		$radLng2=deg2rad($lng2);
		$a=$radLat1-$radLat2;
		$b=$radLng1-$radLng2;
		$s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137;
		return round($s,$decimal);
		
	}
	/**
	 * @desc: 获取天气
	 * @param: city  城市adcode或citycode
	 */
	public function get_weather($city){
		$url = $this->config['requestURL'].'weather/weatherInfo?key='.$this->config['amapKey'].'&city='.$city.'&extensions=all';
		$result = L('sdk/curl')->get($url);
		return $result;
		
	}
	/**
	 * @desc: 根据IP定位
	 * @param: ip
	 * @return array('adcode','city')
	 */
	public function geolocation_by_ip($ip){
		$url = $this->config['requestURL'].'ip?key='.$this->config['api_key'].'&ip='.$ip.'&output=JSON';
		
		$result = L('sdk/curl')->get($url);
		
		return $result;
	}
	
	
	
	
	
	
	
	
	
}	
?>