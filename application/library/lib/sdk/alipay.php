<?php
/**
 * 支付宝支付
 * composer require yansongda/pay
 * https://pay.yanda.net.cn/docs/2.x/overview
 */
namespace app\library\lib\sdk;
use think\Exception;
use Yansongda\Pay\Pay;
use app\library\lib\sdk\alipay_server;
use think\console\input\Argument;

class alipay{
	protected $config = [
		'notify_url' => '',//支付成功后,支付宝异步通知地址
        'return_url' => '',//支付成功后跳转的页面
		// 沙箱
		'app_id' => '',
		//对应支付宝平台的支付宝公钥
		'ali_public_key' => '',
		// 工具生成的商户应用私钥
		'private_key' => '',
	    
		'log' => [ // optional
            'file' => ROOT_PATH.'./runtime/log/pay/alipay.log',
            'level' => 'debug', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,         
        ],
        //'mode' => 'dev', // optional,设置此参数，将进入沙箱模式	 dev	
    ];
	public function __construct(){
	    $setting = M('memcache')->_sys_settins();
	    $this->config['app_id'] = $setting['alipay_app_id'];
	    $this->config['ali_public_key'] = $setting['alipay_public_key'];
	    $this->config['private_key'] = $setting['alipay_private_key'];
	}
	private $pay_way = ['web','wap','app'];		//允许的支付方式
	
	public function set($key,$value){
		$this->config[$key] = $value;
		return $this;
	}
	public function get($key){
		return $this->config[$key];
	}
	public function getInterface(){
		return Pay::alipay($this->config);
	}
	/**
	 * @desc: 发起支付请求
	 * @param: $order_sn 订单号
	 * @param: $price 价格元
	 * @param: $title 商品名称
	 * @param: $type 支付方式  可选   web   wap  app 
	 * @return: web | wap  301重定向至支付页   app返回提供给支付sdk的字符串
	 */
	
	public function pay($order_sn,$price,$title,$pay_way='wap'){
		$order = [
            'out_trade_no' => $order_sn,
            'total_amount' => $price,
            'subject' => $title,
        ];
		
		$alipay = Pay::alipay($this->config);
		if(!in_array($pay_way,$this->pay_way)) throw new Exception('invalid param pay_way');
		
		switch($pay_way){
			case 'web':
				return $alipay->web($order)->send();
				break;
			case 'wap':
				return $alipay->wap($order)->send();
				break;
			case 'app':
				return $alipay->app($order)->getContent();
				break;
			default: 
				return $alipay->web($order)->send();
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
			return Pay::alipay($this->config)->find(['out_trade_no'=>$order_sn]);
		}catch (\Exception $e) {
			return $e->getMessage();
		}
	}
	/**
	 * @desc: 退款
	 * @param: $order_sn 订单号
	 * @param: $refund_money  退款金额  元
	 */
	public function refund($order_sn,$refund_money){
	   
		$order = [
			'out_trade_no' => $order_sn,
			'refund_amount' => $refund_money,
		];
		
		return Pay::alipay($this->config)->refund($order);
	}
	/**
	 * @desc: 关闭订单
	 * @param: order_sn
	 */
	public function close($order_sn){
		// try{
			$order = [
				'out_trade_no' => $order_sn,
			];
			return Pay::alipay($this->config)->close($order);
		// }catch (\Exception $e) {
			// return $e->getMessage();
		// }
	}
	
	/**
	 * @desc: 转账到指定账号
	 * @param: $payAmount 转账金额 最低0.1元	required
	 * @param: $outTradeNo 转账订单号			required
	 * @param: $account 收款人账号 				required
	 * @param: $realName 收款人真实姓名
	 * @param: $remark 转账备注
	 * @return: Array(
			'state'=> boolean	转账是否成功
			'errmsg' =>  // 转账错误信息
			'data'	=>Array(
				out_biz_no		转账订单号
				order_id		支付宝转账订单号	
				msg				Success 成功
				pay_date		转账时间
			)
		)
	 */
	public function transfer($payAmount,$outTradeNo,$account,$realName='',$remark=''){
		$aliPay = new alipay_server($this->config['app_id'],$this->config['private_key']);
		
		$result = $aliPay->doPay($payAmount,$outTradeNo,$account,$realName,$remark);
		$result = $result['alipay_fund_trans_toaccount_transfer_response'];
		
		$data['state'] = false;
		
		if($result['code'] && $result['code']=='10000'){
			$data['state'] = true;
			$data['data'] = $result;
		}else{
			$data['errmsg'] = $result['msg'].' : '.$result['sub_msg'];
		}
		return $data;
		
		
	}
	
	
}