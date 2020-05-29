<?php
/**
 * 微信支付
 *
 */
namespace app\library\lib\sdk;
use think\Exception;
use Yansongda\Pay\Pay;
class wxpay{
	
	private $config = [
		'appid' => '', // APP APPID
		'app_id' => '', // 公众号 APPID
		'miniapp_id' => '', // 小程序 APPID
		'mch_id' => '',
		'key' => '',
		'notify_url' => '',
		'cert_client' => 'apiclient_cert.pem', // optional, 退款，红包等情况时需要用到
		'cert_key' => 'apiclient_key.pem',// optional, 退款，红包等情况时需要用到
	    
	    'log' => [ // optional
			#'file' => './logs/wechat.log',
			'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
			'type' => 'single', // optional, 可选 daily.
			'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
		],
		'http' => [ // optional
			'timeout' => 5.0,
			'connect_timeout' => 5.0,
			// 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
		],
		// 'mode' => 'dev',
	];
	public function __construct(){
	    $setting = M('memcache')->_sys_settins();
	    $this->config['appid'] = $setting['wx_open_appid'];
	    $this->config['mch_id'] = $setting['wxpay_mch_id'];
	    $this->config['key'] = $setting['wxpay_key'];
	}
	private $pay_way = ['scan','wap','app','mp'];		//允许的支付方式
	
	public function set($key,$value){
		$this->config[$key] = $value;
		return $this;
	}
	public function get($key){
		return $this->config[$key];
	}
	public function getInterface(){
		return Pay::wechat($this->config);
	}
	/**
	 * @desc: 发起支付请求
	 * @param: $order_sn 订单号
	 * @param: $price 价格 分
	 * @param: $title 商品名称
	 * @param: $type 支付方式  可选   scan  wap  app 	mp
	 * @return: scan 返回供生成二维码的数据 wap  301重定向至支付页   app返回提供给支付sdk的字符串	
	 */
	
	
	// 修改vendor包 symfony/http-foundation/Response.php
	// 添加方法 
	/**
		public function getLocation(){
			return $this->headers->get('Location');
		}
	
	*/
	public function pay($order_sn,$price,$title,$pay_way='wap',$openid=''){
		$order = [
            'out_trade_no' => $order_sn,
            'total_fee' => $price,
            'body' => $title,
        ];
		if($pay_way == 'mp'){
			$order['openid'] = $openid;
		}
		$wxpay = Pay::wechat($this->config);
		if(!in_array($pay_way,$this->pay_way)) throw new Exception('invalid param pay_way');
		
		switch($pay_way){
			case 'scan':
				return $wxpay->scan($order);
				break;
			case 'wap':
				return $wxpay->wap($order)->getLocation();
				//return $wxpay->wap($order)->isRedirect();
				break;
			case 'app':
				$data = $wxpay->app($order)->getContent();
				return json_decode($data,true);
				break;
			case 'mp':
				return $wxpay->mp($order);
				break;
			default: 
				return $wxpay->wap($order)->send();
				break;
		}
	}	
	/**
	 * @desc: 根据订单号查询订单
	 * @param: $order_sn 支付订单号
	 * @return:  array  ||  err_msg
	 */
	public function find($order_sn){
		try{
		      
			return Pay::wechat($this->config)->find(['out_trade_no'=>$order_sn]);
		}catch (\Exception $e) {
			return $e->getMessage();
		}
	}
	
	
	/**
	 * @desc: 退款
	 * @param: $order_sn 订单号
	 * @param: $out_refund_no	退款单号
	 * @param: $total_fee		订单金额	分
	 * @param: $refund_money  退款金额  	分
	 * @param: $desc		退款说明
	 */
	public function refund($order_sn,$out_refund_no,$total_fee,$refund_money,$desc='退款'){
		$order = [
			'out_trade_no' => $order_sn,
			'out_refund_no' => $out_refund_no,
			'total_fee' => $total_fee,
			'refund_fee' => $refund_money,
			'refund_desc' => $desc,
		];
		return Pay::wechat($this->config)->refund($order);
	}
	/**
	 * @desc: 关闭订单
	 * @param: order_sn
	 */
	public function close($order_sn){

		$order = [
			'out_trade_no' => $order_sn,
		];
		return Pay::wechat($this->config)->close($order);
		
	}
	
}