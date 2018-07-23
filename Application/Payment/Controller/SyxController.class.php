<?php
namespace Payment\Controller;

class SyxController extends PaymentController{
	
	public function __construct(){
		parent::__construct();
	}

    public function PaymentExec($wttlList, $pfaList){
       
        
        $arraystr = [
            'service'   => 'agentpay',
            'format'    => 'json',
            'merchantId'    => $pfaList['mch_id'],
            'inputCharset'  => 'UTF-8',
            'outOrderId'    => $wttlList['orderid'],
            'serialNo'  => 1,
            'payMethod' => 'singleAgentPay',
            'payAmount' => $wttlList['money'],
            'cardHolder'    => $wttlList['bankfullname'],
            'bankCardNo'    => $wttlList['banknumber'],
            'bankName'  => $wttlList['bankname'],
            'bankBranchName'    => $wttlList['bankzhiname'],
            'bankProvince'  => $wttlList['sheng'],
            'bankCity'  => $wttlList['shi'],
            'bankCode'  => $wttlList['additional'][0],
            'cardAccountType'   => $wttlList['additional'][1],
        ];
		$key = openssl_get_publickey($pfaList['public_key']);
		openssl_public_encrypt($wttlList['banknumber'], $encrypt, $key);
		$arraystr['bankCardNo'] = base64_encode($encrypt);
		
       
        $arraystr['sign'] = base64_encode( rsaEncryptVerify( md5Sign($arraystr,'','',false), $pfaList['private_key']) );


		
        $arraystr['signType'] = 'RSA';
        $returnData = curlPost($pfaList['exec_gateway'], http_build_query($arraystr),[], 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727;)');
		$returnData = json_decode($returnData, true);
      
        var_dump($returnData);
        if($returnData['retCode'] == '0000'){
            if($returnData['status'] == '00'){
                $result = ['status'=>1, 'msg' => '申请成功'];
            }else if($returnData['status'] == '04'){
                $result = ['status'=>2, 'msg' => '支付成功'];
            }else if($returnData['status'] == '05'){
                $result = ['status'=>3, 'msg' => '申请失败'];
            }
        }else{
            $result = ['status' => 3, 'msg'=>$returnData['retMsg']];
        }
		
        return $result;
    }


    public function PaymentQuery($wttlList, $pfaList){

        $arraystr = [
            'service'   => 'agentpay',
            'format'    => 'json',
            'merchantId'    => $pfaList['mch_id'],
            'inputCharset'  => 'UTF-8',
            'outOrderId'    => $wttlList['orderid'],
            'version'   => '1',
        ];
        $arraystr['sign'] = base64_encode( rsaEncryptVerify( md5Sign($arraystr,'','',false), $pfaList['private_key']) );
        $arraystr['signType'] = 'RSA';
        $returnData = curlPost($pfaList['query_gateway'], http_build_query($arraystr),[], 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727;)');
        $returnData = json_decode($returnData, true);
		$returnData = $returnData['pResult'];
        if($returnData['retCode'] == '0000'){
            if($returnData['status'] == '00'){
                $result = ['status'=>1, 'msg' => '申请成功'];
            }else if($returnData['status'] == '04'){
                $result = ['status'=>2, 'msg' => '支付成功'];
            }else if($returnData['status'] == '05'){
                $result = ['status'=>3, 'msg' => '申请失败'];
            }
        }else{
            $result = ['status' => 3, 'msg'=>$returnData['retMsg']];
        }
        return $result;

    }

	

}