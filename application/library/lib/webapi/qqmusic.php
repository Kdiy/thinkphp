<?php

namespace app\library\lib\webapi;


class qqmusic extends _base{
	
	
	//搜索
	protected $search = 'https://c.y.qq.com/soso/fcgi-bin/';
	//获取token
	protected $get_token = 'https://c.y.qq.com/base/fcgi-bin/';
	//播放
	protected $song_url = 'http://ws.stream.qqmusic.qq.com/';
	
	public function search($key,$page=1,$pn=10){
		$url = $this->search.'client_search_cp?aggr=1&cr=1&flag_qc=0&p='.$page.'&n='.$pn.'&w='.$key;
		
		$result_str = L('sdk/curl')->get($url,false);
		
		$result = substr($result_str,9,-1);
		
		
		return json_decode($result,true);
	}
	
	public function get_token($songmid,$filename){
		
		$url = $this->get_token.'fcg_music_express_mobile3.fcg?format=json205361747&platform=yqq&cid=205361747&songmid='.$songmid.'&filename='.$filename.'&guid=126548448';
		
		$result_str = L('sdk/curl')->get($url);
		return $result_str;
	}
	
	public function get_url($file_name,$vkey){
		$url = $this->song_url.$file_name.'?fromtag=0&guid=126548448&vkey='.$vkey;
		return $url;
	}
	
	public function song(){
		
		
	}
	
	
	
	
}

