<?php

function Model($model='model',$params = array(),$path = ''){
    try{
        $models = explode('/',$model);
        $class = 'app\\library\\'.$path.join('\\',$models);
        $object = new $class();
        if($params){
            call_user_func_array([$object,'__construct'],[$params]);
        }
        return $object;
    }catch(\Exception $e){
        return $e->getMessage();
    }
}
function D($tb_name){
    return new app\library\model($tb_name);
}
function M($model='model',$params = array()){
    return Model($model,$params);
}
function L($model='model',$params = array()){
    return Model($model,$params,'lib\\');
}


function output_data($datas, $extend_data = array()) {
    $data = array();
    $data['code'] = 200;

    if(!empty($extend_data)) {
        $data = array_merge($data, $extend_data);
    }

    $data['datas'] = $datas;
    if(!empty($_GET['callback'])) {
        echo $_GET['callback'].'('.json_encode($data).')';die;
    } else {
        echo str_replace(':null', ':""',json_encode($data,JSON_UNESCAPED_UNICODE));die;
    }
}
function output_error($message, $extend_data = ['code'=>255]) {
    $datas = array('error' => $message);
    output_data($datas, $extend_data);
}
/**
 * @desc: 返回某个动作的结果信息
 * @param: $res boolean 操作结果
 * @param: error 失败信息
 * @param: success 成功信息
 */
function output_msg($res,$error='',$success='OK'){
	return $res ? output_data($success) : output_error($error,array('code'=>255));
}
function output_list($list,$page){
	$data['list'] = $list;
	$data['page'] = $page;
	return output_data($data);
}
/**
 * @desc: 把键值数组转换成原生sql条件语句,支持表达式查询
 * @param: $where_sql 	[Array]  $where_sql['name'] = 123 $where_sql['id'] = ['<>',1];
 * @return: String  name = '123' and id <> '1'
 */
function build_condition($where_sql = []){
	$where_sql_arr = [];
	foreach($where_sql as $k=>$v){
		if(is_array($v)){
			$exp = $v[0];
			$val = $v[1];
			array_push($where_sql_arr,"$k $exp '$val'");
		}else{
			array_push($where_sql_arr,"$k='$v'");
		}
	}
	$sql = join(' and ',$where_sql_arr);
	return $sql;
}
/**
 * @desc: 校验字段值
 * @param: $param 待校验的参数
 * @param: $zero 是否把0作为不满足校验的删选条件
 */
function required($param,$zero = false){
	if(!isset($param))
		return false;
	if($param == '' || $param == 'null' || $param == 'undefined'){
		return false;
	}
	if($zero && $param == 0)
		return false;
	return true;
}
/**
 * @desc: 验证数据是否有效  适用于单条记录验证
 * @param: key  数据对应的key
 * @param: method 提交的方式
 */

function col_validate($key,$method='get',$err_msg=''){
    $val = $method=='get' ? request()->get($key) : request()->post($key);
	if($val=='' || !isset($val)){
		$msg = $err_msg ? $err_msg : 'empty param '.$key;
		output_msg(false,$msg);
	}
	return $val;
}
function check_validate_money($money){
	$rule = '/^(0|[1-9]\d{0,3})(\.\d{1,2})?$/';
	return preg_match($rule, $money);
}
function check_validate($validate_arr,$data){
	$res = form_validate($validate_arr,$data);
	if($res['error']){
		output_msg(false,$res['error']);
	}
}
/**
 * @desc: 验证数据是否有效  适用于表单验证
 * @param: validate_arr 待验证数据 array(array('username','请输入用户名'),array('mobile','请输入手机号'))
 * @param: data 源数据
 */
function form_validate($validate_arr,$data){	
	$check = array();
	foreach($validate_arr as $k=>$v){	
		if(!isset($data[$v[0]]) || $data[$v[0]] === 'undefined' || $data[$v[0]] === ''){
			$check['error'] = $v[1];
			return $check;
		}
	}
	return $check;	
}
/**
 * @desc 重命名数组key,保留有效数据 适用于过滤form表单,重组成符合数据表字段的数据
 * @param: alias 重命名数组 array('old_name'=>'new_name','old_name'=>'new_name')
 * @param: data 源数据 
 * @param: filter  是否移除空值 移除空值适合搜索功能
 * @param: $remove_zero 是否移除0 
 */
