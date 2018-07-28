<?php
namespace User\Controller;

class UserController extends BaseController
{
    public $fans;
    public function __construct()
    {
        parent::__construct();
        //验证登录
        $user_auth = session("user_auth");
        ksort($user_auth); //排序
        $code = http_build_query($user_auth); //url编码并生成query字符串
        $sign = sha1($code);
        if($sign != session('user_auth_sign') || !$user_auth['uid']){
            header("Location: ".U(__MODULE__.'/Login/index'));
        }
        //用户信息
        $this->fans = M('Member')->where(['id'=>$user_auth['uid']])->field('`id` as uid, `username`, `password`, `groupid`, `parentid`,`salt`,`balance`, `blockedbalance`, `email`, `realname`, `authorized`, `apidomain`, `apikey`, `status`, `mobile`, `receiver`, `agent_cate`,`df_api`,`login_ip`,`open_charge`,`google_secret_key`,`session_random`,`regdatetime`')->find();
		$this->fans['memberid'] = $user_auth['uid']+10000;
		if ($user_auth['subid']) {
		    $subaccount = M('MemberAdmin')->where(['member_id' => $user_auth['uid'], 'id' => $user_auth['subid']])->find();
		    if ( ! $subaccount) {
                session("user_auth", null);
                header("Location: ".U(__MODULE__.'/Login/index'));
            }
		    $this->fans['subaccount'] = $subaccount;
        }
        if(session('user_auth') && $this->fans['google_secret_key'] &&  !session('user_google_auth')) {
            if(!(CONTROLLER_NAME == 'Account' && ACTION_NAME == 'unbindGoogle')
                &&!(CONTROLLER_NAME == 'Index' && ACTION_NAME == 'google')
                &&!(CONTROLLER_NAME == 'Login' && ACTION_NAME == 'verifycode')
                &&!(CONTROLLER_NAME == 'Account' && ACTION_NAME == 'unbindGoogleSend')
            ) {
                if(IS_AJAX){
                    $this->error('请进行谷歌身份验证', 'User/Index/google');
                }else{
                    $this->redirect('User/Index/google');
                }
            }
        }
        if(!session('user_auth.session_random') && $this->fans['session_random'] && session('user_auth.session_random') !=  $this->fans['session_random']) {
            session('user_auth', null);
            session('user_auth_sign', null);
            session('user_google_auth', null);
            $this->error('您的账号在别处登录，如非本人操作，请立即修改登录密码！','index.html');
        }
        $groupId = $this->groupId =  C('GROUP_ID');
        //获取用户的代理等级信息
        foreach($groupId as $k => $v){
            if($k>=$this->fans['groupid'])
                unset($groupId[$k]);
        }
        $this->assign('groupId',$groupId);
        $this->assign('fans',$this->fans);

        //左侧菜单栏
        $admin_auth_group_access_model = D('AdminAuthGroupAccess');
        $uid = array_key_exists('subaccount', $this->fans) ? $this->fans['subaccount']['id'] : 0;
        $navmenus = $admin_auth_group_access_model->getUserRules($uid);
        $this->assign('navmenus', $navmenus);
    }
}
?>
