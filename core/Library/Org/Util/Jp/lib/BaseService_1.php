<?php
namespace jpp\lib;

use jpp\util\HttpClient;
use jpp\util\RsaEncryptor;
use jpp\util\SignMaker;

/**
 * 服务基础类
 *
 * @package jpp\lib
 */
class BaseService
{
    const API_REQ_SUCCESS = 0;

    /**
     * @var array 请求九派商户系统公共入参列表
     */
    private static $commonParams = array(
        'charset'    => '02', // GB18030
        'version'    => '1.0',
        'signType'   => 'RSA256',
        'merchantId' => JPP_MERCHANT_ID,
    );

    /**
     * 执行网络API调用
     *
     * @param string $service 调用的服务名称
     * @param array $params 业务参数
     * @param int $timeout 接口请求超时时间（默认：10秒）
     * @return array
     */
    public static function request($service, $params, $timeout = 10)
    {
        if (empty($params)) {
            $params = array();
        }
        $req            = self::$commonParams + $params;
        $req['service'] = $service;
        // 请求时间戳
        if (!isset($req['requestTime']) || empty($req['requestTime'])) {
            $req['requestTime'] = date('YmdHis');
        }
        // 请求唯一ID（当日唯一）
        // if (!isset($req['requestId']) || empty($req['requestId'])) {
        // $req['requestId'] = StringUtil::getUniqueId();
        $req['requestId'] = date("YmdHis") . rand(1000, 9999);
        // }

        $post = SignMaker::wrapperRequest($req);
        // cc-test
        // var_dump($post);

        $result = HttpClient::instance()->curl(array(
            'url'    => JPP_MERCHANT_URL,
            'post'   => $post,
            'option' => array(
                CURLOPT_TIMEOUT_MS => $timeout * 1000,
            ),
        ));

        // 网络异常的情况
        if ($result['errno'] != self::API_REQ_SUCCESS || empty($result['content'])) {
            return null;
        }

        parse_str($result['content'], $ret);

        ksort($ret);

        $sign = $ret['serverSign'];
        unset($ret['serverSign']);
        $cert = $ret['serverCert'];
        unset($ret['serverCert']);

        $normalText = SignMaker::normalResponse($ret);
        // var_dump($normalText);
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
        if ($checkResult == RsaEncryptor::VERIFY_SUCCESS) {
            var_dump('cc-test:BaseService:request' . '----验签成功-----');
            return $ret;
        }

        return ['rspCode' => 'IPS00008', 'rspMessage' => '请求返回内容验签失败'];
    }
}
