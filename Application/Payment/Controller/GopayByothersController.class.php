<?php
namespace Payment\Controller;

class GopayByothersController extends PaymentController{
	


    protected $_mch_id = '0000059790';

    protected $_key = 'a1234567890';

    protected $_app_id = '0000000002000009620';

    protected $_url = 'https://gateway.gopay.com.cn/Trans/WebClientAction.do';

    //     protected $_mch_id = '0000002642';

    // protected $_key = '11111aaaaa';

    // protected $_app_id = '0000000002000000293';

    // protected $_url = 'https://gatewaymer.gopay.com.cn/Trans/WebClientAction.do';

	public function __construct(){
		parent::__construct();
	}

	
    public function PaymentExec($wttl_list, $pfa_list){

        //拼接国富宝的报文
			$arraystr = [

				'version' => '1.1',
				'tranCode' => '4025',
				'customerId' => $pfa_list['mch_id'],
				'payAcctId' => $pfa_list['appid'],
				'merOrderNum' => $wttl_list['orderid'],
				'merURL' => "http://www.boyapay.cn/Payment_GopayByothers_notifyurl.html",
				'tranAmt' => sprintf('%.2f', $wttl_list['money']),
				'recvBankAcctName' => $wttl_list['bankfullname'], 
				'recvBankProvince' => $wttl_list['sheng'],
				'recvBankCity' => $wttl_list['shi'],
				'recvBankName' => $wttl_list['bankname'],
				'recvBankBranchName' => $wttl_list['bankzhiname'],
				'recvBankAcctNum' => $wttl_list['banknumber'],
				'corpPersonFlag' => $wttl_list['additional'][0],
				'tranDateTime' => date('YmdHis',time()),
				'merchantEncode' => '2',
				'approve' => 2,
				// 'settlementToday' => ,
			];

			//请求国富宝接口
			$arraystr['signValue'] = [
				'version' => $arraystr['version'],
				'tranCode' => $arraystr['tranCode'],
				'customerId' => $arraystr['customerId'],
				'merOrderNum' => $arraystr['merOrderNum'],
				'tranAmt' => $arraystr['tranAmt'],
				'feeAmt' => '',
				'totalAmount' => '',
				'merURL' => $arraystr['merURL'],
				'recvBankAcctNum' => $arraystr['recvBankAcctNum'],
				'tranDateTime' => $arraystr['tranDateTime'],
				'orderId' => '',
				'respCode' => '',
				'payAcctId' => $arraystr['payAcctId'],
				'approve' => $arraystr['approve'],
				'VerficationCode' => $pfa_list['signkey'],
			];

			$arraystr['signValue'] = $this->_createSign($arraystr['signValue']);
			// var_dump($arraystr);
			list($return_code, $return_content) = $this->httpPostData($pfa_list['exec_gateway'], http_build_query($arraystr));
			

            $return_data = xmlToArray($return_content, true);
          
			//处理成功返回的数据
			if($return_data['respCode'] == '2'){
				$result = ['status'=>1, 'msg' => '处理中'];
			}else{
                //处理失败的数据
                $last_error = $return_data['errMessage'] ? $return_data['errMessage'] : '请求失败';
                $result = ['status' => 3, 'msg'=>$return_data['retmsg']];
            }
		
		  
			return $result;
	
    }


    protected function _createSign($data){
    	$sign_str = '';
        foreach($data as $k => $vo){
            $sign_str .= $k . '=[' . $vo . ']';
        }
        // echo $sign_str;
        return md5($sign_str);
    }





    public function PaymentQuery($wttl_list, $pfa_list){

       

      
        //拼接国银的报文
        $arraystr =  [  
            'version'  =>  '1.1',
            'tranCode'  => '5570',
            'tranBeginTime' => '',
            'tranEndTime' => '',
            'merOrderNum' => $wttl_list['orderid'],
            'customerId'  => $pfa_list['mch_id'],
            'userAcct'  => $pfa_list['appid'],
            'queryType' => '4025',
            'pageNo'  => '0',
            'merchantEncode'  => '2',

        ];

        $arraystr['signValue'] = array(
            'version' => $arraystr['version'],
            'tranCode' => $arraystr['tranCode'],
            'tranBeginTime' => '',
            'tranEndTime' => '',
            'merOrderNum' => $arraystr['merOrderNum'],
            'customerId' => $pfa_list['mch_id'],
            'queryType' => $arraystr['queryType'],
            'respCode'  => '',
            'userAcct' => $pfa_list['appid'],
            'VerficationCode' => $pfa_list['signkey'],
        );
        $arraystr['signValue'] = $this->_createSign($arraystr['signValue']);

        //请求国银接口
        list($return_code, $return_content) = $this->httpPostData($pfa_list['query_gateway'], http_build_query($arraystr));
        
        $return_data = xmlToArray($return_content, true);

        $resultArr = $return_data['resultArr']['resultRow'];
        //处理成功
        if($resultArr['orgTxnStat'] == '2'){
            $result = ['status'=>2, 'msg' => '处理中'];
        }else if($resultArr['orgTxnStat'] == 6 || $resultArr['orgTxnStat'] == 3){
            $last_error = $return_data['errMessage'] ? $return_data['errMessage'] : '请求失败';
            $result = ['status' => 3, 'msg'=>$return_data['retmsg']];
        }else{
            $result = ['status' => 1, 'msg' => '申请中'];
        }

        return $result;
    }





