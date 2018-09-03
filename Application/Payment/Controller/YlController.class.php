<?php
namespace Payment\Controller;

class YlController extends PaymentController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function PaymentExec($data, $config)
    {
        $orderid = $this->createOrderId($data['id']);
        M('Wttklist')->where(['id' => $data['id']])->setField('extends', $orderid);
        $arraystr = [
            'mid'             => $config['mch_id'],
            'orderNo'         => $orderid,
            'amount'          => $data['money'],
            'receiveName'     => $data['bankfullname'],
            'openProvince'    => $data['sheng'],
            'openCity'        => $data['shi'],
            'bankCode'        => $data['additional'][0],
            'bankBranchName'  => $data['bankzhiname'],
            'cardNo'          => $data['banknumber'],
            'type'            => '02',
            'cardAccountType' => '01',
            'noise'           => nonceStr(),
        ];
        $arraystr['sign'] = strtoupper(md5Sign($arraystr, $config['signkey'], '&'));

        $result = curlPost($config['exec_gateway'], http_build_query($arraystr));
        $result = json_decode($result, true);
        $return = ['status' => 3, 'msg' => '网络异常，请稍后重新请求'];
        if ($result['code'] === 'SUCCESS') {
            if ($result['resultCode'] === 'SUCCESS') {
                $return['msg'] = $result['transMessage'];
                switch ($result['transState']) {
                    case '1':
                        $return['status'] = 1;
                        break;
                    case '3':
                        $return['status'] = 2;
                        break;
                    default:
                        # code...
                        break;
                }
            } else {
                $return['msg'] = $result['errCodeDes'];
            }
        }
        return $return;
    }

    public function PaymentQuery($data, $config)
    {
        if ( ! $data['extends']) {
            $orderid = $data['extends'];
        } else {
            $orderid = $data['orderid'];
        }
        $arraystr = [
            'mid'     => $config['mch_id'],
            'orderNo' => $orderid,
            'noise'   => nonceStr(),
        ];
        $arraystr['sign'] = strtoupper(md5Sign($arraystr, $config['signkey'], '&'));
        $result = curlPost($config['query_gateway'], http_build_query($arraystr));
        $result = json_decode($result, true);
        $return = ['status' => 3, 'msg' => '网络异常，请稍后重新请求'];
        if ($result['code'] === 'SUCCESS') {
            if ($result['resultCode'] === 'SUCCESS') {
                $return['msg'] = $result['transMessage'];
                switch ($result['transState']) {
                    case '1':
                        $return['status'] = 1;
                        break;
                    case '3':
                        $return['status'] = 2;
                        break;
                    default:
                        # code...
                        break;
                }
            } else {
                $return['msg'] = $result['errCodeDes'];
            }
        }
        return $return;
    }

    public function createOrderId($id)
    {
        return strlen($id) < 13? str_pad($id, 13, '0', STR_PAD_LEFT) : $id;
    }

}
