<?php
namespace Pay\Controller;

use Org\Util\Jp\util\DesHelper;
use Org\Util\Jp\JppSdk;
use Org\Util\Jp\util\SignMaker;
use Org\Util\Jp\util\RsaEncryptor;
class JpKjController extends PayController{
	
	public function __construct(){

    parent::__construct();

    // p12证书存放路径
    defined('JPP_CERT_FILE') or define('JPP_CERT_FILE',  '');
    // p12证书解包密码
    defined('JPP_CERT_PWD') or define('JPP_CERT_PWD', '');
    // 商户ID
    defined('JPP_MERCHANT_ID') or define('JPP_MERCHANT_ID', '');
    // 服务URLhttps://jd.kingpass.cn/paygateway/mpsGate/mpsTransaction
    defined('JPP_MERCHANT_URL') or define('JPP_MERCHANT_URL', 'https://jd.kingpass.cn/paygateway/mpsGate/mpsTransaction'); //'http://43.227.141.32/paygateway/mpsGate/mpsTransaction'

    defined('JPP_MERCHANT_PAY_GATEWAY_URL') or define('JPP_MERCHANT_PAY_GATEWAY_URL', 'https://jd.kingpass.cn/paygateway/paygateway/bankPayment'); 
     //'http://43.227.141.32/paygateway/paygateway/bankPayment'
	}

	public function Pay($array){

    $orderid = I("request.pay_orderid");
    $body = I('request.pay_productname');
    $notifyurl = $this->_site ."Pay_JpKj_notifyurl.html"; //异步通知

    $parameter = array(
      'code' => 'JpKj',
      'title' => '九派快捷支付',
      'exchange' => 100, // 金额比例
      'gateway' => '',
      'orderid'=>'',
      'out_trade_id' => $orderid, //外部订单号
      'channel'=>$array,
      'body'=>$body
    );
    // 订单号，可以为空，如果为空，由系统统一的生成
    $return = $this->orderadd($parameter);
    //如果生成错误，自动跳转错误页面
    $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
   
    //异步回调页面，优先取数据库中的异步回调
    $return["notifyurl"] && $return["notifyurl"] = $notifyurl;
    $callbackurl = $this->_site . 'Pay_JpKj_callbackurl?orderid=' . $return['orderid'] . '&a=a.html'; //跳转通知


    //获取请求的url地址
    $url=$return["gateway"];
    $card_bind_data = $this->_bindCard($return);
    $all_data = array_merge($card_bind_data,$return);
    $pay_quick_data = $this->_quickPayInit($all_data);
    $encryp = $this->_encryptDecrypt(serialize($all_data), 'lgbya');
    $this->assign('callbackurl', $callbackurl);
    $this->assign('encryp', $encryp);
    $this->assign('contractId', $all_data['contract_id']);
    $this->assign('memberId', $all_data['memberid']);
    $this->assign('money', sprintf('%.2f',$return['amount']/100) );
    $this->assign('orderid', $return['orderid']);
    $this->assign('rpay_url', U($parameter['code'] . '/ajaxRpay'));
    $this->display('BankPay/jp_send_code');
	}


  public function ajaxRpay(){

    //接收传输的数据
    $post_data = I('post.','');
    
    //将数据解密并反序列化
    $return = unserialize( $this->_encryptDecrypt($post_data['encryp'],'lgbya',1) );
    
    //检测数据是否正确
    $return || $this->error('传输数据不正确！');
    ($return['phone_code'] = $post_data['phoneCode']) || $this->error('请填写手机验证码！');

    $ret = $this->_uickPayCommit($return);
	
    $ret['rspCode'] == 'IPS00000' || $this->showmessage($ret['rspMessage']);


    $this->ajaxReturn(array('status'=>'ok','msg'=>'成功'));
  }

  public function notifyurl(){

    $data = I('post.','');
    ksort($data);
    $sign = $data['serverSign'];
    unset($data['serverSign']);
    $cert = $data['serverCert'];
    unset($data['serverCert']);
 
    $normalText = SignMaker::normalResponse($data);

    // 如果返回不用验签（比如B2C的返回跳转url的）直接返回
    /**
     *  此接口为 rpmBankPayment B2C银行卡支付，必须要有web页面的跳转，
     *  此接口的返回无须做返回结果的验签，由九派支付的网关直接跳转
     *  当前demo未提供页面的部分，所以此处直接处理为不对 response 做校验
     */
    if ($service == 'rpmBankPayment') {
        return $post;
    }
    // 返回结果验签
    $checkResult = RsaEncryptor::RSAVerify($normalText, $sign, $cert);
    if ($checkResult == RsaEncryptor::VERIFY_SUCCESS && $data['orderSts'] == 'PD') {
        $this->EditMoney($data["orderId"], '', 0);
        exit('success');
    }
  }


  // 页面通知返回
  public function callbackurl(){
      $Order = M("Order");
      for($i=0;$i<=10;$i++){
        sleep(1);
        $pay_status = $Order->where("pay_orderid = '".$_REQUEST["orderid"]."'")->getField("pay_status");
        if($pay_status <> 0){
            $this->EditMoney($_REQUEST["orderid"], '', 1);

        }else{
            exit("error");
        }
      }
  }


  //ajax请求发短信
  public function ajaxSms(){
    $sms_data = array();
    $contractid = I('post.contractId','');
    $contractid || $this->ajaxReturn(array('status'=>'error','msg'=>'缺少绑卡协议！'));
    $memberid = I('post.memberId','');
    $memberid || $this->ajaxReturn(array('status'=>'error','msg'=>'缺少用户号'));
    $params = [
        'contractId' => $contractid,
        'memberId'   => $memberid,
    ];
    $ret = JppSdk::smsSend($params);
    $ret['rspCode'] == 'IPS00000' || $this->showmessage($ret['rspMessage']);
    $this->ajaxReturn(array('status'=>'ok','msg'=>'成功'));
  }




