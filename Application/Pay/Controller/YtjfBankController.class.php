<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-09-04
 * Time: 0:25
 */
namespace Pay\Controller;

/**
 * 第三方接口开发示例控制器
 * Class DemoController
 * @package Pay\Controller
 *
 * 三方通道接口开发说明：
 * 1. 管理员登录网站后台，供应商管理添加通道，通道英文代码即接口类名称
 * 2. 用户管理-》通道-》指定该通道（独立或轮询）
 * 3. 用户费率优先通道费率
 * 4. 用户通道指定优先系统默认支持产品通道指定
 * 5. 三方回调地址URL写法，如本接口 ：
 *    异步地址：http://www.yourdomain.com/Pay_Demo_notifyurl.html
 *    跳转地址：http://www.yourdomain.com/Pay_Demo_callbackurl.html
 *
 *    注：下游对接请查看商户API对接文档部分.
 */

class YtjfBankController extends PayController
{
    protected $b2cBank_ = [
        'ICBC' => '888881010031101',
        'ABC'  => '888881010011101',
        'CMBC' => '888881010141101',
        'CCB'  => '888880910101101',
        'CEB'  => '888881010301101',
        'PSBC' => '888881010401101',
        'SHB' => '888881010171101',
        'BJB' => '888881010201101',
        'GBD' => '888881010501101',
    ];
    /**
     *  发起支付
     */
    public function Pay($array)
    {
        $orderid = I("request.pay_orderid");
        $body    = I('request.pay_productname');
        $bankid  = I('request.pay_bankid');
        $return  = $this->getParameter('易通金服', $array, __CLASS__, 100);

        $formData = [
            'version'      => '1.0.0',
            'transCode'    => '8888',
            'merchantId'   => $return['mch_id'],
            'merOrderNum'  => $return['orderid'],
            'bussId'       => $return['appid'],
            'tranAmt'      => $return['amount'],
            'sysTraceNum'  => $return['orderid'],
            'tranDateTime' => date('YmdHis'),
            'currencyType' => 156,
            'merURL'       => $return['callbackurl'],
            'backURL'      => $return['notifyurl'],
            'entryType'    => '1',
         ];
        if (array_key_exists($bankid, $this->b2cBank_)) {
            $formData['bankId'] =$this->b2cBank_[$bankid];
        }

        $txnString =
            $formData['version'] . '|' .
            $formData['transCode'] . '|' .
            $formData['merchantId'] . '|' .
            $formData['merOrderNum'] . '|' .
            $formData['bussId'] . '|' .
            $formData['tranAmt'] . '|' .
            $formData['sysTraceNum'] . '|' .
            $formData['tranDateTime'] . '|' .
            $formData['currencyType'] . '|' .
            $formData['merURL'] . '|' .
            $formData['backURL'] . '|' .
            $formData['orderInfo'] . '|' .
            $formData['userId'];

        // var_dump($txnString . $return['signkey']);exit;
        $formData['signValue'] = md5($txnString . $return['signkey']);

        echo createForm($return['gateway'], $formData);

    }

    public function callbackurl()
    {
        $Order      = M("Order");
        $pay_status = $Order->where("pay_orderid = '" . $_REQUEST["merOrderNum"] . "'")->getField("pay_status");
        if ($pay_status != 0) {
            $this->EditMoney($_REQUEST["merOrderNum"], '', 1);
        } else {
            exit("error");
        }
    }

    /**
     *  服务器通知
     */
    public function notifyurl()
    {
        $postData = I('request.', '');

        if ($postData['respCode'] == '0000') {
            $txnString =
                $postData['transCode'] . '|' .
                $postData['merchantId'] . '|' .
                $postData['respCode'] . '|' .
                $postData['sysTraceNum'] . '|' .
                $postData['merOrderNum'] . '|' .
                $postData['orderId'] . '|' .
                $postData['bussId'] . '|' .
                $postData['tranAmt'] . '|' .
                $postData['orderAmt'] . '|' .
                $postData['bankFeeAmt'] . '|' .
                $postData['integralAmt'] . '|' .
                $postData['vaAmt'] . '|' .
                $postData['bankAmt'] . '|' .
                $postData['bankId'] . '|' .
                $postData['integralSeq'] . '|' .
                $postData['vaSeq'] . '|' .
                $postData['bankSeq'] . '|' .
                $postData['tranDateTime'] . '|' .
                $postData['payMentTime'] . '|' .
                $postData['settleDate'] . '|' .
                $postData['currencyType'] . '|' .
                $postData['orderInfo'] . '|' .
                $postData['userId'];

            $key       = getKey($postData['merOrderNum']);
            $signValue = md5($txnString . $key);
            if ($signValue == $postData['signValue']) {
                $this->EditMoney($postData['merOrderNum'], '', 0);
                exit("success");
            }
        }
    }

}
