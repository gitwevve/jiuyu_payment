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
                <h5>申请结算</h5>
            </div>
            <div class="ibox-content">
                <blockquote class="layui-elem-quote">
                    <span class="text-danger">可提现：<?php echo ($info['balance']); ?> 元</span>
                    <span style="margin:0 30px;" class="text-muted">冻结：<?php echo ($info['blockedbalance']); ?> 元</span>
                    <span class="text-warning">结算：T+<?php echo ($tkconfig[t1zt]); ?></span>
                </blockquote>

                <form class="layui-form" action="" autocomplete="off" id="calculate">
                    <input type="hidden" name="userid" value="<?php echo ($info[id]); ?>">
                    <input type="hidden" name="balance" id="balance" value="<?php echo ($info['balance']); ?>">
                    <input type="hidden" name="tktype" id="tktype" value="<?php echo ($tkconfig[tktype]); ?>">
                    <?php switch($tkconfig[tktype]): case "0": ?><input type="hidden" name="feilv" id="feilv" value="<?php echo ($tkconfig[sxfrate]); ?>"><?php break;?>
                        <?php case "1": ?><input type="hidden" name="feilv" id="feilv" value="<?php echo ($tkconfig[sxffixed]); ?>"><?php break; endswitch;?>

                    <div class="layui-form-item">
                        <label class="layui-form-label">提现金额：</label>
                        <div class="layui-input-block">
                            <input type="number" name="u[money]" lay-verify="required" id="money" min="100" step="100"
                                   autocomplete="off" onchange="calculate_rate(<?php echo ($info[id]); ?>)"
                                   placeholder="0.00" class="layui-input" value=""
                                   onkeyup="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}" onafterpaste="if(this.value.length==1){this.value=this.value.replace(/[^1-9]/g,'')}else{this.value=this.value.replace(/\D/g,'')}">
                            <div class="layui-form-mid layui-word-aux">注：提现金额最小<?php echo ($tkconfig["tkzxmoney"]); ?>元，含提现手续费,直接在金额中扣除.</div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">到账金额：</label>
                            <div class="layui-input-inline">
                                <input type="text" name="u[amount]" lay-verify="" id="amount" readonly autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">手续费：</label>
                            <div class="layui-input-inline">
                                <input type="text" name="u[brokerage]" id="brokerage" lay-verify="" readonly autocomplete="off"
                                       class="layui-input">
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">选择结算银行卡</label>
                        <div class="layui-input-block">
                            <select name="u[bank]" lay-filter="">
                                <option value=""></option>
                                <?php if(is_array($bankcards)): $i = 0; $__LIST__ = $bankcards;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$b): $mod = ($i % 2 );++$i;?><option value="<?php echo ($b["id"]); ?>"><?php echo ($b["bankname"]); ?>(<?php echo (substr($b["cardnumber"],'-4')); ?>)</option><?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">支付密码：</label>
                        <div class="layui-input-inline">
                            <input type="password" name="u[password]" lay-verify="pass" placeholder="请输入支付密码" autocomplete="off" class="layui-input">
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
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit="" lay-filter="save">提交申请</button>
                            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                        </div>
                    </div>
                </form>
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
    var issubmit=false;
    layui.use(['form', 'layer','element'], function(){
        var layer = layui.layer //弹层
            ,form = layui.form
            ,element = layui.element; //元素操作

        //监听提交
        form.on('submit(save)', function(data){
            layer.confirm('确定提现'+$("#money").val()+"元？", {
                btn: ['确定','取消'] //按钮
            }, function(){
                $.ajax({
                    url:"<?php echo U('Withdrawal/saveClearing');?>",
                    type:"post",
                    data:$('#calculate').serialize(),
                    success:function(res){
                        if(res.status){
                            layer.alert("申请成功", {icon: 6},function () {
                                location.reload();
                                var index = parent.layer.getFrameIndex(window.name);
                                parent.layer.close(index);
                            });
                        }else{
                            layer.alert(res.msg ? res.msg : "申请失败", {icon: 5},function () {
                                location.reload();
                                var index = parent.layer.getFrameIndex(window.name);
                                parent.layer.close(index);
                            });
                        }
                    }
                });
            }, function(){

            });

            return false;
        });
    });

    /*手续费计算*/
    function calculate_rate(userid){
        var type = $('#tktype').val()
            ,money = $('#money').val()
            ,feilv = $('#feilv').val()
            ,balance = $('#balance').val();
        $.ajax({
            url:"<?php echo U('Withdrawal/calculaterate');?>",
            type:'post',
            data:'userid='+userid+'&money='+money+'&balance='+balance+"&tktype="+type+'&feilv='+feilv,
            success:function(res){
                if(res.status){
                    $('#amount').val(res.data.amount);
                    $('#brokerage').val(res.data.brokerage);
                }else{
                    layer.alert(res.msg ? res.msg :"操作失败", {icon: 5},function () {
                        location.reload();
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    });
                }
            }
        });
    }
</script>
</body>
</html>