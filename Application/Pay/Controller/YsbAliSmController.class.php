<?php
namespace Pay\Controller;

class YsbAliSmController extends PayController
{

    public function Pay($array)
    {
        $return = $this->getParameter('银生宝支付宝扫码', $array, __CLASS__);

        $arraystr = [
            'accountId'   => $return['mch_id'],
            'payType'     => '1',
            'orderId'     => $return['orderid'],
            'commodity'   => '123',
            'amount'      => sprintf('%.2f', $return['amount']),
            'responseUrl' => $return['notifyurl'],
        ];

        $sign = 'accountId=' . $arraystr['accountId'] .
            '&payType=' . $arraystr['payType'] .
            '&orderId=' . $arraystr['orderId'] .
            '&commodity=' . $arraystr['commodity'] .
            '&amount=' . $arraystr['amount'] .
            '&responseUrl=' . $arraystr['responseUrl'] .
            '&key=' . $return['signkey'];

        $arraystr['mac'] = strtoupper(md5($sign));

        $result = curlPost($return['gateway'], json_encode($arraystr, JSON_UNESCAPED_SLASHES), ['Content-Type: application/json; charset=utf-8']);
        $result = json_decode($result, true);

        if ($result['result_code'] == '0000') {
            $this->showQRcode($result['qrcode'], $return, 'alipay');
        }

    }

    public function callbackurl()
    {
        $orderid    = I('request.orderid', '');
        $pay_status = M("Order")->where(['pay_orderid' => $orderid])->getField("pay_status");
        if ($pay_status != 0) {
            $this->EditMoney($orderid, '', 1);
        } else {
            exit("error");
        }
    }

    public function notifyurl()
    {
        $data = file_get_contents('php://input');

       

        $data = json_decode($data,true);


        if ($data['result_code'] == '0000') {
            $key  = getKey($data['orderId']);

            $accountId = M('Order')->where(['pay_orderid'=>$data['orderId']])->getField('account');

            $mac  = $data['mac'];
            $sign = 
                'accountId=' . $accountId .
                '&orderId=' . $data['orderId'] .
                '&amount=' . $data['amount'] .
                '&result_code=' . $data['result_code'] .
                '&result_msg=' . $data['result_msg'] .
                '&key=' . $key;
            if ($mac == strtoupper(md5($sign))) {
                $this->EditMoney($data['orderId'], '', 0);
            }
        }
    }

}
