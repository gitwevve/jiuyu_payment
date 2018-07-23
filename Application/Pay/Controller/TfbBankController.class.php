<?php
namespace Pay\Controller;

use Org\Util\HttpClient;
use Org\Util\RequestHandler;
use Org\Util\ResponseHandler;
use Org\Util\Rsa;

class TfbBankController extends PayController{


    protected $_bank_array = array(
        'BOC'    => '1000',
        'CCB'       => '1004',
        'ABC'       => '1002',
        'ICBC'      => '1001',
        'BOC'       => '1003',
        'SPDB'      => '1014',
        'CEB'       => '1008',
        'SPAB'    => '1011',
        'CIB'       => '1013',
        'PSBC'      => '1006',
        'CITIC'    => '1007',
        'HXB'       => '1009',
        'CMB'  => '1012',
        'GDB'       => '1017',
        'BJB'      => '1016',
        'SHB'       => '1025',
        'CMBC'      => '1010',
        'COMM'      => '1005',
        // 'BJRCB'     => '1103',
    );

	protected $_pri_pem = '/cert/tfb/rsa_private_key_68.pem';
	
	protected $_pub_pem = '/cert/tfb/gczf_rsa_public_dev.pem';
	
	public function __construct(){
		parent::__construct();
	}

	
	public function Pay($array){

		$orderid = I("request.pay_orderid");
		
		$body = I('request.pay_productname');

		$bankid = I('request.pay_bankid');

		$parameter = array(
			'code' => 'TfbBank',
			'title' => '天付宝',
			'exchange' => 100, // 金额比例
            'gateway' => '',
            'orderid'=>'',
            'out_trade_id' => $orderid, //外部订单号
            'channel'=>$array,
            'body'=>$body
		);

		//支付金额
		$pay_amount = I("request.pay_amount", 0);

		 // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);

        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        
        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $this->_site . 'Pay_TfbBank_notifyurl.html';
        $return['callbackurl'] || $return['callbackurl'] = $this->_site . 'Pay_TfbBank_callbackurl.html';
		$return['callbackurl'] = urlencode($return['callbackurl']);
        $return['pay_memberid'] = I('post.pay_memberid','');

        $encryp = $this->_encryptDecrypt(serialize($return), 'lgbya');
        $bankCode = array_key_exists($bankid, $this->_bank_array) ? $this->_bank_array[$bankid] : '6666';
        $orderid = $return['orderid'];
        $money = sprintf('%.2f', $return['amount']/100 );
        $data = [
            'encryp' => $encryp,
            'bankCode' => $bankCode,
            'orderid' => $orderid,
            'money' => $money
        ];
        R($parameter['code'] .'/Rpay', [$data]);
//        $this->assign('bank_array',$this->_bank_array);
//        $this->assign('rpay_url', U($parameter['code'] .'/Rpay'));
//        $this->assign('orderid', $return['orderid']);
//        $this->assign('body', $body);
//        $this->assign('money', sprintf('%.2f', $return['amount']/100 ));
//        $this->assign('encryp', $encryp);
//        $this->display('BankPay/number');
    }

    protected function _encryptDecrypt($string, $key='',  $decrypt='0'){ 
        if($decrypt){ 
            $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, md5(md5($key))), "12");
            return $decrypted; 
        }else{ 
            $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key)))); 
            
            return $encrypted; 
        } 
    }

    public function Rpay($array){


        //接收传输的数据
        $post_data = $array;
        
        //将数据解密并反序列化
        $return = unserialize( $this->_encryptDecrypt($post_data['encryp'],'lgbya',1) );
        //检测数据是否正确
        $return || $this->error('传输数据不正确！');

//        $bank_code = I('post.bankCode', '6666');
        $bank_code = $array['bankCode'];
        // $bank_code = 6666;
        $bank_code || $this->error('请选择支付的银行');
    
        $card_type = I('post.cardType', '1');
        $card_type || $this->error('请选择卡的类型');

        $user_type = I('post.userType','1');
        $user_type || $this->error('请选择用户的类型');

        // 创建支付请求对象
        $reqHandler = new RequestHandler ();
        // // 通信对象
        // $httpClient = new HttpClient ();
        // // 应答对象
        // $resHandler = new ClientResponseHandler ();

        // -----------------------------
        // 设置请求参数
        // -----------------------------
        $reqHandler->init ();
        $reqHandler->setKey ( $return['signkey'] );
        $reqHandler->setGateUrl ( $return['gateway'] );
        $reqHandler->setPubPem($this->_pub_pem);
        $reqHandler->setPriPem($this->_pri_pem);
        // ----------------------------------------
        // 设置请求参数
        // ----------------------------------------
        $reqHandler->setParameter ( "spid", $return['mch_id'] );
        $reqHandler->setParameter ( "sp_userid", $return['pay_memberid'] );
        $reqHandler->setParameter ( "spbillno", $return['orderid'] );
        $reqHandler->setParameter ( "money", $return["amount"] );
        $reqHandler->setParameter ( "cur_type", 1 );
        $reqHandler->setParameter ( "notify_url", $return['notifyurl'] );
        $reqHandler->setParameter ( "return_url", $return['callbackurl'] );
        // $reqHandler->setParameter ( "errpage_url", $errpage_url );
        $reqHandler->setParameter ( "memo", $return['subject'] );
        // $reqHandler->setParameter ( "expire_time", $expire_time );
        // $reqHandler->setParameter ( "attach", $_POST ["attach"] );
        $reqHandler->setParameter ( "card_type", $card_type );
        $reqHandler->setParameter ( "bank_segment", $bank_code );
        $reqHandler->setParameter ( "user_type", $user_type );
        $reqHandler->setParameter ( "channel", 1 );
        $reqHandler->setParameter ( "encode_type", 'MD5' );
        $reqHandler->setParameter ( "risk_ctrl", "" );
		


		
		
        // 获取debug信息,建议把请求和debug信息写入日志，方便定位问题
        $req_url = $reqHandler->getRequestURL ();
        $debug_info = $reqHandler->getDebugInfo ();
        
        header('Location:' . $req_url);
        
    }
	
	public function callbackurl(){
		$m = new Rsa ($this->_pri_pem, $this->_pub_pem);
		$data = $m->decrypt($_REQUEST['cipher_data']);
        var_dump($data);
        
		parse_str($data,$data);
		
		$order_model = M("Order");
		$pay_status = $order_model->where("pay_orderid = '".$data['spbillno']."'")->getField("pay_status");
		if($pay_status <> 0){
			$this->EditMoney($data['spbillno'], '', 1);
		}else{
			sleep(1);
			$pay_status = $order_model->where("pay_orderid = '".$data['spbillno']."'")->getField("pay_status");
			if($pay_status <> 0){
				$this->EditMoney($data['spbillno'], '', 1);
			}
		}
		exit("error");
	}

	 // 服务器点对点返回
    public function notifyurl(){
        $data = I('request.', '');
		

		
        //解密数据获取订单号 
        $m = new Rsa ($this->_pri_pem, $this->_pub_pem);
        $cipher_data = $m->decrypt($data['cipher_data']);

        parse_str($cipher_data,$cipher_data);
		

		
        if($cipher_data['result'] == 1){
			$this->EditMoney($cipher_data['spbillno'], '', 0);
			header('Content-type:text/xml');
			$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			$xml .= "<root>\n";
			$xml .= "<retcode>00</retcode>\n";
			$xml .= "</root>\n";
			echo $xml;
        }
	}

}