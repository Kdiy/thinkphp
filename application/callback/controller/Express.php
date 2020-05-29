<?php
namespace app\callback\controller;

// 物流信息推送
class Express extends BaseCtl{
    
    // 快递100的订阅消息推送
    public function express100(){
        $post_data = request()->post();
        
        $params_str = $post_data['param'];
        
        $poll_data = json_decode($params_str,true);
        
        $sign = $post_data['sign'];
        
        $model = L('sdk/express100');
        
        try {
            $model->valid($sign,$params_str);   //校验签名
            
            $result = $poll_data['lastResult']; //最近一次结果
            // 如果系统提交的快递编码和实际的快递有偏差,会接收到推送纠正后的编码
            $express_code = $result['com']; //物流公司代号
            $old_expres_code = $express_code;
            if($poll_data['autoCheck'] == 1){
                $express_code = $poll_data['comNew'];
                $old_expres_code = $poll_data['comOld'];
            }
            $receive_time = strtotime($result['data'][0]['time']);
            $express_no = $result['nu'];        //快递单号
            $express_state = $result['state'];      //快递状态
            $express_data = json_encode($result['data']);   //物流信息
            $poll_state = $poll_data['status'];     //监控状态
            if(!$express_no || !$express_code){
                throw new \Exception('订单信息参数不全');
            }
            $update['push_time'] = TIMESTAMP;
            $update['express_code'] = $express_code;
            $update['express_state'] = $express_state;
            $update['express_data'] = $express_data;
            $update['poll_state'] = $poll_state;
            
            $express_model = M('express');
            $order_info = $express_model->getExpressData($old_expres_code,$express_no);
            if(!$order_info){
                throw new \Exception('没有订单信息');
            }
            $order_id = $order_info['order_id'];
            $res = $express_model->editByOrderId($order_id,$update,$receive_time);
            if(!$res){
                throw new \Exception('修改监控数据失败');
            }
            return $model->success();
            
        }catch (\Exception $e){
            trace($e->getMessage(),'express');
            return $model->error($e->getMessage());
        }
    }
    
    
    public function callback(){
        
        
        
    }
    
    
    
    
}


