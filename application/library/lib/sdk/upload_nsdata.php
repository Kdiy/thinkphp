<?php
//文件上传类 针对app的16进制上传
namespace app\library\lib\sdk;
class upload_nsdata{
	
	/**
     * 文件存储路径
     */
    private $save_path;
	
	private $extension = '';
    /**
     * 允许上传的文件类型
     */
    private $allow_type=array('gif','jpg','jpeg','bmp','png','swf','tbi','xls','xlsx');
    /**
     * 允许的最大文件大小，单位为KB
     */
    private $max_size = '2048';
    /**
     * 改变后的图片宽度
     */
    private $thumb_width = 0;
    /**
     * 改变后的图片高度
     */
    private $thumb_height = 0;
    /**
     * 生成扩缩略图后缀
     */
    private $thumb_ext = false;
    /**
     * 允许的图片最大高度，单位为像素
     */
    private $upload_file;
    /**
     * 是否删除原图
     */
    private $ifremove = false;
    /**
     * 上传文件名
     */
    public $file_name;

    /**
     * 默认文件存放文件夹
     */
    private $default_dir = ATTACH_PATH;
    /**
     * 错误信息
     */
    public $error = '';
    /**
     * 生成的缩略图，返回缩略图时用到
     */
    public $thumb_image;
	/*
	 * 设置
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key,$value){
        $this->$key = $value;
    }
    /**
     * 读取
     */
    public function get($key){
        return $this->$key;
    }
	
	/**
     * 上传操作
     *
     * @param string $binary 16进制数据
     * @return bool
     */
    public function upfile($binary,$video_type=''){
		


		//验证文件格式是否为系统允许
		if(!in_array($this->extension,$this->allow_type)){
			//$error = 'image_allow_ext_is'.implode(',',$this->allow_type);
			$error = '上传文件格式有误';
			$this->setError($error);
			return false;
		}

        if(!$this->validate($binary)){
			$error = '文件数据有误';
			$this->setError($error);
			return false;
		}

        //设置图片路径
        $this->save_path = rtrim($this->setPath(),DS);

        //设置文件名称
        if(empty($this->file_name)){
            $this->setFileName();
        }

        //是否需要生成缩略图
        $ifresize = false;
        if ($this->thumb_width && $this->thumb_height && $this->thumb_ext){
            $thumb_width 	= explode(',',$this->thumb_width);
            $thumb_height 	= explode(',',$this->thumb_height);
            $thumb_ext 		= explode(',',$this->thumb_ext);
            if (count($thumb_width) == count($thumb_height) && count($thumb_height) == count($thumb_ext)) $ifresize = true;
        }

        //计算缩略图的尺寸
//         if ($ifresize){
//             for ($i=0;$i<count($thumb_width);$i++){
//                 $imgscaleto = ($thumb_width[$i] == $thumb_height[$i]);
//                 if ($image_info[0] < $thumb_width[$i]) $thumb_width[$i] = $image_info[0];
//                 if ($image_info[1] < $thumb_height[$i]) $thumb_height[$i] = $image_info[1];
//                 $thumb_wh = $thumb_width[$i]/$thumb_height[$i];
//                 $src_wh	 = $image_info[0]/$image_info[1];
//                 if ($thumb_wh <= $src_wh){
//                     $thumb_height[$i] = $thumb_width[$i]*($image_info[1]/$image_info[0]);
//                 }else{
//                     $thumb_width[$i] = $thumb_height[$i]*($image_info[0]/$image_info[1]);
//                 }
//                 if ($imgscaleto){
//                     $scale[$i]  = $src_wh > 1 ? $thumb_width[$i] : $thumb_height[$i];
// //					if ($this->config['thumb_type'] == 'gd'){
// //						$scale[$i]  = $src_wh > 1 ? $thumb_width[$i] : $thumb_height[$i];
// //					}else{
// //						$scale[$i]  = $src_wh > 1 ? $thumb_width[$i] : $thumb_height[$i];
// //					}
//                 }else{
//                     $scale[$i] = 0;
//                 }
// //				if ($thumb_width[$i] == $thumb_height[$i]){
// //					$scale[$i] = $thumb_width[$i];
// //				}else{
// //					$scale[$i] = 0;
// //				}
//             }
//         }


        if ($this->error != '') return false;

       
        // $re=@move_uploaded_file($this->upload_file['tmp_name'],BASE_UPLOAD_PATH.DS.$this->save_path.DS.$this->file_name);
        $re = file_put_contents(BASE_UPLOAD_PATH.DS.$this->save_path.DS.$this->file_name,$this->binaryTrans($binary));

        if($re){

            if(!empty($video_type)){
                return $this->file_name;
            }

            //产生缩略图
//             if ($ifresize){
//                 $resizeImage	= new ResizeImage();
//                 $save_path = rtrim(BASE_UPLOAD_PATH.DS.$this->save_path,'/');
//                 for ($i=0;$i<count($thumb_width);$i++){
//                     $resizeImage->newImg(
//                         $save_path.DS.$this->file_name,
//                         $thumb_width[$i],
//                         $thumb_height[$i],
//                         $scale[$i],
//                         $thumb_ext[$i].'.',
//                         $save_path,
//                         $this->filling
//                     );
//                     if ($i==0) {
//                         $resize_image = explode('/',$resizeImage->relative_dstimg);
//                         $this->thumb_image = $resize_image[count($resize_image)-1];
//                     }
//                 }
//             }
          
            return true;
        }else{
            $this->setError('上传失败');
            return false;
        }
//		$this->setErrorFileName($this->upload_file['tmp_name']);
        return $this->error;
    }
	/**
     * 设置文件名称 不包括 文件路径
     *
     * 生成(从2000-01-01 00:00:00 到现在的秒数+微秒+四位随机)
     */
    private function setFileName(){
        $tmp_name = sprintf('%010d',time() - 946656000)
            . sprintf('%03d', microtime() * 1000)
            . sprintf('%04d', mt_rand(0,9999));
        $this->file_name = (empty ( $this->fprefix ) ? '' : $this->fprefix . '_')
            . $tmp_name . '.' .$this->extension;
    }
	
