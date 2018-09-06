<?php
namespace Payment\Controller;

use Think\Log;

class YlController extends PaymentController
{
    public $bankmap = [
        'BOB' => '370',
        'BEA' => '400',
        'ICBC' => '102',
        'CEB' => '330',
        'GDB' => '320',
        'HXB' => '105',
        'CCB' => '370',
        'BCM' => '350',
        'CMSB' => '360',
        'NJCB' => '390',
        'NBCB' => '512',
        'ABC' => '103',
        'PAB' => '340',
        'BOS' => '420',
        'SPDB' => '410',
        'CIB' => '309',
        'PSBC' => '403',
        'CMBC' => '310',
        'CZB' => '460',
        'BOC' => '104',
        'CNCB' => '106',
    ];
    public function __construct()
    {
        parent::__construct();
    }

    public function PaymentExec($data, $config)
    {
        if (!array_key_exists($data['bankid'], $this->bankmap)) {
            return ['status' => 4, 'msg' => '此渠道不支持此银行'];
        }
        $arraystr = [
            'mid'             => $config['mch_id'],
            'orderNo'         => $data['orderid'],
            'amount'          => $data['money'],
            'receiveName'     => $data['bankfullname'],
            'openProvince'    => $data['sheng'],
            'openCity'        => $data['shi'],
            'bankCode'        => $this->bankmap[$data['bankid']],
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
        } else {
            $return['msg'] = $result['msg'];
        }
        return $return;
    }

    public function PaymentQuery($data, $config)
    {
        $arraystr = [
            'mid'     => $config['mch_id'],
            'orderNo' => $data['orderid'],
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
