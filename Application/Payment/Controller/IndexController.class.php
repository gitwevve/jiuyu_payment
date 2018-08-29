<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-22
 * Time: 14:34
 */
namespace Payment\Controller;
use Think\Exception;
use Think\Log;

/**
 * 用户中心首页控制器
 * Class IndexController
 * @package User\Controller
 */
class IndexController extends PaymentController{



    public function __construct(){
        parent::__construct();
        $_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
    }



    public function index(){

        //验证传来的数据
        $post_data = verifyData($this->verify_data_);
        //判断是否登录
//        if ($post_data['auto_df'] == 1 || session('auto_submit_df')) {
//            isLogin();
//        }
        //获取要操作的订单id
        $post_data['id'] = explode(',', rtrim($post_data['id'], ',') );
		
        //根据操作查询不同状态的订单
        if ($post_data['opt'] == 'exec') {
            $status = 0;
        } else {
            $status = ['in', '1, 4'];
        }
        $where = ['id'=>['in', $post_data['id']], 'status'=>$status];

        $wttk_lists = $this->selectOrder($where);
		
		$post_data['code'] = $post_data['opt'] == 'exec'?$post_data['code']:$wttk_lists[0]['df_id'];
		//获取要代付的通道信息
        $pfa_list = $this->findPaymentType($post_data['code']);
		
        //检查代付金额与用户金额是否相同
        //$this->checkMoney($wttk_lists['userid'] , $wttk_lists['money']);
		
        //判断代付通道的文件是否存在
        $code = $pfa_list['code'];
        $code || showError('代付渠道不存在！');
        $file = APP_PATH . 'Payment/Controller/' . $code . 'Controller.class.php';
        is_file($file) || showError('代付渠道不存在！');
        //循环存在代付通道的文件限制一次只能操作15条数据
        $opt = ucfirst( $post_data['opt']);
        if($opt == 'Exec' && !session('admin_submit_df')) {
            if (!$post_data['auto_df'] && !session('auto_submit_df')) {
                showError('未通过身份验证！');
            }
        }
        $single_result = null;
        $success = 0;
        if( count($wttk_lists)<= 15){
            $fp = fopen($file, "r");
            foreach($wttk_lists as $k => $v){
                //开启文件锁防止多人操作重复提交
                if( flock($fp,LOCK_EX) ) {
                    if($opt == 'Exec') {
                        //加锁防止重复提交
                        $res = M('Wttklist')->where(['id'=>$v['id'], 'df_lock'=>0])->setField('df_lock',1);
                        if(!$res) {
                            continue;
                        }
                    }
                    $v['money'] = round($v['money'],2);
                    $result = R('Payment/'.$code.'/Payment' . $opt, [$v, $pfa_list]);
                    if($result === FALSE) {
                        if($opt == 'Exec') {
                            M('Wttklist')->where(['id' => $v['id']])->setField('df_lock', 0);
                        }
                        showError('服务器请求失败1！');
                    }
                    $single_result = $result;
                    if(is_array($result)){
                        $cost = $pfa_list['rate_type'] ? bcmul($v['tkmoney'], $pfa_list['cost_rate'], 2):$pfa_list['cost_rate'];
                        $data = [
                            'memo'       => $result['msg'],
                            'df_id'     => $pfa_list['id'],
                            'code'      => $pfa_list['code'],
                            'df_name'   => $pfa_list['title'],
                            'channel_mch_id' =>$pfa_list['mch_id'],
                            'cost_rate' => $pfa_list['cost_rate'],
                            'cost'      => $cost,
                            'rate_type'=>$pfa_list['rate_type'],
                        ];
                        if ($result['status'] == 1 || $result['status'] == 2) {
                            $success ++;
                        }
                        $this->handle($v['id'], $result['status'], $data);
                    }
                }
                if($opt == 'Exec') {
                    M('Wttklist')->where(['id' => $v['id']])->setField('df_lock', 0);
                }
                flock($fp,LOCK_UN);
            }
            fclose($fp);
            if ($single_result !== null) {
                if (count($wttk_lists) == 1) {
                    if($opt == 'Query') {
                        if ($single_result['status'] == 2) {
                            showSuccess($single_result['msg']);
                        } else {
                            showError($single_result['msg']);
                        }
                    } else {
                        if ($single_result['status'] == 3) {
                            showError('代付申请失败:' . $single_result['msg']);
                        } else {
                            showSuccess($single_result['msg']);
                        }
                    }

                } else {
                    if($success == 0) {
                        showError('代付失败！请在页面刷新后查看订单状态！');
                    } else {
                        showSuccess('代付成功,请在页面刷新后查看订单状态！');
                    }
                }
            }
            exit;
        }
        if($opt == 'Exec') {
            session('admin_submit_df', null);
            session('auto_submit_df', null);
        }
        showError('只能同时请求15条代付数据！');
    }

