<?php
namespace Admin\Controller;

use Think\Page;

class ChannelController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->assign("Public", MODULE_NAME); // 模块名称
        $this->assign('paytypes', C('PAYTYPES'));

        //通道
        $channels = M('Channel')
            ->where(['status' => 1])
            ->field('id,code,title,paytype,status')
            ->select();
        $this->assign('channels', $channels);
        $this->assign('channellist', json_encode($channels));
    }

    //供应商接口列表
    public function index()
    {
        $count = M('Channel')->count();
        $size  = 15;
        $rows  = I('get.rows', $size);
        if (!$rows) {
            $rows = $size;
        }
        $Page = new Page($count, $rows);
        $data = M('Channel')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->order('id DESC')
            ->select();
        $this->assign('rows', $rows);
        $this->assign('list', $data);
        $this->assign('page', $Page->show());
        $this->display();
    }

    /**
     * 保存编辑供应商
     */
    public function saveEditSupplier()
    {
        if (IS_POST) {
            $id                       = I('post.id', 0, 'intval');
            $papiacc                  = $_POST['pa'];
            $_request['code']         = trim($papiacc['code']);
            $_request['title']        = trim($papiacc['title']);
            $_request['mch_id']       = trim($papiacc['mch_id']);
            $_request['signkey']      = trim($papiacc['signkey']);
            $_request['appid']        = trim($papiacc['appid']);
            $_request['appsecret']    = trim($papiacc['appsecret']);
            $_request['gateway']      = trim($papiacc['gateway']);
            $_request['pagereturn']   = $papiacc['pagereturn'];
            $_request['serverreturn'] = $papiacc['serverreturn'];
            $_request['defaultrate']  = $papiacc['defaultrate'] ? $papiacc['defaultrate'] : 0;
            $_request['fengding']     = $papiacc['fengding'] ? $papiacc['fengding'] : 0;
            $_request['rate']         = $papiacc['rate'] ? $papiacc['rate'] : 0;
            $_request['updatetime']   = time();
            $_request['unlockdomain'] = $papiacc['unlockdomain'];
            $_request['paytype']      = $papiacc['paytype'];
            $_request['status']       = $papiacc['status'];

            if ($id) {
                //更新
                $res = M('Channel')->where(array('id' => $id))->save($_request);
            } else {
                //添加
                $res = M('Channel')->add($_request);
            }
            $this->ajaxReturn(['status' => $res]);
        }
    }

    //开启供应商接口
    public function editStatus()
    {
        if (IS_POST) {
            $pid    = intval(I('post.pid'));
            $isopen = I('post.isopen') ? I('post.isopen') : 0;
            $res    = M('Channel')->where(['id' => $pid])->save(['status' => $isopen]);
            $this->ajaxReturn(['status' => $res]);
        }
    }

    //新增供应商接口
    public function addSupplier()
    {
        $this->display();
    }

    //编辑供应商接口
    public function editSupplier()
    {
        $pid = intval($_GET['pid']);
        if ($pid) {
            $pa = M('Channel')->where(['id' => $pid])->find();
        }
        $this->assign('pa', $pa);
        $this->display('addSupplier');
    }
    //删除供应商接口
    public function delSupplier()
    {
        $pid = intval($_POST['pid']);
        if ($pid) {
            // 删除子账号
            M('channel_account')->where(['channel_id' => $pid])->delete();
            $res = M('Channel')->where(['id' => $pid])->delete();
            $this->ajaxReturn(['status' => $res]);
        }
    }

    //编辑费率
    public function editRate()
    {
        if (IS_POST) {
            $pa = $_POST['pa'];
            if ($_POST['pid']) {
                $res       = M('Channel')->where(['id' => $_POST['pid']])->save($_POST['pa']);
                $pa['pid'] = $_POST['pid'];
                $this->ajaxReturn(['status' => $res, 'data' => $pa]);
            }
        } else {
            $pid = intval(I('get.pid'));
            if ($pid) {
                $data = M('Channel')->where(['id' => $pid])->find();
            }

            $this->assign('pid', $pid);
            $this->assign('pa', $data);
            $this->display();
        }
    }

    //产品列表
    public function product()
    {
        $data = M('Product')->select();
        $this->assign('list', $data);
        $this->display();
    }

    public function bank()
    {
        $data = M('Banks')->select();
        $this->assign('list', $data);
        $this->display();
    }

    //切换产品状态
    public function prodStatus()
    {
        if (IS_POST) {
            $id    = intval($_POST['id']);
            $colum = I('post.k');
            $value = I('post.v');
            $res   = M('Product')->where(['id' => $id])->save([$colum => $value]);
            $this->ajaxReturn(['status' => $res]);
        }
    }

    //切换网银通道状态
    public function prodBankStatus()
    {
        if (IS_POST) {
            $id    = intval($_POST['id']);
            $colum = I('post.k');
            $value = I('post.v');
            $res   = M('Banks')->where(['id' => $id])->save([$colum => $value]);
            $this->ajaxReturn(['status' => $res]);
        }
    }


    //切换用户显示状态
    public function prodDisplay()
    {
        if (IS_POST) {
            $id    = intval($_POST['id']);
            $colum = I('post.k');
            $value = I('post.v');
            $res   = M('Product')->where(['id' => $id])->save([$colum => $value]);
            $this->ajaxReturn(['status' => $res]);
        }
    }

    //切换用网银通道用户显示状态
    public function prodBankDisplay()
    {
        if (IS_POST) {
            $id    = intval($_POST['id']);
            $colum = I('post.k');
            $value = I('post.v');
            $res   = M('Banks')->where(['id' => $id])->save([$colum => $value]);
            $this->ajaxReturn(['status' => $res]);
        }
    }

    //添加产品
    public function addProduct()
    {
        $this->display();
    }

    //添加网银产品
    public function addBank()
    {
        $this->assign('paytypes', [
            ['id'=>'5','name'=>'网银跳转'],
            ['id'=>'6','name'=>'网银直连'],
        ]);
        $this->display();
    }


    //编辑产品
    public function editProduct()
    {
        $id   = I('get.pid', 0, 'intval');
        $data = M('Product')->where(['id' => $id])->find();

        //权重
        $weights    = [];
        $weights    = explode('|', $data['weight']);
        $_tmpWeight = '';
        if (is_array($weights)) {
            foreach ($weights as $value) {
                list($pid, $weight) = explode(':', $value);
                if ($pid) {
                    $_tmpWeight[$pid] = ['pid' => $pid, 'weight' => $weight];
                }
            }
        } else {
            list($pid, $weight) = explode(':', $data['weight']);
            if ($pid) {
                $_tmpWeight[$pid] = ['pid' => $pid, 'weight' => $weight];
            }
        }
        $data['weight'] = $_tmpWeight;
        //通道
        $channels = M('Channel')->where(["paytype" => $data['paytype'], "status" => 1])->select();
        $this->assign('channels', $channels);
        $this->assign('pd', $data);
        $this->display('addProduct');
    }

    //编辑产品
    public function editBank()
    {
        $id   = I('get.pid', 0, 'intval');
        $data = M('Banks')->where(['id' => $id])->find();

        //权重
        $weights    = [];
        $weights    = explode('|', $data['weight']);
        $_tmpWeight = '';
        if (is_array($weights)) {
            foreach ($weights as $value) {
                list($pid, $weight) = explode(':', $value);
                if ($pid) {
                    $_tmpWeight[$pid] = ['pid' => $pid, 'weight' => $weight];
                }
            }
        } else {
            list($pid, $weight) = explode(':', $data['weight']);
            if ($pid) {
                $_tmpWeight[$pid] = ['pid' => $pid, 'weight' => $weight];
            }
        }
        $data['weight'] = $_tmpWeight;
        //通道
        $channels = M('Channel')->where(["paytype" => $data['paytype'], "status" => 1])->select();
        $this->assign('channels', $channels);
        $this->assign('paytypes', [
            ['id'=>'5','name'=>'网银跳转'],
            ['id'=>'6','name'=>'网银直连'],
        ]);
        $this->assign('pd', $data);
        $this->display('addBank');
    }



    //保存更改
    public function saveProduct()
    {
        if (IS_POST) {
            $id     = intval($_POST['id']);
            $rows   = $_POST['pd'];
            $weight = $_POST['w'];
            //权重
            $weightStr = '';
            if (is_array($weight)) {
                foreach ($weight as $weigths) {
                    if ($weigths['pid']) {
                        $weightStr .= $weigths['pid'] . ':' . $weigths['weight'] . "|";
                    }
                }
            }
            $rows['weight'] = trim($weightStr, '|');
            //保存
            if ($id) {
                $res = M('Product')->where(['id' => $id])->save($rows);
            } else {
                $res = M('Product')->add($rows);
            }
            $this->ajaxReturn(['status' => $res]);
        }
    }

    //保存网银通道更改
    public function saveBank()
    {
        if (IS_POST) {
            $id     = intval($_POST['id']);
            $rows   = $_POST['pd'];
            $weight = $_POST['w'];
            //权重
            $weightStr = '';
            if (is_array($weight)) {
                foreach ($weight as $weigths) {
                    if ($weigths['pid']) {
                        $weightStr .= $weigths['pid'] . ':' . $weigths['weight'] . "|";
                    }
                }
            }
            $rows['weight'] = trim($weightStr, '|');
            //保存
            if ($id) {
                $res = M('Banks')->where(['id' => $id])->save($rows);
            } else {
                $res = M('Banks')->add($rows);
            }
            $this->ajaxReturn(['status' => $res]);
        }
    }


    //删除产品
    public function delProduct()
    {
        if (IS_POST) {
            $id  = I('post.pid', 0, 'intval');
            $res = M('Product')->where(['id' => $id])->delete();
            $this->ajaxReturn(['status' => $res]);
        }
    }

    //删除网银通道产品
    public function delBankProduct()
    {
        if (IS_POST) {
            $id  = I('post.pid', 0, 'intval');
            $res = M('Banks')->where(['id' => $id])->delete();
            $this->ajaxReturn(['status' => $res]);
        }
    }


    //接口模式
    public function selProduct()
    {
        if (IS_POST) {
            $paytyep = intval($_POST['paytype']);
            //通道
            $data = M('Channel')->where(["paytype" => $paytyep, "status" => 1])->select();
            $this->ajaxReturn(['status' => 0, 'data' => $data]);
        }
    }

    /**
     * 通道账户列表
     */
    public function account()
    {
        $channel_id = I('get.pid');
        $channel    = M('Channel')->where(['id' => $channel_id])->find();
        $accounts   = M('channel_account')->where(['channel_id' => $channel_id])->select();
        $this->assign('channel', $channel);
        $this->assign('accounts', $accounts);
        $this->display();
    }

    /**
     * 编辑账户
     */
    public function editAccountControl()
    {
        if (IS_POST) {
            $data = I('post.data', '');

            if ($data['start_time'] != 0 || $data['end_time'] != 0) {
                if ($data['start_time'] >= $data['end_time']) {
                    $this->ajaxReturn(['status' => 0, 'msg' => '交易结束时间不能小于开始时间！']);
                }
            }
            if ($data['max_money'] != 0 && $data['min_money'] != 0) {
                if ($data['min_money'] >= $data['max_money']) {
                    $this->ajaxReturn(['status' => 0, 'msg' => '最大交易金额不能小于或等于最小金额！']);
                }
            }
            if ($data['is_defined'] == 0) {
                $channel_id = M('ChannelAccount')->where(['id' => $data['id']])->getField('channel_id');
                $channelInfo = M('Channel')->where(['id' => $channel_id])->find();
                $data['offline_status'] = $channelInfo['offline_status'];
                $data['control_status'] = $channelInfo['control_status'];
            }
            $res = M('ChannelAccount')->where(['id' => $data['id']])->save($data);
            $this->ajaxReturn(['status' => $res]);
        } else {
            $aid  = I('get.aid', '', 'intval');
            $info = M('ChannelAccount')->where(['id' => $aid])->find();

            $this->assign('info', $info);
            $this->assign('aid', $aid);
            $this->display();
        }

    }

    /**
     * 编辑账户
     */
    public function editAccount()
    {
        $aid = intval($_GET['aid']);
        if ($aid) {
            $pa = M('channel_account')->where(['id' => $aid])->find();
        }
        $this->assign('pa', $pa);
        $this->assign('pid', $pa['channel_id']);
        $this->display('addAccount');
    }

    /**
     * 新增账户
     */
    public function addAccount()
    {
        $pid = intval($_GET['pid']);
        $this->assign('pid', $pid);
        $this->display('addAccount');
    }

    public function showEven()
    {
        // echo "<pre>";
        $channelList = M('Channel')->where(['control_status' => 1, 'status' => 1])->select();
        $accountList = M('ChannelAccount')->where(['control_status' => 1, 'status' => 1])->select();

        $list = [];
        foreach ($channelList as $k => $v) {
            $v['offline_status'] = $v['offline_status'] ? '上线' : '下线';
            $list[$k]            = $v;
            foreach ($accountList as $k1 => $v1) {
                if ($v1['channel_id'] == $v['id']) {
                    $v1['offline_status']  = $v1['offline_status'] ? '上线' : '下线';
                    $list[$k]['account'][] = $v1;
                }
            }
        }
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 保存账户
     */
    public function saveEditAccount()
    {
        if (IS_POST) {
            $id                     = I('post.id', 0, 'intval');
            $papiacc                = $_POST['pa'];
            $_request['title']      = trim($papiacc['title']);
            $_request['channel_id'] = trim($papiacc['pid']);
            $_request['mch_id']     = trim($papiacc['mch_id']);
            $_request['signkey']    = trim($papiacc['signkey']);
            $_request['appid']      = trim($papiacc['appid']);
            $_request['appsecret']  = trim($papiacc['appsecret']);
            // 默认为1
            $weight                     = trim($papiacc['weight']);
            $_request['weight']         = $weight === '' ? 1 : $weight;
            $_request['custom_rate']    = $papiacc['custom_rate'];
            $_request['defaultrate']    = $papiacc['defaultrate'] ? $papiacc['defaultrate'] : 0;
            $_request['fengding']       = $papiacc['fengding'] ? $papiacc['fengding'] : 0;
            $_request['rate']           = $papiacc['rate'] ? $papiacc['rate'] : 0;
            $_request['updatetime']     = time();
            $_request['status']         = $papiacc['status'];
            $_request['is_defined']     = $papiacc['is_defined'];
            $_request['all_money']      = $papiacc['all_money'] == '' ? 0:$papiacc['all_money'];
            $_request['min_money']      = $papiacc['min_money'] == '' ? 0:$papiacc['min_money'];
            $_request['max_money']      = $papiacc['max_money'] == '' ? 0:$papiacc['max_money'];
            $_request['start_time']     = $papiacc['start_time'];
            $_request['offline_status'] = $papiacc['offline_status'];
            $_request['control_status'] = $papiacc['control_status'];
            if ($id) {
                //更新
                $res = M('channel_account')->where(array('id' => $id))->save($_request);
            } else {
                //添加
                $res = M('channel_account')->add($_request);
            }
            $this->ajaxReturn(['status' => $res]);
        }
    }

    //开启供应商接口
    public function editAccountStatus()
    {
        if (IS_POST) {
            $aid    = intval(I('post.aid'));
            $isopen = I('post.isopen') ? I('post.isopen') : 0;
            $res    = M('channel_account')->where(['id' => $aid])->save(['status' => $isopen]);
            $this->ajaxReturn(['status' => $res]);
        }
    }

    //删除供应商接口
    public function delAccount()
    {
        $aid = intval($_POST['aid']);
        if ($aid) {
            $res = M('channel_account')->where(['id' => $aid])->delete();
            $this->ajaxReturn(['status' => $res]);
        }
    }

    //编辑费率
    public function editAccountRate()
    {
        if (IS_POST) {
            $pa = $_POST['pa'];
            if ($_POST['aid']) {
                $res       = M('channel_account')->where(['id' => $_POST['aid']])->save($_POST['pa']);
                $pa['aid'] = $_POST['aid'];
                $this->ajaxReturn(['status' => $res, 'data' => $pa]);
            }
        } else {
            $aid = intval(I('get.aid'));
            if ($aid) {
                $data = M('channel_account')->where(['id' => $aid])->find();
            }

            $this->assign('aid', $aid);
            $this->assign('pa', $data);
            $this->display();
        }
    }

    //编辑风控
    public function editControl()
    {
        if (IS_POST) {
            $data = I('post.data', '');
            if ($data['start_time'] != 0 || $data['end_time'] != 0) {
                if ($data['start_time'] >= $data['end_time']) {
                    $this->ajaxReturn(['status' => 0, 'msg' => '交易结束时间不能小于开始时间！']);
                }
            }
            if ($data['max_money'] != 0 && $data['min_money'] != 0) {
                if ($data['min_money'] >= $data['max_money']) {
                    $this->ajaxReturn(['status' => 0, 'msg' => '最大交易金额不能小于或等于最小金额！']);
                }
            }
            $res = M('Channel')->where(['id' => $data['id']])->save($data);
            $this->ajaxReturn(['status' => $res]);
        } else {
            $pid  = I('get.pid', '');
            $info = M('Channel')->where(['id' => $pid])->find();
            $this->assign('info', $info);
            $this->assign('pid', $pid);
            $this->display();
        }
    }

    public function productDf()
    {
        //通道
        $channels = M('Systembank')
            ->field('id,bankcode,bankname')
            ->select();
        $this->assign('channels', $channels);
        $this->assign('channellist', json_encode($channels));
        $lists = [];
        foreach ($channels as $channel) {
            $list = $channel;
            $df_product = M('ProductDf')->where(['paytype' => $channel['id']])->find();
            $list['polling'] = '默认渠道';
            if ($df_product) {
                $list['polling'] = $df_product['polling'] == 1? '轮询' : '单独';
            }
            $lists[] = $list;
        }
        $this->assign('list', $lists);
        $this->display();
    }

    public function editProductDf()
    {
        $id   = I('get.bankid', 0, 'intval');
        $data = M('ProductDf')->where(['paytype' => $id])->find();

        if ( ! $data) {
            $bank = M('Systembank')->where(['id' => $id])->find();
            $data['name'] = $bank['bankname'];
            $data['code'] = $bank['bankcode'];
        }
        //权重
        $weights    = [];
        $weights    = explode('|', $data['weight']);
        $tmpWeight = '';
        if (is_array($weights)) {
            foreach ($weights as $value) {
                list($pid, $weight) = explode(':', $value);
                if ($pid) {
                    $tmpWeight[$pid] = ['pid' => $pid, 'weight' => $weight];
                }
            }
        } else {
            list($pid, $weight) = explode(':', $data['weight']);
            if ($pid) {
                $tmpWeight[$pid] = ['pid' => $pid, 'weight' => $weight];
            }
        }
        $data['weight'] = $tmpWeight;
        //通道
        $channels = M('PayForAnother')->where(["status" => 1])->select();
        $this->assign('channels', $channels);
        $this->assign('channellist', json_encode($channels));
        $this->assign('pd', $data);
        $this->assign('bankid', $id);
        $this->display('addProductDf');
    }

    public function saveProductDf()
    {
        if (IS_POST) {
            $id     = intval($_POST['id']);
            $bankid = intval($_POST['bankid']);
            $rows   = $_POST['pd'];
            $weight = $_POST['w'];
            //权重
            $weightStr = '';
            if (is_array($weight)) {
                foreach ($weight as $weigths) {
                    if ($weigths['pid']) {
                        $weightStr .= $weigths['pid'] . ':' . $weigths['weight'] . "|";
                    }
                }
            }

            $rows['weight'] = trim($weightStr, '|');
            $rows['status'] = 1;
            $rows['isdisplay'] = 1;

            $res = M('ProductDf')->where(['paytype' => $bankid])->find();
            if ($res) {
                M('ProductDf')->where(['paytype' => $bankid])->setField($rows);
            } else {
                $rows['paytype'] = $bankid;
                $res = M('ProductDf')->add($rows);
            }
            $this->ajaxReturn(['status' => $res]);
        }
    }
}