function array_alias($alias,$data,$filter=false,$remove_zero=false){
	$datas = array();
	if($filter){
		if($remove_zero){
			foreach($alias as $k=>$v){
			
				if(!empty($data[$k]) &&  $data[$k]!='undefined'){
					$datas[$v] = $data[$k];	
				}
			}
		}else{
			foreach($alias as $k=>$v){
			
				if(isset($data[$k]) && $data[$k]!='' &&  $data[$k]!='undefined'){
					$datas[$v] = $data[$k];	
				}
			}
		}
		
		return $datas;
	}
	foreach($alias as $k=>$v){
		$datas[$v] = $data[$k];
	}
	return $datas;
}
/**
 * @desc: 二位数组根据key值去重
 * @param: $arr 要去重的数组 
 * @param: $key   键值
 * @return: array
 */
function assoc_unique($arr, $key){
	$tmp_arr = array();
    foreach ($arr as $k => $v) {
        if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
            unset($arr[$k]);
        } else {
            $tmp_arr[] = $v[$key];
        }
    }
    return array_values($arr);
}
/**
 * @desc: 重组图片,取得完整url
 * @param: base 存储地址 如 https://tp5.com/uploads/
 * @param: filename 文件名 如 test.png
 */
function formateURL($base,$filename){
	return empty($filename) ? '' : $base.$filename;
}
function formateDate($timestamp,$formate='Y.m.d H:i'){
	return date($formate,$timestamp);
}
// 格式化为多久之前
function formateBefore($timestamp){
	if($timestamp == 0){
		return '未知';
	}
	$sum = TIMESTAMP - $timestamp;
	$day = floor($sum / 86400);
	if($day<1){
		$hour = floor($sum / 3600);
		if($hour<1){
			$minutus = floor($sum / 60);
			$msg = $minutus < 1 ? $sum.'秒前' : $minutus.'分钟前';
		}else{
			$msg = $hour.'小时前';
		}
	}else{
		$msg = $day.'天前';
	}
	
	return $msg;
}
// 获取文件mime类型
function getFileMime($filename){
    $extension = pathinfo($filename,PATHINFO_EXTENSION);
    switch($extension){
        case 'jpg':
        case 'png':
        case 'jpeg':
        case 'gif':
        case 'bmp':
            return 'img';
        case 'mp4':
        case 'avi':
            return 'video';            
    }
    return '';
}
// 获取视频对应的截图地址
function getFileScreen($filename){
    $is_video = getFileMime($filename) == 'video' ? true : false;
    if($is_video){
        $path_info = pathinfo($filename);
        return $path_info['dirname'].'/'.$path_info['filename'].'.jpg';        
    }
    return $filename;
}


/**
 * @desc: 图片base64编码
 * @param: $image_file 图片路径
 */

function base64EncodeImage ($image_file) {
	$base64_image = '';
	$image_info = getimagesize($image_file);
	$image_data = fread(fopen($image_file, 'r'), filesize($image_file));
	$base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
	return $base64_image;
}
/**
 * @desc string类型转obj,防止浏览器转义
 * @param  $str:string
 * @return mixed
 */
function str_to_arr($str){
    $str = htmlspecialchars_decode($str);
    return json_decode($str,true);
}

function get_real_ip(){
	$ip=false;
	if(!empty($_SERVER["HTTP_CLIENT_IP"])){
		$ip = $_SERVER["HTTP_CLIENT_IP"];
	}
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
		if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
		for ($i = 0; $i < count($ips); $i++) {
			if (!preg_match  ("^(10|172\.16|192\.168)\.", $ips[$i])) {
				$ip = $ips[$i];
				break;
			}
		}
	}
	return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}


/**
 * @desc: 复制远程图片
 * @param: $url 	远程图片路径
 * @param: save_dir 存储路径
 * @param: filename 存储文件名
 */
function copyLinkImage($url='', $save_dir = './', $filename = '', $type = 0){
	if(empty($url)){return '';}
	if (trim($filename) == '') {
		$filename = time() . '.jpg';
	}
	
	//获取远程文件所采用的方法
	if ($type) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$img = curl_exec($ch);
		curl_close($ch);
	} else {
		ob_start();
		readfile($url);
		$img = ob_get_contents();
		ob_end_clean();
	}
	//$size=strlen($img);
	//文件大小
	$fp2 = @fopen($save_dir . $filename, 'a');
	fwrite($fp2, $img);
	fclose($fp2);
	unset($img, $url);
	return $filename;
}
//处理分页
function page($total){	
	$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
	$pn = !empty($_GET['pn']) ? intval($_GET['pn']) : 10;		
	$total_page = ceil($total / $pn);
	$page_info = array(
		'current_page' =>$page,
		'total_page' =>$total_page,
		'total_num' =>$total
	);
	return $page_info;
}



?>