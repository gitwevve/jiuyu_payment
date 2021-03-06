<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo ($type); ?>登录-API聚合支付</title>
    <link rel="stylesheet" type="text/css" href="css/reset.css">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <script src="js/jquery.min.js" type="text/javascript" charset="utf-8"></script>
    <script src="/js/layer/layer.min.js"></script>
    <!--[if lt IE 9]>
    <script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js" type="text/javascript" charset="utf-8"></script>
    <![endif]-->

</head>
<body>

<div class="loginWrapper clearfix">
    <div class="banWrapper" style="background: #00a7f2">
        <div class="banContent">
            <a href="index.html" target="_blank" title="Data，Change The World">
                <img src="image/<?php echo ($bg); ?>" border="0"/></a>
        </div>
    </div>
    <div class="loginMain">
        <div class="loginWidth">
            <div class="loginLogoDiv"><a class="loginLogo" href="#"><img src="image/logo2.png" /></a></div>
            <form class="formLogin"  data-animate-effect="fadeInRight" id="formlogin" name="formlogin"
                  method="post" role="form" action="<?php echo ($loginUrl); ?>">
                <div class="loginList loginListUser">
                    <label></label>
                    <input type="text" class="loginText" name="username" id="username" value=""
                           placeholder="请输入用户名"/>
                    <span class="errorTips"><i></i><em></em></span>
                </div>
                <div class="loginList loginListPwd">
                    <label></label>
                    <input type="password" class="loginText" name="password" id="password" value="" placeholder="请输入密码"/>
                    <span class="errorTips"><i></i><em></em></span>
                </div>


                <div class="loginList loginListCode" style="">
                    <label></label>
                    <input type="text" class="loginText" name="captcha" id="captcha" value="" placeholder="验证码"/>
                    <img src="<?php echo U('verifycode');?>?t=<?php echo time();?>" style="cursor:pointer" id="vercodeImg" alt="" onclick='this.src="<?php echo U('verifycode');?>?t=<?php echo time();?>"' title="点击刷新验证码"/>

                </div>

                <div class="sysError" style="display:none"><label></label><span><i></i><em></em></span></div>


                <input class="loginBtn" type="button" id="loginBtn" value="登录"/>
                <p class="have">还没有账号，<a href="<?php echo U('Login/register');?>">立即注册<i></i></a></p>

            </form>
        </div>
    </div>
</div>



<script>


    $("#loginBtn").click(function () {

        var username = $("#username").val();
        var password = $("#password").val();
        var varification = $("#captcha").val();


        username = $.trim(username);
        password = $.trim(password);
        varification = $.trim(varification);

        if (username.length < 1) {

            layer.msg('请输入正确格式的用户名');
            return false;

        }
        else if (password.length < 6) {
            layer.msg('请输入正确格式的密码');
            return false;

        }
        else if (varification == '') {
            layer.msg('请输入正确格式的验证码');
            return false;

        }

            $.ajax({
                type:'post',
                url:'user_Login_checklogin.html',
                data: { username: username, password: password, varification:varification},
                dataType:'json',

                success:function(result){
                    if(result['status'] == 0){
                        layer.msg(result['info']);
                    }else{

                        layer.msg('登录成功，正在跳转到用户中心...    ');
                        setTimeout(function() {
                            window.location.href = "<?php echo ($siteurl); ?>" + "user_Index_index.html";
                        },3000 );


                    }
                }
            })

    })
</script>


</body>
</html>