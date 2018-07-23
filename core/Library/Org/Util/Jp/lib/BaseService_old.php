<?php
namespace jpp\lib;

use jpp\util\HttpClient;
use jpp\util\RsaEncryptor;
use jpp\util\SignMaker;
use jpp\util\StringUtil;

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
        $req = self::$commonParams + $params;

        $req['service'] = $service;
        // 请求时间戳
        if (!isset($req['requestTime']) || empty($req['requestTime'])) {
            $req['requestTime'] = date('YmdHis');
        }

        // 请求唯一ID（当日唯一）
        if (!isset($req['requestId']) || empty($req['requestId'])) {
            $req['requestId'] = StringUtil::getUniqueId();
        }
        var_dump($req);
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
        echo "<br>";
        var_dump($result);
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
        ksort($req);

        echo "<br>";
        echo "<br>";
        $normalText = SignMaker::normalResponse($req);
        var_dump($normalText);
        // 返回结果验签
        $checkResult = RsaEncryptor::RSAVerify($normalText, $sign, $cert);
        echo $checkResult . "<br>";
        if ($checkResult == RsaEncryptor::VERIFY_SUCCESS) {
            var_dump('cc-test:BaseService:request' . '----验签成功-----');
            return $ret;
        }
        return $ret;
        return ['rspCode' => 'IPS00008', 'rspMessage' => '请求返回内容验签失败'];
    }
}
