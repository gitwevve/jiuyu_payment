<?php
namespace Pay\Controller;
use Org\Util\AesCtr;
use Org\Util\Aes;
class SyxBankController extends PayController
{

    protected $b2cBank_ = [
        // 'SPDB' => 'SPDB', 
        'PSBC' => 'PSBC', 
        'ICBC' => 'ICBC',
        'CITIC' => 'CITIC', 
        'CIB'  => 'CIB', 
        'CEB'  => 'CEB',
        'CCB'  => 'CCB',
        'BOC'  => 'BOC',
        'ABC'  => 'ABC',
        // 'HXB'  => 'HXB',
    ];

    // public $b2bBank_ = [
    //     'CMB' => 'CMB',
    //     'CCB'  => 'CCB',
    //     'BOC'  => 'BOC',
    //     'ABC'  => 'ABC',
    // ];

    // public $userType_ = [
    //     'B2C' => '个人',
    //     'B2B' => '企业',
    // ];

    // public $cardType_ = [
    //     '01' => '借记卡',
    //     '02' => '贷记卡',
    // ];

    private $privateKey_ = './cert/syx/private.txt';
    
    private $publicKey_ = './cert/syx/public.txt';
    
   
    public function Pay($array)
    {
      
        $orderid = I("request.pay_orderid", '');
        
        $body = I('request.pay_productname', '');

        $parameter = [
            'code' => 'SyxBank',
            'title' => '商银信（网关支付）',
            'exchange' => 1, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
        ];

        //支付金额
        $pay_amount = I("request.pay_amount", 0);

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);
      
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        
        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $this->_site . 'Pay_SyxBank_notifyurl.html';
        
        $return['callbackurl'] || $return['callbackurl'] = $this->_site . 'Pay_SyxBank_callbackurl.html';
        
        $encryp = encryptDecrypt(serialize($return), 'lgbya!');

        $bankid = I('request.pay_bankid');
        $bankCode = array_key_exists($bankid, $this->b2cBank_) ? $this->b2cBank_[$bankid] : 'CEB';

        $data = [
            'bankCode' => $bankCode,
            'orderid' => $return['orderid'],
            'body' => $body,
            'money' => $return['amount'],
            'encryp' => $encryp,

        ];

        R($parameter['code'] .'/Rpay', [$data]);

//        $this->assign('b2cBank',$this->b2cBank_);
//        // $this->assign('b2bBank',$this->b2bBank_);
//        // $this->assign('cardType',$this->cardType_);
//        // $this->assign('userType',$this->userType_);
//
//        $this->assign('rpayUrl', U($parameter['code'] .'/Rpay'));
//        $this->assign('orderid', $return['orderid']);
//        $this->assign('body', $body);
//        $this->assign('money',  $return['amount']);
//        $this->assign('encryp', $encryp);
//        $this->display('BankPay/SyxBank');
    }

  
    public function Rpay($data){

        //接收传输的数据
        $post_data = $data;
        
        //将数据解密并反序列化
        $return = unserialize(encryptDecrypt($post_data['encryp'],'lgbya!',1) );
        //检测数据是否正确
        $return || $this->error('传输数据不正确！');

//        $bank_code = I('post.bankCode', '');
        $bank_code = $data['bankCode'];
        $bank_code || $this->error('请选择支付的银行');
    
        $arraystr = [
                    'service' => 'directPay',
                    'merchantId' => $return['mch_id'],
                    'notifyUrl' => $return['notifyurl'],
                    'returnUrl' => $return['callbackurl'],
                    'inputCharset' => 'UTF-8',
                    'outOrderId'    => $return['orderid'],
                    'subject'   => '支付产品',
                    'body'  => '支付产品',
                    'transAmt'  => sprintf('%.2f', $return['amount']),
                    'payMethod' => 'bankPay',
                    'defaultBank' => $bank_code ,
                    'channel' => 'B2C',
                    'cardAttr' => '01',            
                ];
                
        $string = md5Sign($arraystr, '', '', false);

        $arraystr['sign'] = base64_encode( rsaEncryptVerify($string, $this->privateKey_) );  
        $arraystr['signType'] = 'RSA';
        echo createForm($return['gateway'], $arraystr);
    }
  

    public function callbackurl(){
        $orderid = I('request.outOrderId','');
        $payStatus = M("Order")->where(['pay_orderid'=>$orderid])->getField("pay_status");
        if($payStatus <> 0){
            $this->EditMoney($orderid, '', 1);
        }else{
            exit("error");
        }
    }

    public function notifyurl(){
        $data = I('request.', '');
        // $f = fopen('api_data.txt', 'a+');
        // fwrite($f, serialize($data));
        // fwrite($f, '=======================\r\n');
        // fclose($f);
        if($data['tradeStatus'] == '2'){
            $sign = $data['sign'];
            $sign=str_replace('*','+',$sign);
            $sign=str_replace('-','/',$sign);
            $sign=base64_decode($sign);
            unset($data['sign']);
            $string = md5Sign($data, '', '', false);
            $newSign = rsaEncryptVerify($string, $this->publicKey_, $sign);
            if($newSign == $sign ){
                $this->EditMoney($data['outOrderId'], '', 0);
            }
        }
    }
       
}
?>