    public function httpGetData($url){

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   //  信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);        //  检查证书中是否设置域名
        curl_setopt ( $ch, CURLOPT_TIMEOUT, $TIMEOUT);
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $TIMEOUT-2);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
        $return_content = curl_exec( $ch );
        curl_close($ch);
        return $return_content;
    }

    public function httpPostData($url, $data_string){

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
	

	public function callbackurl(){
       
	}

	public function notifyurl(){

        $data = I('post.');
        
        $f = fopen('./api_data.txt', 'a+');
        fwrite($f,serialize($data). "\r\n");
        fwrite($f, "======================================================\r\n");
        fclose($f);
        $sign_array = array(
            'version' => '2.2',
            'tranCode' => $data['tranCode'],
            'customerId'  => $data['merURL'],
            'merOrderNum' => $data['merOrderNum'],
            'tranAmt' =>  $data['tranAmt'],
            'feeAmt' => $data['feeAmt'],
            'totalAmount'  => $data['merURL'],
            'merURL' => $data['merURL'],
            'recvBankAcctNum' => $data['merURL'],
            'tranDateTime' => $data['tranDateTime'],
            'orderId' => $data['orderId'],
            'respCode' => $data['respCode'],
            'VerficationCode' => $this->_key,
        );
        $sign_value = $this->_createSign($sign_array);

       if($sign_value == $data['signValue']  ){

            $wttklist_model = M('Wttklist');
            $wttklist_where = array( 'orderid' => $data['merOrderNum'] );
            $wttklist_list = $wttklist_model->where($wttklist_where)->find();
            $wttklist_list || exit('fail');

            if($data['respCode'] == '2'){
                $wttklist_model->where($wttklist_where)->save(array('status' => 2, 'cldatetime' => date('Y-m-d H:i:s', time())));
                exit('SUCCESS');
            }else if( in_array(array('1', '4', '5', '6', '7'),$data['respCode'] ) ){
                //处理失败的数据
                $last_error = $data['msgExt'] ? json_encode($data['msgExt']) : '请求失败';
                $wttklist_data = array(
                    'status' => 3,
                    'cldatetime' => date('Y-m-d H:i:s', time()),
                    'memo' => $last_error,
                );
                $bool = $wttklist_model->where($wttklist_where)->save($wttklist_data);
                if($bool !== false)
                    M('Member')->where(['id'=>$wttklist_list['userid']])->save(['balance'=>array('exp',"balance+{$wttklist_list['tkmoney']}")]);
            }
        }
	}

	 // 定时检查代付情况
    public function EverCheck(){

        $wttklist_model = M('Wttklist');
        $wttklist_where = array( 'status' => 1 );
        $wttklist_lists = $wttklist_model->where($wttklist_where)->select();
         echo "<pre>";
        if($wttklist_lists){
            foreach($wttklist_lists as $k => $vo){
              
                //拼接的报文
                $arraystr =  array(    
                    'version'  =>  '1.1',
                    'tranCode'  => '5570',
                    'tranBeginTime' => '',
                    'tranEndTime' => '',
                    'merOrderNum' => $vo['orderid'],
                    'customerId'  => $this->_mch_id,
                    'userAcct'  => $this->_app_id,
                    'queryType' => '4025',
                    'pageNo'  => '0',
                    'merchantEncode'  => '1',
                );

                //拼接签名
                $arraystr['signValue'] = array(
                    'version' => $arraystr['version'],
                    'tranCode' => $arraystr['tranCode'],
                    'tranBeginTime' => '',
                    'tranEndTime' => '',
                    'merOrderNum' => $arraystr['merOrderNum'],
                    'customerId' => $this->_mch_id,
                    'queryType' => $arraystr['queryType'],
                    'respCode'  => '',
                    'userAcct' => $this->_app_id,
                    'VerficationCode' => $this->_key,
                );
                $arraystr['signValue'] = $this->_createSign($arraystr['signValue']);

                //请求国银接口
                list($return_code, $return_content) = $this->httpPostData($this->_url, http_build_query($arraystr));

                
                $return_data = xmlToArray($return_content, true);
                if(!$return_data){
                     $return_data = json_decode( 
                            json_encode( 
                                    simplexml_load_string(iconv("UTF-8", "gb2312//IGNORE" , $return_content)) 
                                ) , true
                        );
                }

                $resultArr = $return_data['resultArr']['resultRow'];
        
                //处理成功                
                $wttklist_where['id'] = $vo['id'];
                if($resultArr['orgTxnStat'] == '2'){
                    $wttklist_model->where($wttklist_where)->save(array('status' => 2, 'cldatetime' => date('Y-m-d H:i:s', time())));
                }
                var_dump($return_data);
                sleep(5);    
            }   
           
        }
    }


}