<?php
/**
 * Created by PhpStorm.
 * User: dong
 * Date: 2018/7/28
 * Time: 22:35
 */
namespace User\Controller;

class AuthController extends UserController
{
    public function index()
    {
        $admin_model = D('Admin');
        $data = $admin_model->getAdminList($this->fans['uid']);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->display();

    }

    public function addAdmin(){

        if(IS_POST){
            $data=I("post.");
            if(!$data["username"]){
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入用户名!']);
            }
            if(!$data["password"]){
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入密码!']);
            }
            if($data["password"] != $data["reppassword"]){
                $this->ajaxReturn(['status'=>0,'msg'=>'两次输入密码不一致！']);
            }

            $data["password"]=md5($data["password"].C('DATA_AUTH_KEY'));
            $data["createtime"]=time();
            $data['member_id'] = $this->fans['uid'];

            $admin_model = D('Admin');
            if($admin_model->field("id")->where(array("username"=>$data["username"], "member_id" => $this->fans['uid']))->find()){
                $this->ajaxReturn(['status'=>0,'msg'=>'用户名已存在！']);
            }
            $add_admin_result = $admin_model->add($data);
            if($add_admin_result){
                //更新权限
                $groupAccess= M("member_auth_group_access")->where(array("uid"=>$add_admin_result))->find();
                if($groupAccess&&$groupAccess["group_id"]!=$data["groupid"]){
                    M("member_auth_group_access")->where(array("uid"=>$add_admin_result))->setField("group_id",$data["groupid"]);
                }else{
                    M("member_auth_group_access")->add(array("uid"=>$add_admin_result,"group_id"=>$data["groupid"]));
                }
            }
            $this->ajaxReturn(['status'=>$add_admin_result]);
        }else{
            //用户组
            $groups = M('MemberAuthGroup')->where(['member_id' => $this->fans['uid'], 'status' => 1])->field('id,title')->select();
            $this->assign('groups',$groups);
            $this->display();
        }

    }
    public function deleteAdmin()
    {
        $id = I('id', 0, 'intval');
        if(!$id){
            parent::ajaxError('管理员不存在!');
        }

        $admin_model = D('Admin');
        $admin = $admin_model->findAdmin($id);
        if(!$admin){
            $this->ajaxReturn(['status'=>0,'msg'=>'角色不存在!']);
        }
        $change_result= $admin_model->delete($id);
        if($change_result){
            M("member_auth_group_access")->where(array("uid"=>$id))->delete();
        }
        $this->ajaxReturn(['status'=>$change_result]);
    }

    public function editAdmin(){
        if(IS_POST){
            $data=I("post.");
            if(!$data['id']){
                $this->ajaxReturn(['status'=>'error','msg'=>'管理员不存在!']);
            }

            if($data["epassword"]&&$data["epassword"] != $data["ereppassword"]){
                $this->ajaxReturn(['status'=>0,'msg'=>'两次输入密码不一致！']);
            }
            if($data["epassword"]){
                $data["password"]=md5($data["epassword"].C('DATA_AUTH_KEY'));
            }

            if(!$data['username']){
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入用户名！']);

            }
            $admin_model = D('Admin');
            if($admin_model->field("id")->where(array("username"=>$data["username"],"id"=>array("neq",$data['id'])))->find()){
                $this->ajaxReturn(['status'=>0,'msg'=>'用户名已存在！']);
            }

            $admin_result = $admin_model->save($data);
            if($admin_result!==false){
                //更新权限
                $groupAccessModel = D('AdminAuthGroupAccess');
                $groupAccess = $groupAccessModel->where(array("uid"=>$data['id']))->find();
                if ($groupAccess) {
                    $groupAccessModel->save(["group_id"=>$data["groupid"]]);
                } else {
                    M("member_auth_group_access")->add(array("uid"=>$data['id'],"group_id"=>$data["groupid"]));
                }
                $this->ajaxReturn(['status'=>1,'msg'=>'修改成功!']);
            }else{
                $this->ajaxReturn(['status'=>0,'msg'=>'修改失败!']);
            }

        }else{
            $id = I('id', 0, 'intval');
            $admin_model = D('Admin');
            $admin_info = $admin_model->findAdmin($id);
            //用户组
            $groups = M('MemberAuthGroup')->where(array('member_id' => $this->fans['uid']))->field('id,title')->select();
            $this->assign('groups',$groups);
            $this->assign('admin_info', $admin_info);
            $this->display();
        }
    }

    //列表
    public function role()
    {
        $admin_auth_group_model = D('AdminAuthGroup');
        $data = $admin_auth_group_model->getGroupList($this->fans['uid']);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->display();
    }

