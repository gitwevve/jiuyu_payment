<?php
namespace Pay\Controller;

class YlBankController extends PayController
{

    protected $_bankCode_ = [
        'ABC'   => '103',
        'CCB'   => '105',
        'ICBC'  => '102',
        'BOC'   => '104',
        'CMBC'  => '360',
        'CIB'   => '309',
        'CEB'   => '330',
        // 'SHB'   => '420',
        'CMB'   => '310',
        'BJB'   => '370',
        'PSBC'  => '403',
        'COMM'  => '350',
        'SPAB'  => '340',
        'CITIC' => '106',
        // 'GDB'   => '320',
    ];

    public function Pay($array)
    {
        $orderid = I("request.pay_orderid", '');

        $body = I('request.pay_productname', '');

        $parameter = [
            'code'         => 'YlBank',
            'title'        => '亿联（网银）',
            'exchange'     => 1, // 金额比例
            'gateway'      => '',
            'orderid'      => '',
            'out_trade_id' => $orderid, //外部订单号
            'channel'      => $array,
            'body'         => $body,
        ];

        //支付金额
        $pay_amount = I("request.pay_amount", 0);

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);
        $this->EditMoney($return['orderid'], '', 0);
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $this->_site . 'Pay_YlBank_notifyurl.html';

        $return['callbackurl'] || $return['callbackurl'] = $this->_site . 'Pay_YlBank_callbackurl.html';

        $encryp = encryptDecrypt(serialize($return), 'lgbya!');


        $bankid = I('request.pay_bankid');
        $bankCode = array_key_exists($bankid, $this->_bankCode_) ? $this->_bankCode_[$bankid] : '102';
        $data = [
            'bankCode' => $bankCode,
            'body'      => $body,
            'rpayUrl'   => U('Pay/YlBank/Rpay'),
            'orderid'   => $return['orderid'],
            'money'     => sprintf('%.2f', $return['amount'] / 100),
            'encryp'    => $encryp,
        ];
        R($parameter['code'] .'/Rpay', [$data]);

//        $this->assign([
//            'bankArray' => $this->_bankCode_,
//            'body'      => $body,
//            'rpayUrl'   => U('Pay/YlBank/Rpay'),
//            'orderid'   => $return['orderid'],
//            'money'     => sprintf('%.2f', $return['amount'] / 100),
//            'encryp'    => $encryp,
//        ]);
//        //选择银行的视图，
//        $this->display('BankPay/LsBank');
    }

    public function Rpay($data)
    {

        //接收传输的数据
//        $postData = I('post.', '');
        $postData = $data;

        //将数据解密并反序列化
        $return = unserialize(encryptDecrypt($postData['encryp'], 'lgbya!', 1));

        //检测数据是否正确
        $return || $this->error('传输数据不正确！');

        ($bankCode = $postData['bankCode']) || $this->error('请选择银行');

        $arraystr = [
            'mid'          => $return['mch_id'],
            'orderNo'      => $return['orderid'],
            'amount'       => $return['amount'],
            'notifyUrl'    => $return['notifyurl'],
            'returnUrl'    => $return['callbackurl'],
            'currencyType' => 'CNY',
            'bankCode'     => $bankCode,
            'subject'      => '普通商品',
            'body'         => '普通商品',
            'cardType'     => '01',
            'channel'      => '01',
            'businessType' => '01',
            'noise'        => nonceStr(),
        ];

        $arraystr['sign'] = strtoupper(md5Sign($arraystr, $return['signkey'], '&'));

        echo createForm($return['gateway'], $arraystr);
    }

    public function callbackurl()
    {
        $orderid    = I('request.orderNo', '');
        $pay_status = M("Order")->where(['pay_orderid' => $orderid])->getField("pay_status");
        if ($pay_status != 0) {
            $this->EditMoney($orderid, '', 1);
        } else {
            exit("error");
        }
    }

    public function notifyurl()
    {
        $data = $_REQUEST;
        if ($data['status'] == '1') {
            $sign = $data['sign'];
            unset($data['sign']);
            $key     = getKey($data['orderNo']);
            $md5Sign = strtoupper(md5Sign($data, $key, '&'));
            if ($md5Sign == $sign) {
                $this->EditMoney($data['orderNo'], '', 0);
                echo '{"code":"SUCCESS","msg":"ok"}';
            }
        }
    }

}
