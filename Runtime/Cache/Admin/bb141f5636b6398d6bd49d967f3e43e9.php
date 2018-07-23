<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <title><?php echo ($sitename); ?>---管理</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/Public/Front/css/bootstrap.min.css" rel="stylesheet">
    <link href="/Public/Front/css/font-awesome.min.css" rel="stylesheet">
    <link href="/Public/Front/css/animate.css" rel="stylesheet">
    <link href="/Public/Front/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="/Public/Front/js/plugins/layui/css/layui.css">
    <style>
        .layui-form-label {width:110px;padding:4px}
        .layui-form-item .layui-form-checkbox[lay-skin="primary"]{margin-top:0;}
        .layui-form-switch {width:54px;margin-top:0px;}
    </style>
<body class="gray-bg">
<div class="wrapper wrapper-content animated">
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <!-- Content -->
            <div class="ibox-content">
                <form class="layui-form" action="<?php echo U("Auth/verifyGoogle");?>" autocomplete="off" method="post" id="incr">
                <input name="action_type" type="hidden" value="<?php echo ($action_type); ?>">
                <input name="redirect" type="hidden" value="<?php echo ($redirect); ?>">
                <input name="requestData" type="hidden" value="<?php echo ($requestData); ?>">
                <?php if(($google_token) == ""): ?><blockquote class="layui-elem-quote layui-quote-nm">
                        手机打开Google Authenticator(谷歌身份验证器)，<br>扫码二维码开启登录两步验证
                    </blockquote>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <img src="<?php echo ($qrCodeUrl); ?>" width="150px">
                            <p style="color:#fff;">不能扫码？点击 <a href="javascript:layer.alert('<?php echo ($secret); ?>')" style="color:#ef4300;text-decoration:none;">查看密钥</a> 手动添加</p>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit="" lay-filter="save">绑定</button>
                        </div>
                    </div>
                    <?php else: ?>
                    <blockquote class="layui-elem-quote layui-quote-nm">
                        手机打开Google Authenticator(谷歌身份验证器)，查看验证码
                    </blockquote>

                    <div class="layui-form-item">
                        <label class="layui-form-label">谷歌验证码：</label>
                        <div class="layui-input-block">
                            <input name="code" id="code" class="input-code layui-input" autocomplete="off" type="text" required lay-verify="required" pattern="[\S]{6}[\S]*" title="请输入6位验证码">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit="" lay-filter="save">立即提交</button>
                        </div>
                    </div><?php endif; ?>
                    </form>
            </div>
            <!-- Content -->
        </div>
    </div>
</div>
</div>
<script src="/Public/Front/js/jquery.min.js"></script>
<script src="/Public/Front/js/bootstrap.min.js"></script>
<script src="/Public/Front/js/plugins/peity/jquery.peity.min.js"></script>
<script src="/Public/Front/js/content.js"></script>
<script src="/Public/Front/js/plugins/layui/layui.js" charset="utf-8"></script>
<script src="/Public/Front/js/x-layui.js" charset="utf-8"></script>
<script>
    // 定义全局JS变量
    var GV = {
        current_controller: "admin//",
        base_url: "/Public/Front"
    };
</script>

<script src="/Public/Front/js/plugins/layui/layui.js"></script>

<!--页面JS脚本-->

<script>
    function changeCode() {

        $("#codeimage").attr("src",'<?php echo U("Admin/Login/verifycode");?>?t='+parseInt(40*Math.random()));
    };
    layui.use(['laydate', 'layer', 'element'], function(){
        var laydate = layui.laydate //日期
            ,layer = layui.layer //弹层
            ,element = layui.element; //元素操作
    });
    $(function(){
        $.supersized({
            // 功能
            slide_interval     : 4000,
            transition         : 1,
            transition_speed   : 1000,
            performance        : 1,
            // 大小和位置
            min_width          : 0,
            min_height         : 0,
            vertical_center    : 1,
            horizontal_center  : 1,
            fit_always         : 0,
            fit_portrait       : 1,
            fit_landscape      : 0,
            // 组件
            slide_links        : 'blank',
            slides             : [
                {image : '/Public/Front/newadmin/images/login/1.jpg'},
                {image : '/Public/Front/newadmin/images/login/2.jpg'},
                {image : '/Public/Front/newadmin/images/login/3.jpg'},
                {image : '/Public/Front/newadmin/images/login/4.jpg'},
                {image : '/Public/Front/newadmin/images/login/5.jpg'}
            ]
        });
        //显示隐藏验证码
        $("#hide").click(function(){
            $(".code").fadeOut("slow");
        });
        $("#captcha").focus(function(){

            $(".code").fadeIn("fast");
            if(getOs()=="Firefox"){
                $(".code").css("top","-85px");
            }
        });
        //跳出框架在主窗口登录
        if(top.location!=this.location)	top.location=this.location;
        $('#user_name').focus();
        $("#captcha").nc_placeholder();
        //动画登录
        $('.btn-submit').click(function(data){
            $('.btn-submit').addClass('animated fadeOutUp');
            setTimeout(function () {
                    $('.submit').hide();
                    $('.submit2').html('<div class="progress"><div class="progress-bar progress-bar-success" aria-valuetransitiongoal="100"></div></div>');
                    $('.progress .progress-bar').progressbar({
                        done : function() {
                            $.ajax({
                                url: $("#form_google").attr("action"),
                                type: $("#form_google").attr("method"),
                                data:$("#form_google").serialize(),
                                success: function (info) {
                                    if(info.status){
                                        setTimeout(function () {
                                            location.href = info.url;
                                        }, 1000);
                                        layer.msg(info.info);
                                    }else{
                                        layer.msg(info.info,{icon:5});
                                        setTimeout(function () {
                                            window.location.reload();
                                        }, 1000);
                                        return false;
                                    }
                                }
                            });
                        }
                    });
                },
                300);

        });
        // 回车提交表单
        $('#form_login').keydown(function(event){
            if (event.keyCode == 13) {
                $('.btn-submit').click();
            }
        });
        // 定义全局JS变量
        var GV = {
            current_controller: "admin//"
        };
    });
</script>

<script>
    //固定层移动
    $(function(){
        //管理显示与隐藏
        $("#admin-manager-btn").click(function () {
            if ($(".manager-menu").css("display") == "none") {
                $(".manager-menu").css('display', 'block');
                $("#admin-manager-btn").attr("title","关闭快捷管理");
                $("#admin-manager-btn").removeClass().addClass("arrow-close");
            }
            else {
                $(".manager-menu").css('display', 'none');
                $("#admin-manager-btn").attr("title","显示快捷管理");
                $("#admin-manager-btn").removeClass().addClass("arrow");
            }
        });
    });
</script>
</body>
</html>