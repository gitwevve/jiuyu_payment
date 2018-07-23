<?php
namespace Payment\Controller;

use Org\Util\HttpClient;
use Org\Util\RequestHandler;
use Org\Util\ResponseHandler;
use Org\Util\Rsa;
class TfbByothersController extends PaymentController{
	
	public function __construct(){
		parent::__construct();
	}

    public function PaymentExec($wttl_list, $pfa_list){
        header('Content-type: text/html; charset=utf-8'); 
        
        //拼接的报文
        $money = $wttl_list['money'] * 100;
        $reqHandler = new RequestHandler ();
        $reqHandler->init ();
        $reqHandler->setMbConvert();
        $reqHandler->setKey ( $pfa_list['signkey'] );
        $reqHandler->setGateUrl ( $pfa_list['exec_gateway'] );
        $reqHandler->setPubPem( $pfa_list['public_key'] );
        $reqHandler->setPriPem( $pfa_list['private_key'] );     
        $reqHandler->setParameter ( "version", "1.0" );
        $reqHandler->setParameter ( "spid", $pfa_list['mch_id'] );
        $reqHandler->setParameter ( "sp_serialno", $wttl_list['orderid']);
        $reqHandler->setParameter ( "sp_reqtime", date('YmdHis', time()) );
        $reqHandler->setParameter ( "tran_amt", $money );
        $reqHandler->setParameter ( "cur_type", "1" );
        $reqHandler->setParameter ( "acct_name", iconv("UTF-8", "GBK", $wttl_list['bankfullname']) );
        $reqHandler->setParameter ( "acct_id", $wttl_list['banknumber'] );
        $reqHandler->setParameter ( "acct_type", $wttl_list['accounttype']  );
        $reqHandler->setParameter ( "bank_settle_no", $wttl_list['banklinked'] );
        $reqHandler->setParameter ( "business_type", "20101" );
        $reqHandler->setParameter ( "memo", "tx" . $wttl_list['userid'] );
        $req_url = $reqHandler->getRequestURL ();
        $debug_info = $reqHandler->getDebugInfo ();
        list($return_code, $return_content) = $this->httpPostData($req_url);
      
        $return_data = xmlToArray($return_content);
        if($return_data['retcode'] == '00'){

            //解密数据
            $m = new Rsa ($pfa_list['private_key'], $pfa_list['public_key']);
            $return_data['cipher_data'] = iconv("UTF-8", "GBK", $return_data['cipher_data']); 
            $cipher_data = $m->decrypt($return_data['cipher_data']);
            parse_str($cipher_data,$cipher_data);

			//处理成功返回的数据
            if($cipher_data['serialno_state'] == 1){
                $result = ['status'=>1, 'msg' => '申请成功'];
            }else if ($cipher_data['serialno_state'] == 2) {
                $result = ['status'=>2, 'msg' => '付款成功'];
            }else if ($cipher_data['serialno_state'] == 3){
                $result = ['status' => 3, 'msg'=>'代付失败！'];
            }
        }else{
			$result = ['status' => 3, 'msg'=>$return_data['retmsg']];
		}
        return $result;
    }


    public function PaymentQuery($wttl_list, $pfa_list){

      
        $reqHandler = new RequestHandler ();
        $reqHandler->init ();
        $reqHandler->setMbConvert();
        $reqHandler->setKey ( $pfa_list['signkey'] );
        $reqHandler->setGateUrl ( $pfa_list['query_gateway'] );
        $reqHandler->setPubPem( $pfa_list['public_key'] );
        $reqHandler->setPriPem( $pfa_list['private_key'] ); 
        $reqHandler->setParameter ( "version", "1.0" );
        $reqHandler->setParameter ( "spid", $pfa_list['mch_id'] );
        $reqHandler->setParameter ( "sp_serialno", $wttl_list['orderid']);
        $reqHandler->setParameter ( "sp_reqtime", date('YmdHis', time()) );
        $req_url = $reqHandler->getRequestURL ();
        $debug_info = $reqHandler->getDebugInfo ();
        list($return_code, $return_content) = $this->httpPostData($req_url);
        $return_data = xmlToArray($return_content);

        if($return_data['retcode'] == '00'){

            //解密数据
            $m = new Rsa ($pfa_list['private_key'], $pfa_list['public_key']);
            $return_data['cipher_data'] = iconv("UTF-8", "GBK", $return_data['cipher_data']); 
           
            $cipher_data = $m->decrypt($return_data['cipher_data']);

            parse_str($cipher_data,$cipher_data);
                        //处理成功返回的数据
            if ($cipher_data['serialno_state'] == 1) {
                $result = ['status'=>2, 'msg' => '付款成功'];
            }else if ($cipher_data['serialno_state'] == 3 ){
                $result = ['status' => 3, 'msg'=>'代付失败！'];
            }else if($cipher_data['serialno_state'] == 4){
                $result = ['status' => 3, 'msg'=>'已退汇'];
            }
        }else{
			$result = ['status' => 3, 'msg'=>$return_data['retmsg']];
		}


        return $result;

    }

    public function httpPostData($url, $data_string=''){

        $cacert = ''; //CA根证书  (目前暂不提供)
        $CA = false ;   //HTTPS时是否进行严格认证
        $TIMEOUT = 30;  //超时时间(秒)
        $SSL = substr($url, 0, 8) == "https://" ? true : false;
        
        $ch = curl_init ();
        if ($SSL && $CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   //  只信任CA颁布的证书
            curl_setopt($ch, CURLOPT_CAINFO, $cacert);      //  CA根证书（用来验证的网站证书是否是CA颁布）
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    //  检查证书中是否设置域名，并且是否与提供的主机名匹配
        } else if ($SSL && !$CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  //  信任任何证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);    //  检查证书中是否设置域名
        }

        curl_setopt ( $ch, CURLOPT_TIMEOUT, $TIMEOUT);
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $TIMEOUT-2);
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data_string );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded') );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();

        $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
       
        curl_close($ch);
        return array (
            $return_code,
            $return_content
        );
    }
	

}