    //定时任务-查询上游代付订单
    public function evenQuery(){
        $where = ['status'=>['in', [1, 4]]];
        $wttk_lists = $this->selectOrder($where);
        foreach($wttk_lists as $k => $v){
            $file = APP_PATH . 'Payment/Controller/' . $v['code'] . 'Controller.class.php';
            if( is_file($file) ){
                $pfa_list = $this->findPaymentType($v['df_id']);
                $result = R('Payment/'.$v['code'].'/PaymentQuery', [$v, $pfa_list]);
                $result!==FALSE || showError('服务器请求失败！');
                if(is_array($result)){
                    $data = [
                        'msg'       => $result['msg'],
                        'df_id'     => $pfa_list['id'],
                        'code'      => $pfa_list['code'],
                        'df_name'   => $pfa_list['title'],
                    ];
                    $this->handle($v['id'], $result['status'], $data);
                }
            }
            sleep(3);
        }
    }

    //批量查询代付订单状态
    public function batchQuery(){
        //判断是否登录
        isLogin();
        $id = I('post.id', '');
        //获取要查询的订单id
        $id = explode(',', rtrim($id, ',') );
        if(empty($id)) {
            showError('请选择订单！');
        }
        $where['id'] = ['in', $id];
        $where['status'] = ['in', [1]];
        $wttk_lists = M('Wttklist')->where($where)->select();
        if(empty($wttk_lists)) {
            showError('所选订单不能查询！');
        }
        $success = 0;
        foreach($wttk_lists as $k => $v){
            $file = APP_PATH . 'Payment/Controller/' . $v['code'] . 'Controller.class.php';
            if( file_exists($file) ){
                $pfa_list = M('PayForAnother')->where(['id'=>$v['df_id']])->find();
                if(empty($pfa_list)) {
                    continue;
                }
                $result = R('Payment/'.$v['code'].'/PaymentQuery', [$v, $pfa_list]);
                if(FALSE === $result) {
                    continue;
                } else {
                    if(is_array($result)){
                        $success++;
                        $data = [
                            'msg'       => $result['msg'],
                            'df_id'     => $pfa_list['id'],
                            'code'      => $pfa_list['code'],
                            'df_name'   => $pfa_list['title'],
                        ];
                        $this->handle($v['id'], $result['status'], $data);
                    }
                }
            } else {
                continue;
            }
        }
        if($success == 0) {
            showError('查询失败！');
        } else {
            showSuccess('查询成功,请在页面刷新后查看订单状态！');
        }
    }

    public function userAutoDf($order_ids)
    {

        $where = [];
        foreach ($order_ids as $id) {
            $where[] = ['orderid' => $id];
        }
        $ids =  M('Wttklist')->where($where)->getField('id', ',');
        Log::record($ids);
        session('get_raw_return', 1);
        session('admin_submit_df', 1);
        session('auto_submit_df', 1);
        if ($ids) {
            $_REQUEST = [
                'code'=>'default',
                'id'=> $ids . ',',
                'opt' => 'exec',
            ];
            try {
                $res = R('Payment/Index/index');
            } catch (Exception $exception) {
                Log::record($exception->getMessage());
                $res = json_decode($exception->getMessage(), true);
            }
            return $res;
        }
    }
}
