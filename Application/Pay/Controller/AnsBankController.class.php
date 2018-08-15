<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-09-04
 * Time: 0:25
 */
namespace Pay\Controller;

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

class AnsBankController extends PayController
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

    /**
     *  发起支付
     */
    public function Pay($array)
    {
        $orderid  = I("request.pay_orderid");
        $body     = I('request.pay_productname');
        $bankid   = I('request.pay_bankid', '');
        $return   = $this->getParameter('爱农商网银支付', $array, __CLASS__, 100);
        $formData = [

            'version'    => '1.0.0',
            'txnType'    => '01',
            'txnSubType' => '00',
            'bizType'    => '000000',
            'accessType' => '0',
            'accessMode' => '01',
            'merId'      => $return['mch_id'],
            'merOrderId' => $return['orderid'],
            'txnTime'    => date('YmdHis'),
            'txnAmt'     => $return['amount'],
            'currency'   => 'CNY',
            'frontUrl'   => $return['callbackurl'],
            'backUrl'    => $return['notifyurl'],
            'payType'    => '0201',
            'channelId'  => '',
            'subject'    => '',
            'body'       => '',
            'merResv1'   => '',
            'bankId'     => array_key_exists($bankid, $this->b2cBank_) ? $this->b2cBank_[$bankid] : '',
        ];
        // var_dump($this->md5Sign($formData, $return['signkey'],'',0));exit;
        $formData['signature']  = base64_encode($this->md5Sign($formData, $return['signkey']));
        $formData['signMethod'] = 'MD5';

        echo createForm(
            'http://pay.gueiyoug.cn/Pay_AnsBank_Rpay',
            [
                'data'    => encryptDecrypt(serialize($formData), 'lgbya!'),
                'gateway' => $return['gateway'],
            ]
        );
    }

    public function md5Sign($data, $key, $connect = '', $is_md5 = true)
    {
        ksort($data);
        $string = '';
        foreach ($data as $k => $vo) {
            $string .= $k . '=' . $vo . '&';
        }
        $string = rtrim($string, '&');
        $result = $string . $connect . $key;

        return $is_md5 ? md5($result, true) : $result;

    }

    public function Rpay()
    {
        //接收传输的数据
        $postData = I('post.', '');
        //将数据解密并反序列化
        $formData = unserialize(encryptDecrypt($postData['data'], 'lgbya!', 1));
        $gateway  = $postData['gateway'];
        // var_dump($_POST);exit;
        echo createForm($gateway, $formData);
    }

    public function callbackurl()
    {
      
        $Order      = M("Order");
        $pay_status = $Order->where("pay_orderid = '" . $_REQUEST["merOrderId"] . "'")->getField("pay_status");
        if ($pay_status != 0) {
            $this->EditMoney($_REQUEST["merOrderId"], '', 1);
        } else {
            exit("error");
        }
    }

    /**
     *  服务器通知
     */
    public function notifyurl()
    {

        if($_POST['respCode'] != '1001'){
            return false;
        }
        //对部分参数值进行base64_decode解码
        $base64Field = ['subject', 'body', 'respMsg','signature'];
        foreach ($base64Field as $v) {
            isset($_POST[$v]) && ($_POST[$v] = base64_decode($_POST[$v]));
        }

        $signature = iconv("UTF-8", "GBK//IGNORE",$_POST['signature']);
        unset($_POST['signature']);
        unset($_POST['signMethod']);

        $key = getKey($_POST['merOrderId']);
        $signValue = iconv("UTF-8", "GBK//IGNORE",$this->md5Sign($_POST, $key));

        if ($signValue == $signature) {
            $this->EditMoney($_POST['merOrderId'], '', 0);
            exit("success");
        }
        exit;

    }

}
