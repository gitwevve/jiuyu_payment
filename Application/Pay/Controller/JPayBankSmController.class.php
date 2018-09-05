<?php
/**
 * Created by PhpStorm.
 * User: dong
 * Date: 2018/9/2
 * Time: 23:58
 */
namespace Pay\Controller;
use Org\Util\Jp\util\RsaEncryptor;
use Think\Log;

/**
 * 第三方接口开发示例控制器
 * Class DemoController
 * @package Pay\Controller
 *
 * 三方通道接口开发说明：
 * 1. 管理员登录网站后台，供应商管理添加通道，通道英文代码即接口类名称
 * 2. 用户管理-》通道-》指定该通道（独立或轮询）
 * 3. 用户费率优先通道费率
 * 4. 用户通道指定优先系统默认支持产品通道指定
 * 5. 三方回调地址URL写法，如本接口 ：
 *    异步地址：http://www.yourdomain.com/Pay_Demo_notifyurl.html
 *    跳转地址：http://www.yourdomain.com/Pay_Demo_callbackurl.html
 *
 *    注：下游对接请查看商户API对接文档部分.
 */

class JPayBankSmController extends PayController
{
    protected $common_params = [
        'charset' => '02', // UTF-8
        'version' => '1.0',
        'signType' => 'RSA256',
    ];
    protected $b2cBank_ = [
        'ICBC' => 'ICBC',
        'ABC'  => 'ABC',
        'CMBC' => 'CMBC',
        'CCB'  => 'CCB',
        'CEB'  => 'CEB',
        'PSBC' => 'PSBC',
        'SHB' => 'SHB',
        'BJB' => 'BJB',
        'GBD' => 'GBD',
    ];

    /**
     *  发起支付
     */
    public function Pay($array)
    {

        $return  = $this->getParameter('久派支付(银联扫码)', $array, __CLASS__, 100);

        $config = [
            'merchantId' => $return['mch_id'],
            'requestTime' => date('YmdHis'),
            'requestId' => md5(time().mt_rand(100, 999)),
            'service' => 'qrcodeSpdbPreOrder',
        ];
        $this->mergeCommonParams($config);
        $formData = [
            'payChannel'    => 'UPOP',
            'goodsName'   => '产品充值',
            'corpOrg'    => 'UPOP',
            'clientIP'    => getIP(),
            'terminalId'    => mt_rand(10000000, 99999999),
            'orderId'      => $return['orderid'],
            'amount'  => $return['amount'],
//            'bankUrl' => $return['callbackurl'],
            'offlineNotifyUrl'      => $return['notifyurl'],
        ];

        $data = array_merge($formData, $this->common_params);
        ksort($data);
        $text = $this->normalResponse($data);
        $data['merchantSign'] = RsaEncryptor::RSASign($text, $return['signkey'], $return['appsecret']);
        $data['merchantCert'] = RsaEncryptor::getPubCert($return['signkey'], $return['appsecret']);

        $res = curlPost($return['gateway'], http_build_query($data), array('Content-Type: application/x-www-form-urlencoded'));
        parse_str($res, $output);
        if ($output['rspCode'] == 'IPS00000') {
            $url = urldecode($output['bankUrl']);
            $return['amount'] = bcdiv($return['amount'], 100, 2);
            $this->showQRcode($url, $return, 'banksm');
            return;
        }else {
            echo "失败";
        }
        exit();
//        echo createForm($return['gateway'], $data);

    }

    /**
     * 验签参数归一化
     *
     * @param array $rsp
     * @return string
     */
    public function normalResponse($rsp)
    {
        $ret = [];
        // 生成签名之前要进行编码转换
        while (list($k, $v) = each($rsp)) {
            if ($v === 'null') {
                continue;
            }
            if (!empty(trim($v)) || $v === 0 || $v === '0') {
                $ret[] = "$k=$v";
            }
        }
        return implode('&', $ret);
    }

    public function callbackurl()
    {
        $Order      = M("Order");
        $pay_status = $Order->where("pay_orderid = '" . $_REQUEST["merOrderNum"] . "'")->getField("pay_status");
        if ($pay_status != 0) {
            $this->EditMoney($_REQUEST["merOrderNum"], '', 1);
        } else {
            exit("error");
        }
    }

    /**
     *  服务器通知
     */
    public function notifyurl()
    {
        $postData = I('request.', '');

        if ($postData['orderSts'] == 'PD') {
            $data = $postData;

            $serverSign = $data['serverSign'];
            $serverCert = $data['serverCert'];

            unset($data['serverSign']);
            unset($data['serverCert']);
//            $rootCa = openssl_x509_parse(file_get_contents(SignMaker::$rootCertFile));
            $normalText = $this->normalResponse($data);

            // 返回结果验签
            $checkResult = RsaEncryptor::RSAVerify($normalText, $serverSign, $serverCert);
            if ($checkResult == RsaEncryptor::VERIFY_SUCCESS) {
                $this->EditMoney($postData['orderId'], '', 0);
                exit("result=SUCCESS");
            }
        }
    }

    public function mergeCommonParams($config)
    {
        $this->common_params = array_merge($this->common_params, $config);
        return $this;
    }

}