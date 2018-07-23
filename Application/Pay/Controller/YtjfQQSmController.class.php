<?php
namespace Pay\Controller;

class YtjfQQSmController extends PayController
{

    public function Pay($array)
    {

        $parameter = array(
            'code'         => 'YtjfQQSm', // 通道名称
            'title'        => '易通金服支付QQ扫码', //通道名称
            'exchange'     => 100, // 金额比例
            'gateway'      => '',
            'orderid'      => '',
            'out_trade_id' => I('request.pay_orderid', ''), //外部订单号
            'channel'      => $array,
            'body'         => I('request.pay_productname', ''),
        );
        $return = $this->orderadd($parameter);
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"]     = $this->_site . 'Pay_YtjfQQSm_notifyurl.html';
        $return['callbackurl'] || $return['callbackurl'] = $this->_site . 'Pay_YtjfQQSm_callbackurl.html';

        $arraystr = [
            'version'      => '1.0.0',
            'transCode'    => '8888',
            'merchantId'   => $return['mch_id'],
            'merOrderNum'  => $return['orderid'],
            'bussId'       => $return['appid'],
            'tranAmt'      => $return['amount'],
            'sysTraceNum'  => $return['orderid'],
            'tranDateTime' => date('YmdHis'),
            'currencyType' => '156',
            'merURL'       => $return['callbackurl'],
            'backURL'      => $return['notifyurl'],
            'orderInfo'    => '普通支付',
            'bankId'       => '888880600002900',
            'entryType'    => '1',
            'payPage'      => 'false',

        ];

        $txnString = $arraystr['version'] . '|' .
            $arraystr['transCode'] . '|' .
            $arraystr['merchantId'] . '|' .
            $arraystr['merOrderNum'] . '|' .
            $arraystr['bussId'] . '|' .
            $arraystr['tranAmt'] . '|' .
            $arraystr['sysTraceNum'] . '|' .
            $arraystr['tranDateTime'] . '|' .
            $arraystr['currencyType'] . '|' .
            $arraystr['merURL'] . '|' .
            $arraystr['backURL'] . '|' .
            $arraystr['orderInfo'] . '|' .
            $arraystr['userId'];

        $arraystr['signValue'] = md5($txnString . $return['signkey']);
		
		$f = fopen('./api_data.txt', 'a+');
		fwrite($f, serialize($arraystr));
		fclose($f);
        $res = curlPost($return['gateway'], http_build_query($arraystr));
        parse_str($res, $res);

        if ($res['respCode'] == '0000') {
            // header('location:' . $res['data']['tokenCodeURL']);

            import("Vendor.phpqrcode.phpqrcode", '', ".php");
            $url = $res['codeUrl'];
            $QR  = "Uploads/codepay/" . $return['orderid'] . ".png"; //已经生成的原始二维码图
            \QRcode::png($url, $QR, "L", 20);
            $this->assign("imgurl", '/' . $QR);
            $this->assign('params', $return);
            $this->assign('orderid', $return['orderid']);
            $this->assign('money', bcdiv($return['amount'], 100, 2));
            $this->display("WeiXin/qq");
        } else {
            $this->showmessage($res['respMsg']);
        }
    }

    // 页面通知返回
    public function callbackurl()
    {
        $Order      = M("Order");
        $orderid    = isset($_REQUEST["txnOrderId"]) ? $_REQUEST["txnOrderId"] : $_REQUEST["orderid"];
        $pay_status = $Order->where("pay_orderid = '" . $orderid . "'")->getField("pay_status");
        if ($pay_status != 0) {
            $this->EditMoney($orderid, '', 1);
        } else {
            exit("error");
        }
    }

    // 服务器点对点返回
    public function notifyurl()
    {
        $data = $_REQUEST;
       
        if ($data['respCode'] == '0000') {

            $key  = getKey($data['merOrderNum']);
            $sign = $data['transCode'] . '|' .
                $data['merchantId'] . '|' .
                $data['respCode'] . '|' .
                $data['sysTraceNum'] . '|' .
                $data['merOrderNum'] . '|' .
                $data['orderId'] . '|' .
                $data['bussId'] . '|' .
                $data['tranAmt'] . '|' .
                $data['orderAmt'] . '|' .
                $data['bankFeeAmt'] . '|' .
                $data['integralAmt'] . '|' .
                $data['vaAmt'] . '|' .
                $data['bankAmt'] . '|' .
                $data['bankId'] . '|' .
                $data['integralSeq'] . '|' .
                $data['vaSeq'] . '|' .
                $data['bankSeq'] . '|' .
                $data['tranDateTime'] . '|' .
                $data['payMentTime'] . '|' .
                $data['settleDate'] . '|' .
                $data['currencyType'] . '|' .
                $data['orderInfo'] . '|' .
                $data['userId'];
            $sign = md5($sign . $key);
            if ($data['signValue'] === $sign) {
                $this->EditMoney($data['merOrderNum'], '', 0);
				echo 'success';
            }
        }
    }

}
