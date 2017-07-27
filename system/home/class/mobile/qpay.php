<?php
/**
 * Created by PhpStorm.
 * User: 刘建凡
 * Date: 2017/6/22
 * Time: 16:28
 */
namespace home\controller;

class qpay extends \home\controller\base
{
    /**
     * 支付宝支付（即时到帐）
     * @param string $ordersn 订单号
     */
    public function pay() {
        $pay_data = array(
            'out_trade_no'  => '201762646'.uniqid(), //订单号
            'subject'       => '测试商品呀',  //标题
            'total_fee'     => 1, //订单金额，单位为元
        );
        $payobj = new \service\shopwap\qpayService();
        $result = $payobj->qpay($pay_data);
        if (!$result) {
            message($payobj->getError(),refresh(),'error');
        }else{
            $cfg = globaSetting();
            //如果是PC端那么返回的是一段 扫码地址
            include themePage('qpay');
        }
    }

    /**
     * 服务器异步通知页面方法
     */
    function notifyUrl()
    {
        $pay = new \service\shopwap\qpayService();
        $result = $pay->notifyUrl();
        if($result){
            ajaxReturnData(1,'支付成功','success');
        }else{
            ajaxReturnData(0,'支付失败','fail');
        }
    }

    /**
     * 同步通知页面跳转处理方法
     */
    function returnUrl()
    {
        $pay = new \service\shopwap\qpayService();
        $result = $pay->returnUrl();
        if($result) {
            message('支付成功！',mobile_url('order',array('name'=>'home')),'success');
        } else {
            message($pay->getError());
        }
    }

}