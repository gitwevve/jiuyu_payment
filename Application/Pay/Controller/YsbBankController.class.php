<?php
namespace Pay\Controller;

class YsbBankController extends PayController
{

    protected $_bankCode_ = [
        'ABC'   => 'abc',
        'CMB'   => 'cmb',
        'CCB'   => 'ccb',
        'ICBC'  => 'icbc',
        'COMM'  => 'comm',
        'BOC'   => 'boc',
        'CMBC'  => 'cmbc',
        'CIB'   => 'cib',
        'CEB'   => 'ceb',
        'BJB'   => 'bob',
        'SHB'   => 'bosh',
        'PSBC'  => 'psbc',
        'CITIC' => 'cncb',
    ];

    public function Pay($array)
    {
        $orderid   = I("request.pay_orderid", '');
        $body      = I('request.pay_productname', '');
        $parameter = [
            'code'         => 'YsbBank',
            'title'        => '银生宝网银支付',
            'exchange'     => 1, // 金额比例
            'gateway'      => '',
            'orderid'      => '',
            'out_trade_id' => $orderid, //外部订单号
            'channel'      => $array,
            'body'         => $body,
        ];

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);

        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"]     = $this->_site . 'Pay_YsbBank_notifyurl.html';
        $return['callbackurl'] || $return['callbackurl'] = $this->_site . 'Pay_YsbBank_callbackurl.html';

        $encryp = encryptDecrypt(serialize($return), 'lgbya!');


        $bankid = I('request.pay_bankid');
        $bankCode = array_key_exists($bankid, $this->_bankCode_) ? $this->_bankCode_[$bankid] : 'icbc';

        $data = [
            'bankCode' => $bankCode,
            'body' => $body,
            'orderid' => $return['orderid'],
            'money' => sprintf('%.2f', $return['amount']),
            'encryp' => $encryp,
        ];
        R($parameter['code'] . '/Rpay', [$data]);


//        $this->assign('bankArray', $this->_bankCode_);
//        $this->assign('body', $body);
//        $this->assign('rpayUrl', U('Pay/' . $parameter['code'] . '/Rpay'));
//        $this->assign('orderid', $return['orderid']);
//        $this->assign('money', sprintf('%.2f', $return['amount']));
//
//        $this->assign('encryp', $encryp);
//        选择银行的视图，
//        $this->display('BankPay/YsbBank');
    }

    public function Rpay($data)
    {

        //接收传输的数据
        $post_data = $data;

        //将数据解密并反序列化
        $return = unserialize(encryptDecrypt($post_data['encryp'], 'lgbya!', 1));

        //检测数据是否正确
        $return || $this->error('传输数据不正确！');

        ($bank_code = $post_data['bankCode']) || $this->error('请选择银行');
      
        $arraystr  = [
            'version'      => '3.0.0',
            'merchantId'   => $return['mch_id'],
            'merchantUrl'  => $return['notifyurl'],
            'responseMode' => '3',
            'orderId'      => $return['orderid'],
            'currencyType' => 'CNY',
            'amount'       => (string) $return['amount'],
            'assuredPay'   => 'false',
            'time'         => date('YmdHis'),
            'remark'       => $return['orderid'],
            'bankCode'     => $bank_code,
            'frontURL'     => $return['callbackurl'],
        ];
		
        $sign = "merchantId=";
        $sign .= $arraystr['merchantId'];
        $sign .= "&merchantUrl=";
        $sign .= $arraystr['merchantUrl'];
        $sign .= "&responseMode=";
        $sign .= $arraystr['responseMode'];
        $sign .= "&orderId=";
        $sign .= $arraystr['orderId'];
        $sign .= "&currencyType=";
        $sign .= $arraystr['currencyType'];
        $sign .= "&amount=";
        $sign .= $arraystr['amount'];
        $sign .= "&assuredPay=";
        $sign .= $arraystr['assuredPay'];
        $sign .= "&time=";
        $sign .= $arraystr['time'];
        $sign .= "&remark=";
        $sign .= $arraystr['remark'];
        $sign .= "&merchantKey=";
        $sign .= $return['signkey'];
        $arraystr['mac'] = strtoupper(md5($sign));

        echo createForm($return['gateway'], $arraystr);
    }

    protected function _createSign($data, $key)
    {

        ksort($data);
        foreach ($data as $k => $vo) {
            $sign .= $k . '=' . $vo . '&';
        }
        return strtoupper(md5($sign . 'key=' . $key));
    }

    public function callbackurl()
    {

        $orderid    = I('request.orderId', '');
        $pay_status = M("Order")->where(['pay_orderid' => $orderid])->getField("pay_status");
        if ($pay_status != 0) {
            $this->EditMoney($orderid, '', 1);
        } else {
            exit("error");
        }
    }

    public function notifyurl()
    {
        $data = $_POST;
        if ($data['returnCode'] == '0000') {
            $key = getKey($data['orderId']);
            $mac = $data['mac'];
            unset($data['mac']);
            $sign = 'merchantId=' . $data['merchantId'] .
                '&responseMode=' . $data['responseMode'] .
                '&orderId=' . $data['orderId'] .
                '&currencyType=' . $data['currencyType'] .
                '&amount=' . $data['amount'] .
                '&returnCode=' . $data['returnCode'] .
                '&returnMessage=' . $data['returnMessage'] .
                '&merchantKey=' . $data['merchantKey'] . $key;
            if ($mac == strtoupper(md5($sign))) {
                $this->EditMoney($data['orderId'], '', 0);
            }
        }
    }

}
