<?php
require_once('function_inc.php');
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件



define('TIMESTAMP',time());

define('APPPATH',__DIR__);



$ssl = $_SERVER['HTTPS'] =='on' ? 'https://' : 'http://';

define('HOST_URL',$ssl.$_SERVER['HTTP_HOST'].'/');

//用户上传目录
define('UPLOAD_PATH','data/upload/');
//用户上传目录  绝对路径
define('BASE_UPLOAD_PATH',ROOT_PATH.'../'.UPLOAD_PATH);
//用户头像上传目录
define('AVATAR_PATH','avatar/');
//身份证信息
define('CARD_AUTH', 'idcard/');
// 发货凭证
define('EXPRESS_PATH', 'express/');




//公司图片\文件上传目录
define('COMPANY_IMG_PATH','companyImg/');
//商品发布图片/文件上传目录
define('PRODUCTS_IMG_PATH','productsImg/');

// 商品类目图标
define('SALE_CLASS', 'sale_class/');

//代办图片/文件上传目录
define('INTERMEDIARY_PATH','intermediary/');
//首页轮播图上传目录
define('INDEX_BANNER_PATH','index_banner/');
//首页广告图上传目录
define('INDEX_ADV_PATH','index_adv/');
//首页导航图上传目录
define('INDEX_NAV_PATH','index_nav/');
//入驻相关图片目录
define('JOIN_IN_PATH','join_in/');
//退款图片路径
define('BACK_FEE_IMG_PATH','back_fee_img/');
//苹果圈图片路径
define('FRIEND_CICLE_PATH','friend_cicle/');
//新闻图片路径
define('NEWS_PATH','news/');
//广告图片路径
define('ADVERTISEMENT_PATH','advertisement/');
//Im_app_id
define('IM_APP_ID','f61580b1e9bc4ef9');
//Im_app_secrect
define('IM_APP_SECRECT','31f7526af61580b1e9bc4ef94b0a4cd6');
define('OSS_ENABLE',false);
define('QINIU_ENABLE',false);