	private function validate($str){
		$str = str_replace('<','',$str);
		$str = str_replace('>','',$str);
		$str_arr = explode(' ',$str);
		$result = true;
		foreach($str_arr as $v){
			if(!ctype_xdigit($v)){
				$result = false;
				var_dump($v);
				break;
				return $result;
			}
		}
		return $result;	
	} 
	/**
	 * 16进制转2进制
	 *
	 */
	private function binaryTrans($byte){
		$byte = str_replace(' ','',$byte);   //处理数据 
		$byte = str_ireplace("<",'',$byte);
		$byte = str_ireplace(">",'',$byte);
		$byte = pack("H*",$byte);      //16进制转换成二进制
		return $byte;
	}
	
	/**
     * 裁剪指定图片
     *
     * @param string $field 上传表单名
     * @return bool
     */
    public function create_thumb($pic_path){
        if (!file_exists($pic_path)) return ;

        //是否需要生成缩略图
        $ifresize = false;
        if ($this->thumb_width && $this->thumb_height && $this->thumb_ext){
            $thumb_width 	= explode(',',$this->thumb_width);
            $thumb_height 	= explode(',',$this->thumb_height);
            $thumb_ext 		= explode(',',$this->thumb_ext);
            if (count($thumb_width) == count($thumb_height) && count($thumb_height) == count($thumb_ext)) $ifresize = true;
        }
        $image_info = @getimagesize($pic_path);
        //计算缩略图的尺寸
        if ($ifresize){
            for ($i=0;$i<count($thumb_width);$i++){
                $imgscaleto = ($thumb_width[$i] == $thumb_height[$i]);
                if ($image_info[0] < $thumb_width[$i]) $thumb_width[$i] = $image_info[0];
                if ($image_info[1] < $thumb_height[$i]) $thumb_height[$i] = $image_info[1];
                $thumb_wh = $thumb_width[$i]/$thumb_height[$i];
                $src_wh	 = $image_info[0]/$image_info[1];
                if ($thumb_wh <= $src_wh){
                    $thumb_height[$i] = $thumb_width[$i]*($image_info[1]/$image_info[0]);
                }else{
                    $thumb_width[$i] = $thumb_height[$i]*($image_info[0]/$image_info[1]);
                }
                if ($imgscaleto){
                    $scale[$i]  = $src_wh > 1 ? $thumb_width[$i] : $thumb_height[$i];
                }else{
                    $scale[$i] = 0;
                }
            }
        }
        //产生缩略图
//         if ($ifresize){
//             $resizeImage	= new ResizeImage();
//             $save_path = rtrim(BASE_UPLOAD_PATH.DS.$this->save_path,'/');
//             for ($i=0;$i<count($thumb_width);$i++){
// //				$resizeImage->newImg($save_path.DS.$this->file_name,$thumb_width[$i],$thumb_height[$i],$scale[$i],$thumb_ext[$i].'.',$save_path,$this->filling);
//                 $resizeImage->newImg($pic_path,$thumb_width[$i],$thumb_height[$i],$scale[$i],$thumb_ext[$i].'.',dirname($pic_path),$this->filling);
//             }
//         }
    }
	/**
     * 设置存储路径
     *
     * @return string 字符串形式的返回结果
     */
    public function setPath(){

		/**
		 * 判断目录是否存在，如果不存在 则生成
		 */

		if (!is_dir(BASE_UPLOAD_PATH . DS . $this->default_dir)) {
			$dir = $this->default_dir;
			$dir_array = explode(DS, $dir);
			$tmp_base_path = BASE_UPLOAD_PATH;
			foreach ($dir_array as $k => $v) {
				$tmp_base_path = $tmp_base_path . DS . $v;
				if (!is_dir($tmp_base_path)) {
					if (!@mkdir($tmp_base_path, 0755,true)) {
						$this->setError('无法创建目录' . $tmp_base_path);
						return false;
					}
				}
			}
			unset($dir, $dir_array, $tmp_base_path);
		}

		//设置权限
		@chmod(BASE_UPLOAD_PATH . DS . $this->default_dir, 0755);

		//判断文件夹是否可写
		if (!is_writable(BASE_UPLOAD_PATH . DS . $this->default_dir)) {
			$this->setError('写入文件失败' . $this->default_dir);
			return false;
		}
      
        return $this->default_dir;
    }
	/**
     * 设置错误信息
     *
     * @param string $error 错误信息
     * @return bool 布尔类型的返回结果
     */
    private function setError($error){
        $this->error = $error;
    }

    /**
     * 根据系统设置返回商品图片保存路径
     */
    public function getSysSetPath($image_dir_type=5){
		
        switch($image_dir_type){
            case "1":
                //按文件类型存放,例如/a.jpg
                $subpath = "";
                break;
            case "2":
                //按上传年份存放,例如2011/a.jpg
                $subpath = date("Y",time()) . "/";
                break;
            case "3":
                //按上传年月存放,例如2011/04/a.jpg
                $subpath = date("Y",time()) . "/" . date("m",time()) . "/";
                break;
            case "4":
                //按上传年月日存放,例如2011/04/19/a.jpg
                $subpath = date("Y",time()) . "/" . date("m",time()) . "/" . date("d",time()) . "/";
				break;
			case "5":
				//按上传年月日存放,例如20110419/a.jpg
				$subpath = date("Ymd") . "/";
				break;
        }
        return $subpath;
    }

	
	
}