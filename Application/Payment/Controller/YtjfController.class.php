<?php
namespace Payment\Controller;

use Org\Util\Array2XML;

class YtjfController extends PaymentController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function PaymentExec($data, $config)
    {

        //todo 0、数据初始化
        $MerNo         = $config['mch_id']; //业务申请
        $PayTm         = date('YmdHis'); //时间-dyn
        $BatchName     = '代付';
        $BatchNo       = $this->createOrder($data['id']); //
        $BussNo        = $config['appid']; //测试业务类型，每个商户不同,生产环境由业务分配
        $ProcedureType = '1'; //1:付款方付费 2：收款方付费
        $totCnt        = 1;
        $totAmt        = $data['money'] * 100;

        $details[] = [
            'merinsid'  => $BatchNo . mt_rand(10, 99), //每个批次内，流水唯一即可
            'pay-type'  => '1',
            //todo 1、加密
            'bank-no'   => $this->rsaEncrypt($data['banknumber'], $config['public_key'], 'utf-8'),
            'real-name' => $this->rsaEncrypt($data['bankfullname'], $config['public_key'], 'utf-8'),
            'bank-name' => $data['bankzhiname'],
            'pay-fee'   => $totAmt, //代付金额
        ];

        $SignData = $MerNo . '|' . $PayTm . '|' . $BatchName . '|' . $BatchNo . '|' . $BussNo . '|' . $ProcedureType . '|' . $totCnt . '|' . $totAmt;
        foreach ($details as $detail) {
            foreach ($detail as $value) {
                $SignData .= '|' . $value;
            }
        }
        $sign = $this->p1enc($SignData, $config['private_key'], $config['signkey']);

        $request = array(
            '@attributes' => array(
                'version'  => '100',
                'security' => 'true',
                'lang'     => 'chs',
            ),
            'trans'       => array(
                'trn-y2e0010-req' => array(
                    'trn-info'    => array(
                        'mer-no'         => $MerNo,
                        'pay-tm'         => $PayTm,
                        'batch-name'     => $BatchName,
                        'batch-no'       => $BatchNo,
                        'buss-no'        => $BussNo,
                        'procedure-type' => $ProcedureType,
                        'tot-cnt'        => $totCnt,
                        'tot-amt'        => $totAmt,
                    ),
                    'sign'        => $sign,
                    'trn-details' => array(
                        'trn-detail' => $details,
                    ),
                ),
            ),
        );
        $version  = '1.0';
        $encoding = 'utf-8';
        Array2XML::init($version, $encoding, false);
        $xml       = Array2XML::createXML('yt2e', $request);
        $RequestD  = $xml->saveXML();
        $ResponseD = $this->postXmlCurl($RequestD, $config['exec_gateway']);
        $res       = xmlToArray($ResponseD);

        if ($res) {
            $status = ['B001', 'B022', 'B023', 'B024', 'B025'];
            $bool   = array_search($res['trans']['trn-y2e0010-res']['status']['rspcod'], $status);
            if ($bool !== false) {
                $return = ['status' => 1, 'msg' => '受理中！'];
            } else {
                $return = ['status' => 3, 'msg' => $res['trans']['trn-y2e0010-res']['status']['rspmsg']];
            }
        } else {
            $return = ['status' => 3, 'msg' => '网络延迟，请稍后再试！'];
        }
        return $return;
    }

    public function PaymentQuery($data, $config)
    {

        //todo 0、数据初始化
        $MerNo    = $config['mch_id']; //业务申请
        $BatchNo  = $this->createOrder($data['id']); //
        $SignData = $MerNo . '|' . $BatchNo;
        $sign     = $this->p1enc($SignData, $config['private_key'], $config['signkey']);
        $request  = [
            '@attributes' => [
                'version'  => '100',
                'security' => 'true',
                'lang'     => 'chs',
            ],
            'trans'       => [
                'trn-y2e0011-req' => [
                    'mer-no'   => $MerNo,
                    'batch-no' => $BatchNo,
                    'sign'     => $sign,
                ],
            ],
        ];
        $version  = '1.0';
        $encoding = 'utf-8';
        Array2XML::init($version, $encoding, false);
        $xml      = Array2XML::createXML('yt2e', $request);
        $RequestD = $xml->saveXML();

        $ResponseD = $this->postXmlCurl($RequestD, $config['query_gateway']);
        $res       = xmlToArray($ResponseD);

        if ($res) {
            $status = ['B001', 'B022'];
            $bool   = array_search($res['trans']['trn-y2e0011-res']['status']['rspcod'], $status);
            if ($bool !== false) {
                $orderStatus = $res['trans']['trn-y2e0011-res']["trn-details"]["pay-detail"]["status"];
                if ($orderStatus == '96' || $orderStatus == '03') {
                    $return = ['status' => 3, 'msg' => '代付失败！'];
                } else if ($orderStatus == '02') {
                    $return = ['status' => 2, 'msg' => '成功'];
                }
            } else {
                $return = ['status' => 3, 'msg' => '受理中！'];
            }
        } else {
            $return = ['status' => 3, 'msg' => '网络延迟，请稍后再试！'];
        }
        return $return;
    }

    public function createOrder($id)
    {
        $bu = 8 - strlen($data['id']);
        return str_pad($id, $bu, '6', STR_PAD_LEFT);
    }

    public function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        // //如果有配置代理这里就设置代理
        // if (DaiFuConfig::CURL_PROXY_HOST != "0.0.0.0"
        //     && DaiFuConfig::CURL_PROXY_PORT != 0) {
        //     curl_setopt($ch, CURLOPT_PROXY, DaiFuConfig::CURL_PROXY_HOST);
        //     curl_setopt($ch, CURLOPT_PROXYPORT, DaiFuConfig::CURL_PROXY_PORT);
        // }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); //严格校验
        //设置header
        //curl_setopt($ch, CURLOPT_HEADER, FALSE);
        $headers = array('content-type: application/xml;charset=utf-8');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($useCert == true) {
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, DaiFuConfig::SSLCERT_PATH);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, DaiFuConfig::SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            // throw new DaiFuException("curl出错，错误码:$error");
        }
    }

    public function splitCN($cont, $n = 0, $subnum, $charset)
    {
        //$len = strlen($cont) / 3;
        $arrr = array();
        for ($i = $n; $i < strlen($cont); $i += $subnum) {
            $res = $this->subCNchar($cont, $i, $subnum, $charset);
            if (!empty($res)) {
                $arrr[] = $res;
            }
        }
        return $arrr;
    }

    public function subCNchar($str, $start = 0, $length, $charset = "gbk")
    {
        if (strlen($str) <= $length) {
            return $str;
        }
        $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
        return $slice;
    }

    public function rsaEncrypt($data, $rsaPublicKeyPem, $charset)
    {

        //转换为openssl格式密钥
        $res    = openssl_get_publickey($rsaPublicKeyPem);
        $blocks = $this->splitCN($data, 0, 30, $charset);

        $chrtext  = null;
        $encodes  = array();
        foreach ($blocks as $n => $block) {
            if (!openssl_public_encrypt($block, $chrtext , $res)) {
                echo "<br/>" . openssl_error_string() . "<br/>";
            }
            $encodes[] = $chrtext ;
        }
        $chrtext = implode(",", $encodes);
        return strtoupper(bin2hex($chrtext));
    }

    public function rsaDecrypt($data, $rsaPrivateKeyPem, $charset)
    {

        //转换为openssl格式密钥
        $res     = openssl_get_privatekey($rsaPrivateKeyPem);
        $decodes = explode(',', $data);
        $strnull = "";
        $dcyCont = "";
        foreach ($decodes as $n => $decode) {
            if (!openssl_private_decrypt($decode, $dcyCont, $res)) {
                echo "<br/>" . openssl_error_string() . "<br/>";
            }
            $strnull .= $dcyCont;
        }
        return $strnull;
    }

    protected function p1enc($SignData, $path, $key)
    {

        if (!$pkcs12 = file_get_contents($path)) {
            echo "Error: Unable to read the cert file\n";
            exit;
        }
        if (openssl_pkcs12_read($pkcs12, $cert_info, $key)) {
            $privateKey = $cert_info['pkey'];
            $cert       = $cert_info['cert'];
        } else {
            echo "Error: Unable to read the cert store.\n";
            exit;
        }
        if (!openssl_sign($SignData, $signature, $privateKey, OPENSSL_ALGO_SHA1)) {
            echo "Error: Unable to signdata.\n";
            exit;
        }

        $base64Signature = base64_encode($signature);
        return $base64Signature;
    }
}
