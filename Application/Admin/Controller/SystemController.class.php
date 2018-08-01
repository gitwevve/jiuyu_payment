<?php
namespace Admin\Controller;

class SystemController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
    }

    //修改管理员密码
    public function editPassword()
    {
        if (IS_POST) {
            $ypassword     = trim(I("post.ypassword"));
            $newpassword   = trim(I("post.newpassword"));
            $newpasswordok = I("post.newpasswordok");
            if (md5($ypassword . C('DATA_AUTH_KEY')) != session('admin_auth')['password']) {
                $this->ajaxReturn(['status' => 0, 'msg' => '原密码错误！']);
            }
            if ($newpassword != $newpasswordok) {
                $this->ajaxReturn(['status' => 0, 'msg' => '两次输入密码不一致！']);
            }
            $userid = session('admin_auth');
            $res    = M('admin')->where(['id' => $userid['uid']])->save(['password' => md5($newpassword . C('DATA_AUTH_KEY')
            )]);
            $this->ajaxReturn(['status' => $res, 'msg' => 'success']);
        } else {
            $this->display();
        }
    }

    //基本设置
    public function base()
    {
        $Websiteconfig = D("Websiteconfig");
        $list          = $Websiteconfig->find();
        $this->assign("vo", $list);
        $this->display();
    }

    public function saveBase()
    {
        if (IS_POST) {
            $id      = I('post.id');
            $configs = I('post.config');
            $mconfig = M("Websiteconfig");
            if ($id) {
                $res = $mconfig->where(['id' => $id])->save($configs);
            } else {
                $res = $mconfig->add($configs);
            }
            if (!$res) {
                $this->ajaxReturn(['status' => 0, 'msg' => "修改失败，请稍后重试！"]);
            } else {
                $websitename = $configs['websitename'];
                $domain      = $configs['domain'];
                $directory   = $configs['directory'] == "" ? "Admin" : $configs['directory'];
                $login       = $configs['login'] == "" ? "Login" : $configs['login'];
                $str         = "";

                $str = "<?php \n";
                $str .= "\t\treturn array(\n";
                $str .= "\t\t\t'WEB_TITLE' => '" . $websitename . "',\n";
                $str .= "\t\t\t'DOMAIN' => '" . $domain . "',\n";
                $str .= "\t\t\t'MODULE_ALLOW_LIST'   => array('Home','User','" . ucfirst($directory) . "','Install', 'Weixin','Pay','Cashier','Agent','Payment'),\n";
                if ($directory != "Admin") {
                    $str .= "\t\t\t'URL_MODULE_MAP'  => array('" . strtolower($directory) . "'=>'admin', 'agent'=>'user', 'user'=>'user'),\n";
                }
                $str .= "\t\t\t'LOGINNAME' => '" . $login . "',\n";
                $str .= "\t\t\t'HOUTAINAME' => '" . $directory . "',\n";
                $str .= "\t\t);\n";
                $str .= "?>";

                file_put_contents(CONF_PATH . 'website.php', $str);
                $this->ajaxReturn(['status' => 1, 'msg' => "修改成功！"]);
            }
        }
    }

    public function email()
    {
        // 邮箱设置
        $Email = M("Email");
        $list  = $Email->find();
        $this->assign("vo", $list);
        $this->display();
    }

    public function saveEmail()
    {
        if (IS_POST) {
            $_formdata = array(
                'smtp_host'  => I('post.smtp_host'),
                'smtp_port'  => I('post.smtp_port'),
                'smtp_user'  => I('post.smtp_user'),
                'smtp_pass'  => I('post.smtp_pass'),
                'smtp_email' => I('post.smtp_email'),
                'smtp_name'  => I('post.smtp_name'),
            );
            $id    = I('post.id', 0, 'intval');
            $email = M("Email");
            if ($id) {
                $result = $email->where(['id' => $id])->save($_formdata);
            } else {
                $result = $email->add($_formdata);
            }
            $this->ajaxReturn(['status' => $result]);
        }

    }

    public function testEmail()
    {
        if (IS_POST) {
            $cs_email = I('post.cs_text');
            if (!$cs_email) {
                $this->ajaxReturn(['status' => 0, 'msg' => "测试收件邮箱地址不能为空"]);
            } else {
                $result = sendEmail($cs_email, '测试邮件', '测试邮件');
                if ($result == 1) {
                    $this->ajaxReturn(['status' => 1, 'msg' => "测试邮件发送成功，请注意查收！"]);
                } else {
                    $this->ajaxReturn(['status' => 0, 'msg' => "发送失败，错误信息：$result"]);
                }
            }
        }
    }

    public function smssz()
    {
        $Sms = M("Sms");

        $list = $Sms->find();

        $this->assign("vo", $list);

        $this->display();
    }
    public function saveSms()
    {
        if (IS_POST) {
            $_formdata = I("post.");
            $id        = I('post.id', 0, 'intval');
            $email     = M("Sms");
            if ($id) {
                $result = $email->where(['id' => $id])->save($_formdata);
            } else {
                $result = $email->add($_formdata);
            }
            $this->ajaxReturn(['status' => $result]);
        }

    }

    public function smsszedit()
    {
        $Sms = M("Sms");

        $Sms->create();

        if ($Sms->save()) {
            exit("修改成功！");
        } else {
            exit("修改失败！");
        }
    }

    public function smsTemplateList()
    {
        $m     = M("sms_template");
        $cache = $m->select();
        $this->assign("cache", $cache);
        $this->display();
    }
    public function addSmsTemplate()
    {
        $this->display();
    }
    public function editSmsTemplate()
    {
        $id = I("id", 0, "intval");
        if (!$id) {
            return;
        }

        $m = M("sms_template");

        $list = $m->where(['id' => $id])->find();

        $this->assign("vo", $list);

        $this->display();
    }

    public function saveSmstemplate()
    {
        if (IS_POST) {
            $_formdata = I("post.");
            $id        = I('post.id', 0, 'intval');
            $m         = M("sms_template");
            if ($id) {
                $result = $m->where(['id' => $id])->save($_formdata);
            } else {
                $_formdata["ctime"] = time();
                $result             = $m->add($_formdata);
            }

            $this->ajaxReturn(['status' => $result]);
        }
    }

    public function testMobile()
    {
        if (IS_POST) {
            $mobile = I('post.cs_text');
            if (!$mobile) {
                $this->ajaxReturn(['status' => 0, 'msg' => "测试手机号不能为空"]);
            } else {
                $smsTemplate = M("sms_template")->field("template_code")->where(array("call_index" => 'test'))->find();
                $result      = sendSMS($mobile, $smsTemplate["template_code"], array('code' => mt_rand(1000, 9999)));
                if ($result == 1) {
                    $this->ajaxReturn(['status' => 1, 'msg' => "测试短信发送成功，请注意查收！"]);
                } else {
                    $this->ajaxReturn(['status' => 0, 'msg' => "发送失败，错误信息：$result"]);
                }
            }
        }
    }

    public function csfasms()
    {
        $cs_email = I('request.cs_text', '');
        if ($cs_email == '') {
            exit("测试接收手机号不能为空");
        } else {
            $ReturnEmail = PHPFetion($cs_email, "测试短信", 0);
            if ($ReturnEmail == 1) {
                exit("测试短信发送成功，请注意查收！");
            } else {
                exit("发送失败，错误信息：" . $ReturnEmail);
            }
            exit($ReturnEmail);
        }
    }

    /**
     * 保存计划
     */
    public function planning()
    {
        if (IS_POST) {
            $config = $_POST['config'];
            $str    = <<<EOD
<?php
return [
    'PLANNING'=>[
        'postnum'=>"{$config['postnum']}",
        'allowstart'=>"{$config['allowstart']}",
        'allowend'=>"{$config['allowend']}",
    ]
];
EOD;
            file_put_contents(CONF_PATH . 'planning.php', $str);
            $this->ajaxReturn(['status' => 1]);
        } else {
            $config = C('PLANNING');
            $this->assign('configs', $config);
            $this->display();
        }
    }

    public function uploadImg()
    {
        if (IS_POST) {
          
            $upload           = new \Think\Upload();
            $upload->maxSize  = 5097152;
            $upload->exts     = array('jpg', 'gif', 'png');
            $upload->savePath = '/logo/';
            $info             = $upload->uploadOne($_FILES['file']);
            if (!$info) {
                // 上传错误提示错误信息
                $this->error($upload->getError());
            } else {
                $data = [
                    'logo' => 'Uploads' . $info['savepath'] . $info['savename'],
                ];
                $res = M('Websiteconfig')->where(['id' => 1])->save($data);
                $this->ajaxReturn($res);
            }
        }
    }

    public function mobile()
    {
        $uid = session('admin_auth')['uid'];
        $user = M('admin')->where(['id'=>$uid])->find();
        $this->assign("user", $user);
        $this->display();
    }

    /**
     * 绑定手机验证码
     */
    public function bindMobile()
    {
        $mobile = I('request.mobile');
        $res    = $this->send('adminbindMobile', $mobile, '绑定手机');
        $this->ajaxReturn(['status' => $res['code'], 'msg' => $res['message']]);
    }

    /**
     * 修改手机验证码
     */
    public function editMobile()
    {
        $uid = session('admin_auth')['uid'];
        $user = M('admin')->where(['id'=>$uid])->find();
        $mobile = I('request.mobile', '');
        if (!$mobile) {
            $mobile = $user['mobile'];
        }
        $res = $this->send('admineditMobile', $mobile, '修改手机');
        $this->ajaxReturn(['status' => $res['code']]);
    }

    /**
     *绑定手机
     */
    public function bindMobileShow()
    {
        if (IS_POST) {
            //验证验证码
            $code   = I('request.code');
            $mobile = I('request.mobile');
            $uid = session('admin_auth')['uid'];
            if (session('send.adminbindMobile') == $code && $this->checkSessionTime('adminbindMobile', $code)) {
                $res = M('Admin')->where(['id' => $uid])->save(['mobile' => $mobile]);
                $this->ajaxReturn(['status' => $res]);
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => '验证码错误']);
            }
        } else {
            $sms_is_open = smsStatus();
            if ($sms_is_open) {
                $id = I('request.id', '');
                $this->assign('sendUrl', U('System/bindMobile'));
                $this->assign('first_bind_mobile', 1);
                $this->assign('sms_is_open', $sms_is_open);
                $this->display();
            }
        }
    }

    /**
     *修改手机新手机
     */
    public function editMobileShow()
    {
        $sms_is_open = smsStatus();
        if (IS_POST) {
            $code = I('request.code');
            if (session('send.admineditMobile') == $code && $this->checkSessionTime('admineditMobile', $code)) {
                session('send.admineditMobile', null);
                $uid = session('admin_auth')['uid'];
                //判断是验证码新手机还是旧手机后的处理
                if (session('admineditmobile') == '1') {
                    $mobile           = I('request.mobile');
                    $return['status'] = M('Admin')->where(['id' => $uid])->save(['mobile' => $mobile]);
                    $return['data']   = 'editNewMobile';
                } else {
                    session('admineditmobile', 1);
                    $return['status'] = 1;
                }
                $this->ajaxReturn($return);
            } else {
                $this->ajaxReturn(['status' => 0, 'msg' => '验证码错误']);
            }
        } else {
            if ($sms_is_open) {
                $uid = session('admin_auth')['uid'];
                $user = M('Admin')->where(['id'=>$uid])->find();
                //判断是否是获取新手机验证码还是旧手机验证码的视图
                !I('request.editnewmobile', 0) && session('admineditmobile', 0);
                $this->assign('editmobile', session('admineditmobile'));
                $this->assign('sms_is_open', $sms_is_open);
                $this->assign('sendUrl', U('System/editMobile'));
                $this->assign('mobile', $user['mobile']);
                $this->display();
            }
        }
    }

    /**
     *  数据清理
     */
    public function clearData() {

        if(IS_POST) {
            $data = I('post.');
            $type_ids = $data['type'];
            if(empty($type_ids)){
                $this->ajaxReturn(['status'=>0,'msg'=>'请选择需清除的数据类型']);
            }
            $createtime  = urldecode(I("request.createtime"));
            if ($createtime) {
                list($cstime, $cetime)  = explode('|', $createtime);
                $startTime = strtotime($cstime);
                $endTime = strtotime($cetime);
                if(!$startTime || !$endTime || ($startTime >= $endTime)) {
                    $this->ajaxReturn(array('status' => 0, "时间范围错误"));
                }
            } else {
                $this->ajaxReturn(array('status' => 0, "请选择删除时间范围"));
            }
            $auth_type = I('request.auth_type',0,'intval');
            if($auth_type == 1) {//谷歌安全码验证
                $google_code   = I('request.google_code');
                if(!$google_code) {
                    $this->ajaxReturn(['status' => 0, 'msg' => "谷歌安全码不能为空！"]);
                } else {
                    $ga = new \Org\Util\GoogleAuthenticator();
                    $uid = session('admin_auth')['uid'];
                    $google_secret_key = M('Admin')->where(['id'=>$uid])->getField('google_secret_key');
                    if(!$google_secret_key) {
                        $this->ajaxReturn(['status' => 0, 'msg' => "您未绑定谷歌身份验证器！"]);
                    }
                    $oneCode = $ga->getCode($google_secret_key);
                    if($google_code !== $oneCode) {
                        $this->ajaxReturn(['status' => 0, 'msg' => "谷歌安全码错误！"]);
                    }
                }
            } else {//短信验证码
                $code   = I('request.code');
                if(!$code) {
                    $this->ajaxReturn(['status' => 0, 'msg'=>"短信验证码不能为空！"]);
                } else {
                    if (session('send.clearDataSend') != $code || !$this->checkSessionTime('clearDataSend', $code)) {
                        $this->ajaxReturn(['status' => 0, 'msg' => '验证码错误']);
                    }
                }
            }
            //开启事物
            M()->startTrans();
            //执行清除数据
            foreach($type_ids as $v) {
                if($v == 1) { //入金记录
                    $res1 = M('order')->where(['pay_applydate'=>['between', [$startTime, $endTime]]])->delete();
                } elseif($v == 2) {//出金记录
                    $res2 = M('wttklist')->where(['sqdatetime'=>['between', [$startTime, $endTime]]])->delete();
                    $res3 = M('tklist')->where(['sqdatetime'=>['between', [$startTime, $endTime]]])->delete();
                } elseif($v == 3) {//登录记录
                    $res4 = M('loginrecord')->where(['logindatetime'=>['between', [$startTime, $endTime]]])->delete();
                } elseif($v == 4) {//冻结记录
                    $res5 = M('blockedlog')->where(['createtime'=>['between', [date('Y-m-d H:i:s',$startTime), date('Y-m-d H:i:s',$endTime)]]])->delete();
                    $res6 = M('complaints_deposit')->where(['create_at'=>['between', [$startTime, $endTime]]])->delete();
                } elseif($v == 5) {//资金记录
                    $res7 = M('moneychange')->where(['datetime'=>['between', [date('Y-m-d H:i:s',$startTime), date('Y-m-d H:i:s',$endTime)]]])->delete();
                }
            }
            if(FALSE !== $res1 && FALSE !== $res2 && FALSE !== $res3 && FALSE !== $res4 && FALSE !== $res5 && FALSE !== $res6 && FALSE !== $res7) {
                M()->commit();
                $this->ajaxReturn(['status' => 1, 'msg' => "清理成功！"]);
            } else {
                M()->rollback();
                $this->ajaxReturn(['status' => 0, 'msg' => "清理失败！"]);
            }
        } else {
            $uid = session('admin_auth')['uid'];
            $user = M('Admin')->where(['id'=>$uid])->find();
            $this->assign('mobile', $user['mobile']);
            $this->display();
        }
    }

    /**
     * 设置订单为已支付验证码信息
     */
    public function clearDataSend()
    {
        $mobile = I('request.mobile');
        $res = $this->send('clearDataSend', $mobile ,'数据清理');
        $this->ajaxReturn(['status' => $res['code']]);
    }
}
