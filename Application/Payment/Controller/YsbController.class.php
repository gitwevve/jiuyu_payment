<?php
namespace Payment\Controller;

class YsbController extends PaymentController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function PaymentExec($wttlList, $pfaList)
    {
        $arraystr = [
            'accountId' => $pfaList['mch_id'],
            'name'      => $wttlList['bankfullname'],
            'cardNo'    => $wttlList['banknumber'],
            'orderId'   => $wttlList['orderid'],
            'purpose'   => 'pay',
            'amount'    => $wttlList['money'],
        ];

        $mac = 'accountId=' . $arraystr['accountId'] .
            '&name=' . $arraystr['name'] .
            '&cardNo=' . $arraystr['cardNo'] .
            '&orderId=' . $arraystr['orderId'] .
            '&purpose=' . $arraystr['purpose'] .
            '&amount=' . $arraystr['amount'] .
            '&key=' . $pfaList['signkey'];

        $arraystr['mac'] = strtoupper(md5($mac));
        $result          = curlPost($pfaList['exec_gateway'] . '?' . http_build_query($arraystr));

        $result          = json_decode($result, true);

        if ($result) {
            $return['status'] = $result['result_code'] == '0000' ? '1' : '3';
            $return['msg']    = $result['result_msg'];
        } else {
            $return = ['status' => '3', 'msg' => '网络异常，请稍后重试'];
        }
        return $return;
    }

    public function PaymentQuery($wttlList, $pfaList)
    {
        $arraystr = [
            'accountId' => $pfaList['mch_id'],
            'orderId'   => $wttlList['orderid'],
        ];

        $mac = 'accountId=' . $arraystr['accountId'] .
            '&orderId=' . $arraystr['orderId'] .
            '&key=' . $pfaList['signkey'];
        $arraystr['mac'] = strtoupper(md5($mac));
        $result          = curlPost($pfaList['query_gateway'] . '?' . http_build_query($arraystr));
        $result          = json_decode($result, true);

        if ($result) {
            if ($result['result_code'] == '0000') {
                switch ($result['status']) {
                    case '00':
                        $return = ['status' => '2', 'msg' => '代付成功'];
                        break;
                    case '10':
                        $return = ['status' => '1', 'msg' => '上游处理中'];
                        break;
                    case '20':
                        $return = ['status' => '3', 'msg' => $result['desc']];
                        break;
                    default:
                        # code...
                        break;
                }
            } else {
                $return = ['status' => '3', 'msg' => $result['result_msg']];
            }

        } else {
            $return = ['status' => '3', 'msg' => '网络异常，请稍后重试'];
        }
        return $return;
    }
}