    /**
     * 添加角色页面显示
     */
    public function addGroup()
    {
        if(IS_POST){
            $is_manager = I('post.is_manager') == 'on' ? 1 :0;
            $params = array(
                'title' => I('title','','trim'),
                'is_manager'=>$is_manager,
                'status' => 1,
                'rules' => '',
                'member_id' => $this->fans['uid']
            );

            if(!$params['title']){
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入角色名称!']);
            }
            $admin_auth_group_model = D('AdminAuthGroup');
            $add_group_result = $admin_auth_group_model->add($params);
            $this->ajaxReturn(['status'=>$add_group_result]);
        }else{
            $this->display();
        }

    }


    /**
     * 编辑角色页面显示
     */
    public function editGroup()
    {
        if(IS_POST){
            $params = array(
                'id' => I('id', 0, 'intval'),
                'title' => I('title'),
            );
            if(!$params['id']){
                $this->ajaxReturn(['status'=>'error','msg'=>'角色不存在!']);
            }
            if(!$params['title']){
                parent::ajaxError('请输入角色名称!');
            }
            /* @var $admin_auth_group_model \Admin\Model\AdminAuthGroupModel */
            $admin_auth_group_model = D('AdminAuthGroup');
            $save_group_result = $admin_auth_group_model->save($params);
            $this->ajaxReturn(['status'=>$save_group_result,'msg'=>'修改成功!']);
        }else{
            $id = I('id', 0, 'intval');
            /* @var $admin_auth_group_model \Admin\Model\AdminAuthGroupModel */
            $admin_auth_group_model = D('AdminAuthGroup');
            $group_info = $admin_auth_group_model->findGroup($id);

            $this->assign('group_info', $group_info);
            $this->display();
        }

    }

    /**
     * 删除角色处理
     */
    public function deleteGroup()
    {
        $id = I('id', 0, 'intval');
        if(!$id){
            parent::ajaxError('角色不存在!');
        }
        /* @var $admin_auth_group_model \Admin\Model\AdminAuthGroupModel */
        $admin_auth_group_model = D('AdminAuthGroup');
        $group_info = $admin_auth_group_model->findGroup($id);
        if(!$group_info){
            $this->ajaxReturn(['status'=>0,'msg'=>'角色不存在!']);
        }
        $change_result = $admin_auth_group_model->changeResult($id, 2);
        $this->ajaxReturn(['status'=>$change_result]);
    }

    /**
     * 分配角色
     */
    public function giveRole()
    {
        if(IS_POST){
            $user_id = I('user_id', 0, 'intval');
            if(!$user_id){
                parent::ajaxError('用户不存在!');
            }

            $group_id = $_POST['group_id'];
            //html_entity_decode($string)
            /* @var $admin_auth_group_model \Admin\Model\AdminAuthGroupModel */
            $admin_auth_group_access_model = D('AdminAuthGroupAccess');

            if(!empty($group_id)){
                //删除原有角色
                $admin_auth_group_access_model->where(array('uid'=>$user_id))->delete();
                foreach($group_id as $v){
                    $add_data = array(
                        'uid' => $user_id,
                        'group_id' => $v,
                    );
                    $admin_auth_group_access_model->add($add_data);
                }
            }
            parent::ajaxSuccess('分配成功!');
        }else{
            $user_id = I('user_id', 0, 'intval');

            /* @var $admin_auth_group_model \Admin\Model\AdminAuthGroupModel */
            $admin_auth_group_model = D('AdminAuthGroup');
            $data = $admin_auth_group_model->getGroupList($user_id);

            $this->assign('list', $data['list']);
            $this->assign('user_id', $user_id);
            $this->display();
        }
    }

    /**
     * 分配权限
     */
    public function ruleGroup()
    {
        /* @var $admin_auth_group_model \Admin\Model\AdminAuthGroupModel */
        $admin_auth_group_model = D('AdminAuthGroup');
        if(IS_POST){
            $data = I('post.');
            $rule_ids = implode(",", $data['menu']);
            $role_id = $data['roleid'];
            if(!count($rule_ids)){
                $this->ajaxReturn(['status'=>0,'msg'=>'请选择需要分配的权限']);
            }
            if($admin_auth_group_model->addAuthRule($rule_ids, $role_id) !== false){
                $this->ajaxReturn(['status'=>1,'msg'=>'分配成功']);
            }else{
                $this->ajaxReturn(['status'=>0,'msg'=>'分配失败，请检查']);
            }
        }else{

            $role_id = I('get.roleid',0,'intval');
            /* @var $menu_model \Admin\Model\AdminMenuModel */
            $menu_model = D('AdminMenu');

            $menus = get_column($menu_model->selectAllMenu(2),2);
            $role_info = $admin_auth_group_model->findGroup($role_id);

            if($role_info['rules']){
                $rulesArr = explode(',',$role_info['rules']);

                $this->assign('rulesArr',$rulesArr);
            }
            $this->assign('menus',$menus);
            $this->assign('role_id',$role_id);
            $this->display();
        }
    }

}