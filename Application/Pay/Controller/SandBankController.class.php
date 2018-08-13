<?php
/**
 * Created by PhpStorm.
 * User: dong
 * Date: 2018/8/10
 * Time: 23:15
 */
namespace Pay\Controller;

class SandBankController extends PayController
{
    protected $b2cBank_ = [
        'ICBC' => '01020000',
        'ABC'  => '01030000',
        'CMB' => '03080000',
        'BOC'  => '01040000',
        'CMBC' => '03050000',
        'CCB'  => '01050000',
        'CITIC' => '03020000',
        'COMM' => '03010000',
        'CIB'  => '03090000',
        'CEB'  => '03030000',
        'PSBC' => '01000000',
        'SHB' => '04012900',
        'BJB' => '04031000',
        'HXB' => '03040000',
        'GBD' => '03060000',
        'SPDB' => '03100000',
        'SPAB' => '03070000',
    ];

    private $privateKey_ = './cert/sande/production.pfx';

    private $publicKey_ = './cert/sande/sand.cer';

    private $private_pwd = '123456';

    public function Pay($array)
    {
        $bankid = I("request.pay_bankid", '');
        $return  = $this->getParameter('衫德支付', $array, __CLASS__, 100);
        // step1: 拼接data
        $bankCode = array_key_exists($bankid, $this->b2cBank_) ? $this->b2cBank_[$bankid] :'01020000';
        $data = array(
            'head' => array(
                'version' => '1.0',
                'method' => 'sandpay.trade.pay',
                'productId' => '00000007',
                'accessType' => '1',
                'mid' => $return['mch_id'],
                'channelType' => '07',
                'reqTime' => date('YmdHis', time())
            ),
            'body' => array(
                'orderCode' => $return['orderid'],
                'totalAmount' => str_pad($return['amount'], 12, 0, STR_PAD_LEFT),
                'subject' => $return['subject'],
                'body' => '入金操作',
                'payMode' => 'bank_pc',
                'payExtra' => json_encode(array('payType' => 1, 'bankCode' => $bankCode)),
                'clientIp' => getIP(),
                'notifyUrl' => $return['notifyurl'],
                'frontUrl' => $return['callbackurl'],
            )
        );
        // step2: 私钥签名
        $prikey = $this->loadPk12Cert($this->privateKey_, $this->private_pwd);
        $sign = $this->sign($data, $prikey);

        // step3: 拼接post数据
        $post = array(
            'charset' => 'utf-8',
            'signType' => '01',
            'data' => json_encode($data),
            'sign' => $sign
        );

        // step4: post请求
        $result = $this->http_post_json($return['gateway'], $post);
        $arr = $this->parse_result($result);

        //step5: 公钥验签
        $pubkey = $this->loadX509Cert($this->publicKey_);
        try {
            $this->sandverify($arr['data'], $arr['sign'], $pubkey);
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
        // step6： 获取credential
        $data = json_decode($arr['data'], true);
        if ($data['head']['respCode'] == "000000") {
            $credential = $data['body']['credential'];
            $html = <<<html
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="renderer" content="webkit"/>
    <title>Insert title here</title>
    <script type="text/javascript" src="/Public/Pay/js/sande/paymentjs.js"></script>
    <script type="text/javascript" src="/Public/Pay/js/sande/jquery-1.7.2.min.js"></script>
</head>
<body>
<script>
    function wap_pay() {
        var responseText = $("#credential").text();
        console.log(responseText);
        paymentjs.createPayment(responseText, function (result, err) {
            console.log(result);
            console.log(err.msg);
            console.log(err.extra);
        });
    }
</script>

<div style="display: none">
    <p id="credential">$credential</p>
</div>
</body>

<script>
    window.onload = function () {
        wap_pay();
    };
</script>
</html>
html;
            echo $html;
            exit();
        } else {
            $this->showmessage('支付出错：' .$data['head']['respMsg']);
        }
    }

    public function callbackurl()
    {
        $reqData = I('request.data');
        $data = json_decode($reqData, true);
        $Order      = M("Order");
        $pay_status = $Order->where("pay_orderid = '" . $data["orderCode"] . "'")->getField("pay_status");
        if ($pay_status != 0) {
            $this->EditMoney($data["orderCode"], '', 1);
        } else {
            exit("error");
        }
    }

    public function notifyurl()
    {
        $postData = json_decode(file_get_contents('php://input'), true);
        $pubkey = $this->loadX509Cert($this->publicKey_);
        if ($postData) {
            $sign = $postData['sign']; //签名
            $signType = $postData['signType']; //签名方式
            $data = stripslashes($postData['data']); //支付数据
            $charset = $postData['charset']; //支付编码
            $result = json_decode($data, true); //data数据

            if ($this->sandverify($data, $sign, $pubkey)) {
                //签名验证成功
                $data = $postData['data'];
                $data = json_decode($data, true);
                $this->EditMoney($data['orderCode'], '', 0);
                echo "respCode=000000";
                exit;
            } else {
                //签名验证失败
                file_put_contents("temp/sd_notifyUrl_log.txt", date("Y-m-d H:i:s") . "  " . "异步通知返回报文：" . $data . "\r\n",
                    FILE_APPEND);
                echo 'sign failed';
                exit;
            }
        }
    }

    //--------------------------------------------end基础参数配置------------------------------------------------
    /**
     * 获取公钥
     * @param $path
     * @return mixed
     * @throws \Exception
     */
    protected function loadX509Cert($path)
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
    protected function loadPk12Cert($path, $pwd)
    {
        try {
            $file = file_get_contents($path);
            if (!$file) {
                throw new \Exception('loadPk12Cert::file_get_contents');
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
    protected function sign($plainText, $path)
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
    protected function sandverify($plainText, $sign, $path)
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
     * 发送请求
     * @param $url
     * @param $param
     * @return bool|mixed
     * @throws \Exception
     */
    protected function http_post_json($url, $param)
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

    protected function parse_result($result)
    {
        $arr = array();
        $response = urldecode($result);
        $arrStr = explode('&', $response);
        foreach ($arrStr as $str) {
            $p = strpos($str, "=");
            $key = substr($str, 0, $p);
            $value = substr($str, $p + 1);
            $arr[$key] = $value;
        }

        return $arr;
    }

}