  //请求绑卡协议id
  protected function _bindCard($return){

    //获取下游上传的数据，并判断是否正确
    $bind_card_params['card_num'] = I('post.card_num', '');
    $bind_card_params['card_num'] || $this->showmessage('银行卡参数card_num不为空');

    $bind_card_params['card_name'] = I('post.card_name', '');
    $bind_card_params['card_name']  || $this->showmessage('姓名参数card_name不为空');

    $bind_card_params['id_num'] = I('post.id_num', '');
    $bind_card_params['id_num'] || $this->showmessage('身份证参数id_num不为空');

    $bind_card_params['phone'] = I('post.phone', '');
    $bind_card_params['phone'] || $this->showmessage('银行预留手机号参数phone不为空');

    $bind_card_params['orderid'] = $return['orderid'];
    $bind_card_params['memberid'] = I('post.pay_memberid','');

    //获取用户绑卡信息
    $card_bind_model = M('CardBind');
    $card_bind_where = array(
                          'card_num' => $bind_card_params['card_num'],
                          'card_name' => $bind_card_params['card_name'],
                          'id_num' => $bind_card_params['id_num'],
                          'phone' => $bind_card_params['phone'],
                        );
    $card_bind_data = $card_bind_model->where($card_bind_where)->find();
    

    //判断用户是否已经绑卡
    if(!$card_bind_data){
      //加密敏感信息
      $id_num = DesHelper::desEncode($bind_card_params['id_num']);
      $card_num = DesHelper::desEncode($bind_card_params['card_num']);
      //拼接绑卡报文
      $card_bind_request_params = array(
        'memberId'   => $bind_card_params['memberid'],
        'orderId'    => $bind_card_params['orderid'],
        'idType'     => '00',
        'idNo'       => $id_num,
        'userName'   => $bind_card_params['card_name'],
        'phone'      => $bind_card_params['phone'],
        'cardNo'     => $card_num,
        'cardType'   => '0',
      );

      //发送请求
      $card_bind_return_datas = JppSdk::bindCard($card_bind_request_params);

      //如果请求成功判断是否存在绑卡协议，如果存在则记录到数据库中
      if($card_bind_return_datas['contractId']){
        $card_bind_data = $bind_card_params;
        $card_bind_data['contract_id'] = $card_bind_return_datas['contractId'];
        $card_bind_model->add($card_bind_data) || $this->showmessage('请求超时请重新请求');
      }else{
        $this->showmessage($card_bind_return_datas['rspMessage']);
      } 
    }
    return $card_bind_data;
  }

  // 快捷支付发起
  protected function _quickPayInit($array){
      $params   = [
          'memberId'         => $array['memberid'],
          'orderId'          => $array['orderid'],
          'contractId'       => $array['contract_id'],
          'payType'          => 'DQP', //通道类型 String（3）只能取以下枚举值 DQP:借记卡快捷 CQP:信用卡快捷【暂不支持】
          'amount'           => $array['amount'], //交易金额 String(11)以分为单位,有效长度1-11
          'currency'         => 'CNY', //交易币种 String(32)默认CNY, 即人民币
          'orderTime'        => date('YmdHis', time()), //格式YYYYMMDDHHmmss
          'clientIP'         => '127.0.0.1', //商户发送的客户端IP
          'validUnit'        => '00', //订单有效期单位 Number(2) 只能取以下枚举值 00-分 01-小时 02-日 03-月
          'validNum'         => '1', //订单有效期数量
          'offlineNotifyUrl' => $array['notifyurl'], //异步通知url String(256) 交易结果通过后台通知到这个 url，建议DES加密
      ];
      $ret = JppSdk::quickPayInit($params);
      $ret['rspCode'] == 'IPS00000' || $this->showmessage($ret['rspMessage']);

      return $ret;
  }

  // 快捷支付提交
  protected function _uickPayCommit($array){
    $params   = [
        'memberId'         => $array['memberid'],
        'orderId'          => $array['orderid'],
        'contractId'       => $array['contract_id'],
        'checkCode'        => $array['phone_code'], //短信校验码 String(8) 短信校验码，只能是数字
        'payType'          => 'DQP', //通道类型 String（3）只能取以下枚举值 DQP:借记卡快捷 CQP:信用卡快捷【暂不支持】
        'amount'           => $array['amount'], //交易金额 String(11)以分为单位,有效长度1-11
        'currency'         => 'CNY', //交易币种 String(32)默认CNY, 即人民币
        'orderTime'        => date('YmdHis', time()), //格式YYYYMMDDHHmmss
        'clientIP'         => '127.0.0.1', //商户发送的客户端IP
        'validUnit'        => '00', //订单有效期单位 Number(2) 只能取以下枚举值 00-分 01-小时 02-日 03-月
        'validNum'         => '3',
        'offlineNotifyUrl' => $array['notifyurl'], //异步通知url String(256) 交易结果通过后台通知到这个 
    ];
    $ret = JppSdk::quickPayCommit($params);
    return $ret;
  }

 


  //用来加密传输的数据
  protected function _encryptDecrypt($string, $key='',  $decrypt='0'){ 
        if($decrypt){ 
            $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, md5(md5($key))), "12");
            return $decrypted; 
        }else{ 
            $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key)))); 
            return $encrypted; 
        } 
  }

}