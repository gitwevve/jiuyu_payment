<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <title><?php echo ($sitename); ?>---用户管理中心</title>
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
    <div class="col-md-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>绑定谷歌身份验证码</h5>
            </div>
            <div class="ibox-content">
                <!--用户信息-->
                <form class="layui-form" action="" autocomplete="off" id="bindgoogle">
                    
                    <?php if(($google_token) != ""): ?><div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label"></label>
                                <div class="layui-input-inline" style="width: 300px;">
                                    <p>您已绑定谷歌身份验证器 &nbsp;&nbsp;&nbsp;<a href="<?php echo U('User/Account/unbindGoogle');?>" style="color:#4395ff">解绑</a></p>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <label class="layui-form-label"></label>
                                <div class="layui-input-inline" style="text-align: center;">
                                    <p style="text-align: center;padding-bottom: 10px;">手机打开Google Authenticator(谷歌身份验证器)，扫描二维码</p>
                                    <img src="<?php echo ($qrCodeUrl); ?>" width="200px;padding-top: 10px;">
                                    <p style="text-align: center;">不能扫码？点击 <a href="javascript:layer.alert('<?php echo ($secret); ?>')" style="color:#ef4300;text-decoration:none;">查看密钥</a> 手动添加</p>
                                </div>
                            </div>
                        </div>
                        <script src="/Public/Front/js/jquery.min.js"></script>
<?php if($sms_is_open): ?><div class="layui-form-item">
    <label class="layui-form-label">手机验证码：</label>
    <div class="layui-input-inline">
        <input type="text" name="code" lay-verify="required" autocomplete="off"
               placeholder="" class="layui-input" value="">
    </div>
    <div class="layui-input-inline">
        <a href="javascript:;" id="sendBtn" data-bind='<?php echo ($first_bind_mobile); ?>' class="layui-btn" data-mobile="<?php echo ($fans[mobile]); ?>">发送验证码</a>
    </div>
</div><?php endif; ?>
<script>
    $(function (){
        // 手机验证码发送
        $('#sendBtn').click(function(){
            var mobile = $(this).data('mobile');
            var first_bind = $(this).data('bind');
            var sendUrl = "<?php echo ($sendUrl); ?>";
            if(!mobile){
                //判断用户是否准备绑定手机号
                if(!first_bind){
                    layer.alert('请先填写手机号码',{icon: 5}, function() {
                        location.href = "<?php echo U('Account/profile');?>";
                    });
                }else{
                    layer.alert('请先填写手机号码',{icon: 5});
                }
                return;
            }
            sendSms(this, mobile, sendUrl);
        });
    })
</script>
                        <div class="layui-form-item">
                            <label class="layui-form-label">谷歌安全码：</label>
                            <div class="layui-input-inline">
                                <input type="text" name="googlecode" lay-verify="required" autocomplete="off" class="layui-input" value="" placeholder="扫码绑定后查看">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit="" lay-filter="bindmobile">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div><?php endif; ?>
                </form>
                <!--用户信息-->
            </div>
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
<script src="/Public/Front/js/Util.js" charset="utf-8"></script>
<script>
    //短信验证是否开启
    var sms_is_open = "<?php echo ($sms_is_open); ?>";
    layui.use(['laydate', 'laypage', 'layer', 'form', 'element'], function() {
        var laydate = layui.laydate //日期
            ,layer = layui.layer //弹层
            ,form = layui.form //弹层
            , element = layui.element; //元素操作
        //日期
        laydate.render({
            elem: '#date'
        });

        $('#mobile').on('blur',function(){
            var mobile = $(this).val();
            $('#sendBtn').attr('data-mobile', mobile);
        });

        //监听提交
        form.on('submit(bindmobile)', function(data){
            $.ajax({
            url:"<?php echo U('Account/google');?>",
            type:"post",
            data:$('#bindgoogle').serialize(),
            success:function(res){
                if(res.status){
                    layer.alert(res.msg, {icon: 6},function () {
                        window.location.reload();
                    });
                }else{
                    layer.alert(res.msg ? res.msg :"操作失败", {icon: 5});
                }
            }
        });
            return false;
        });
    });
</script>
</body>
</html>