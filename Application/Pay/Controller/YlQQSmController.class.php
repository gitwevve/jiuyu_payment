<?php
namespace Pay\Controller;

class YlQQSmController extends PayController
{

    public function Pay($array)
    {

        $orderid = I("request.pay_orderid", '');

        $body = I('request.pay_productname', '');

        $parameter = [
            'code'         => 'YlQQSm',
            'title'        => '亿联（QQ扫码）',
            'exchange'     => 1, // 金额比例
            'gateway'      => '',
            'orderid'      => '',
            'out_trade_id' => $orderid, //外部订单号
            'channel'      => $array,
            'body'         => $body,
        ];

        //支付金额
        $pay_amount = I("request.pay_amount", 0);

        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);

        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);

        //跳转页面，优先取数据库中的跳转页面
        $return["notifyurl"] || $return["notifyurl"] = $this->_site . 'Pay_YlQQSm_notifyurl.html';

        $return['callbackurl'] || $return['callbackurl'] = $this->_site . 'Pay_YlQQSm_callbackurl.html';

        $arraystr = [
            'mid'       => $return['mch_id'],
            'orderNo'   => $return['orderid'],
            'subject'   => '普通商品',
            'body'      => '普通商品',
            'amount'    => $return['amount'],
            'type'      => 'QQwallet',
            'notifyUrl' => $return['notifyurl'],
            'noise'     => nonceStr(),
        ];

        $arraystr['sign'] = strtoupper(md5Sign($arraystr, $return['signkey'], '&'));

        $result = curlPost($return['gateway'], http_build_query($arraystr));
        $result = json_decode($result, true);
        if ($result['code'] == 'SUCCESS' && $result['qrCode']) {
            import("Vendor.phpqrcode.phpqrcode", '', ".php");
            $url = $result['qrCode'];
            $QR  = "Uploads/codepay/" . $return["orderid"] . ".png"; //已经生成的原始二维码图
            \QRcode::png($url, $QR, "L", 20);
            $this->assign("imgurl", $this->_site . $QR);
            $this->assign('params', $return);
            $this->assign('orderid', $return['orderid']);
            $this->assign('money', $return['amount']);
            $this->display("WeiXin/qq");
        } else {
            $this->showmessage($result['errCodeDes']);
        }

    }

    public function callbackurl()
    {
        $orderid    = I('request.orderid', '');
        $pay_status = M("Order")->where(['pay_orderid' => $orderid])->getField("pay_status");
        if ($pay_status != 0) {
            $this->EditMoney($orderid, '', 1);
        } else {
            exit("error");
        }
    }

    public function notifyurl()
    {
        $data = $_REQUEST;
        if ($data['status'] == '1') {
            $sign = $data['sign'];
            unset($data['sign']);
            $key     = getKey($data['orderNo']);
            $md5Sign = strtoupper(md5Sign($data, $key, '&'));
            if ($md5Sign == $sign) {
                $this->EditMoney($data['orderNo'], '', 0);
                echo '{"code":"SUCCESS","msg":"ok"}';
            }
        }
    }

}
