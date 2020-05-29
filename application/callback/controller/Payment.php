<?php

namespace app\callback\controller;

use think\Controller;
use Yansongda\Pay\Pay;


// 对于系统内部错误导致业务没有处理完成的订单,对支付平台服务不进行响应,等待其再次进行推送,直接系统处理业务完成

class Payment extends Controller{
    
    // 支付宝订单异步通知地址
    public function alipay(){
        
        
        $alipay = L('sdk/alipay')->getInterface();
        
        try{
            $data = $alipay->verify();  // 验签
            trace($data->all(),'alipay');
            
            $pay_data = $data->all();
            
            if($pay_data['trade_status']=='TRADE_SUCCESS' || $pay_data['trade_status']=='TRADE_FINISHED'){
                // 对应处理业务信息
                $result = M('pay_notify')->alipay($pay_data);
                if($result){
                    return $alipay->success();
                }
            }
            
        } catch (\Exception $e) {
            trace($e->getMessage(),'pay');
        }
        die;
        
    }
    // 微信订单异步通知地址
    public function wxpay(){
        $wechat = L('sdk/wxpay')->getInterface();
        
        try{
            $result = $wechat->verify();
            
            //  ....
            $pay_data = $result->all();
            trace($pay_data,'wxpay');
            
            if($pay_data['return_code']=='SUCCESS' && $pay_data['result_code']=='SUCCESS'){
                $result = M('pay_notify')->wxpay($pay_data);
                if($result){
                    return $wechat->success();
                }
            }
        } catch (\Exception $e) {
            trace($e->getMessage(),'pay');
            #die('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
        }
        die;
    }
    
    
    
}