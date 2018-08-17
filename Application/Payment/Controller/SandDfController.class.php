<?php
namespace Payment\Controller;

class SandDfController extends PaymentController{


    private $privateKey_;

    private $publicKey_ = './cert/sande/sand.cer';

    private $private_pwd ;

    private $priKey;
    private $pubKey;
    private $bankMap;

	public function __construct(){
        parent::__construct();

        $this->bankMap = [
            '北京银行	' => '',
            '东亚银行	' => '',
            '中国工商银行	' => '',
            '中国光大银行	' => '',
            '广发银行	' => '',
            '华夏银行	' => '',
            '中国建设银行	' => '',
            '交通银行	' => '',
            '中国民生银行	' => '',
            '南京银行	' => '',
            '宁波银行	' => '',
            '中国农业银行	' => '',
            '平安银行	' => '',
            '上海银行	' => '',
            '上海浦东发展银行	' => '',
            '深圳发展银行	' => '',
            '兴业银行	' => '',
            '中国邮政储蓄银行	' => '',
            '招商银行	' => '',
            '浙商银行	' => '',
            '中国银行	' => '',
            '中信银行	' => '',
            '支付宝	' => '',
            '微信支付	' => '',
        ];
	}



    public function PaymentExec($wttlList, $pfaList){
        $this->initKey($pfaList);

        /**id	bankcode	bankname	images
162	BOB	'北京银行	'BOB.gif
164	BEA	'东亚银行	'BEA.gif
165	ICBC	'中国工商银行	'ICBC.gif
166	CEB	'中国光大银行	'CEB.gif
167	GDB	'广发银行	'GDB.gif
168	HXB	'华夏银行	'HXB.gif
169	CCB	'中国建设银行	'CCB.gif
170	BCM	'交通银行	'BCM.gif
171	CMSB	'中国民生银行	'CMSB.gif
172	NJCB	'南京银行	'NJCB.gif
173	NBCB	'宁波银行	'NBCB.gif
174	ABC	'中国农业银行	'5414c87492ad8.gif
175	PAB	'平安银行	'5414c0929a632.gif
176	BOS	'上海银行	'BOS.gif
177	SPDB	'上海浦东发展银行	'SPDB.gif
178	SDB	'深圳发展银行	'SDB.gif
179	CIB	'兴业银行	'CIB.gif
180	PSBC	'中国邮政储蓄银行	'PSBC.gif
181	CMBC	'招商银行	'CMBC.gif
182	CZB	'浙商银行	'CZB.gif
183	BOC	'中国银行	'BOC.gif
184	CNCB	'中信银行	'CNCB.gif
193	ALIPAY	'支付宝	'58b83a5820644.jpg
194	WXZF	'微信支付	'58b83a757a298.jpg
         **/


	    $info = array(
            'transCode' => 'RTPM', // 实时代付
            'merId' => $pfaList['mch_id'], // 此处更换商户号
            'pt' => array(
                'orderCode' => $wttlList['orderid'],
                'version' => '01',
                'productId' => '00000004',
                'tranTime' => date('YmdHis', time()),
                'tranAmt' => str_pad(bcmul($wttlList['money'], 100, 2), 12, 0, STR_PAD_LEFT),
                'currencyCode' => '156',
                'accAttr' => '0',
                'accNo' => $wttlList['banknumber'],
                'accType' => '4',
                'accName' => $wttlList['bankfullname'],
                'remark' => 'pay',
                'payMode' => '2',
                'channelType' => '07'
            )
        );

        // step1: 拼接报文及配置
        $transCode = $info['transCode']; // 交易码
        $accessType = '0'; // 接入类型 0-商户接入，默认；1-平台接入
        $merId =  $info['merId']; // 此处更换商户号

        $pt = $info['pt']; // 报文

// step2: 生成AESKey并使用公钥加密
        $AESKey = $this->aes_generate(16);
        $encryptKey = $this->RSAEncryptByPub($AESKey, $this->pubKey);

// step3: 使用AESKey加密报文
        $encryptData = $this->AESEncrypt($pt, $AESKey);

// step4: 使用私钥签名报文
        $sign = $this->sign($pt, $this->priKey);

// step5: 拼接post数据
        $post = array(
            'transCode' => $transCode,
            'accessType' => $accessType,
            'merId' => $merId,
            'encryptKey' => $encryptKey,
            'encryptData' => $encryptData,
            'sign' => $sign
        );

// step6: post请求
        $result = $this->http_post_json($pfaList['exec_gateway'], $post);
        parse_str($result, $arr);

        try {
            // step7: 使用私钥解密AESKey
            $decryptAESKey = $this->RSADecryptByPri($arr['encryptKey'], $this->priKey);

            // step8: 使用解密后的AESKey解密报文
            $decryptPlainText = $this->AESDecrypt($arr['encryptData'], $decryptAESKey);

            // step9: 使用公钥验签报文
            $this->verify($decryptPlainText, $arr['sign'], $this->pubKey);
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
        $returnData = json_decode(convertToUTF8($decryptPlainText), true);
        if($returnData['respCode'] == '0000'){
            if($returnData['resultFlag'] == '2'){
                $result = ['status'=>1, 'msg' => '申请成功'];
            }else if($returnData['resultFlag'] == '0'){
                $result = ['status'=>2, 'msg' => '支付成功'];
            }else if($returnData['resultFlag'] == '1'){
                $result = ['status'=>3, 'msg' => '申请失败'];
            }
        }else{
            $result = ['status' => 3, 'msg'=>convertToUTF8($returnData['respDesc'])];
        }
        return $result;
    }


    public function PaymentQuery($wttlList, $pfaList){

        $this->initKey($pfaList);
	    $info = array(
                'transCode' => 'ODQU', // 订单查询
                'merId' => $pfaList['mch_id'], // 此处更换商户号
                'pt' => array(
                    'orderCode' => $wttlList['orderid'],
                    'version' => '01',
                    'productId' => '00000003',
                    'tranTime' => date('YmdHis')
                )
            );
        // step1: 拼接报文及配置
        $transCode = $info['transCode']; // 交易码
        $accessType = '0'; // 接入类型 0-商户接入，默认；1-平台接入
        $merId = $info['merId']; // 此处更换商户号
        $pt = $info['pt']; // 报文

// step2: 生成AESKey并使用公钥加密
        $AESKey = $this->aes_generate(16);
        $encryptKey = $this->RSAEncryptByPub($AESKey, $this->pubKey);

// step3: 使用AESKey加密报文
        $encryptData = $this->AESEncrypt($pt, $AESKey);

// step4: 使用私钥签名报文
        $sign = $this->sign($pt, $this->priKey);

// step5: 拼接post数据
        $post = array(
            'transCode' => $transCode,
            'accessType' => $accessType,
            'merId' => $merId,
            'encryptKey' => $encryptKey,
            'encryptData' => $encryptData,
            'sign' => $sign
        );

// step6: post请求
        $result = $this->http_post_json($pfaList['query_gateway'], $post);
        parse_str($result, $arr);

        try {
            // step7: 使用私钥解密AESKey
            $decryptAESKey = $this->RSADecryptByPri($arr['encryptKey'], $this->priKey);

            // step8: 使用解密后的AESKey解密报文
            $decryptPlainText = $this->AESDecrypt($arr['encryptData'], $decryptAESKey);

            // step9: 使用公钥验签报文
            $this->verify($decryptPlainText, $arr['sign'], $this->pubKey);
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
        $returnData = json_decode($decryptPlainText, true);
        if($returnData['respCode'] == '0000'){
            if($returnData['resultFlag'] == '2'){
                $result = ['status'=>1, 'msg' => '申请成功'];
            }else if($returnData['resultFlag'] == '0'){
                $result = ['status'=>2, 'msg' => '支付成功'];
            }else if($returnData['resultFlag'] == '1'){
                $result = ['status'=>3, 'msg' => '申请失败'];
            }
        }else{
            $result = ['status' => 3, 'msg'=>convertToUTF8($returnData['respDesc'])];
        }
        return $result;

    }

    /**
     * 获取公钥
     * @param $path
     * @return mixed
     * @throws \Exception
     */
    function loadX509Cert($path)
    {
        try {
            $file = file_get_contents($path);
            if (!$file) {
                throw new \Exception('loadx509Cert::file_get_contents ERROR');
            }

            $cert = chunk_split(base64_encode($file), 64, "\n");
            $cert = "-----BEGIN CERTIFICATE-----\n" . $cert . "-----END CERTIFICATE-----\n";

            $res = openssl_pkey_get_public($cert);
            $detail = openssl_pkey_get_details($res);
            openssl_free_key($res);

            if (!$detail) {
                throw new \Exception('loadX509Cert::openssl_pkey_get_details ERROR');
            }

            return $detail['key'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 获取私钥
     * @param $path
     * @param $pwd
     * @return mixed
     * @throws \Exception
     */
    function loadPk12Cert($path, $pwd)
    {
        try {
            $file = file_get_contents($path);
            if (!$file) {
                throw new \Exception('loadPk12Cert::file
					_get_contents');
            }

            if (!openssl_pkcs12_read($file, $cert, $pwd)) {
                throw new \Exception('loadPk12Cert::openssl_pkcs12_read ERROR');
            }
            return $cert['pkey'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 私钥签名
     * @param $plainText
     * @param $path
     * @return string
     * @throws \Exception
     */
    function sign($plainText, $path)
    {
        $plainText = json_encode($plainText);
        try {
            $resource = openssl_pkey_get_private($path);
            $result = openssl_sign($plainText, $sign, $resource);
            openssl_free_key($resource);

            if (!$result) {
                throw new \Exception('签名出错' . $plainText);
            }

            return base64_encode($sign);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 公钥验签
     * @param $plainText
     * @param $sign
     * @param $path
     * @return int
     * @throws \Exception
     */
    function verify($plainText, $sign, $path)
    {
        $resource = openssl_pkey_get_public($path);
        $result = openssl_verify($plainText, base64_decode($sign), $resource);
        openssl_free_key($resource);

        if (!$result) {
            throw new \Exception('签名验证未通过,plainText:' . $plainText . '。sign:' . $sign, '02002');
        }

        return $result;
    }

    /**
     * 公钥加密AESKey
     * @param $plainText
     * @param $puk
     * @return string
     * @throws \Exception
     */
    function RSAEncryptByPub($plainText, $puk)
    {
        if (!openssl_public_encrypt($plainText, $cipherText, $puk, OPENSSL_PKCS1_PADDING)) {
            throw new \Exception('AESKey 加密错误');
        }

        return base64_encode($cipherText);
    }

    /**
     * 私钥解密AESKey
     * @param $cipherText
     * @param $prk
     * @return string
     * @throws \Exception
     */
    function RSADecryptByPri($cipherText, $prk)
    {
        if (!openssl_private_decrypt(base64_decode($cipherText), $plainText, $prk, OPENSSL_PKCS1_PADDING)) {
            throw new \Exception('AESKey 解密错误');
        }

        return (string)$plainText;
    }

    /**
     * AES加密
     * @param $plainText
     * @param $key
     * @return string
     * @throws \Exception
     */
    function AESEncrypt($plainText, $key)
    {
        $plainText = json_encode($plainText);
        $result = openssl_encrypt($plainText, 'AES-128-ECB', $key, 1);

        if (!$result) {
            throw new \Exception('报文加密错误');
        }

        return base64_encode($result);
    }

    /**
     * AES解密
     * @param $cipherText
     * @param $key
     * @return string
     * @throws \Exception
     */
    function AESDecrypt($cipherText, $key)
    {
        $result = openssl_decrypt(base64_decode($cipherText), 'AES-128-ECB', $key, 1);

        if (!$result) {
            throw new \Exception('报文解密错误', 2003);
        }

        return $result;
    }

    /**
     * 生成AESKey
     * @param $size
     * @return string
     */
    function aes_generate($size)
    {
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $arr = array();
        for ($i = 0; $i < $size; $i++) {
            $arr[] = $str[mt_rand(0, 61)];
        }

        return implode('', $arr);
    }

    /**
     * 发送请求
     * @param $url
     * @param $param
     * @return bool|mixed
     * @throws \Exception
     */
    function http_post_json($url, $param)
    {
        if (empty($url) || empty($param)) {
            return false;
        }
        $param = http_build_query($param);
        try {

            $ch = curl_init();//初始化curl
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //正式环境时解开注释
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $data = curl_exec($ch);//运行curl
            curl_close($ch);

            if (!$data) {
                throw new \Exception('请求出错');
            }

            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function initKey($pfaList)
    {
        $this->privateKey_ = $pfaList['private_key'];
        $this->private_pwd = $pfaList['signkey'];
        // 获取公私钥匙
        $this->priKey = $this->loadPk12Cert($this->privateKey_, $this->private_pwd);
//$priKey_2 = loadPk12Cert(PRI_KEY_PATH_2, CERT_PWD);
        $this->pubKey = $this->loadX509Cert($this->publicKey_);
    }


}