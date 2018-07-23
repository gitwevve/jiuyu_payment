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
                <h5>代付申请</h5>
            </div>
            <div class="ibox-content">
				<div class="layui-tab">
					  <ul class="layui-tab-title">
						<li class="layui-this"><a href="<?php echo U("Withdrawal/dfapply");?>">表单提交方式</a></li>
						<li><a href="<?php echo U("Withdrawal/entrusted");?>">EXCEL导入方式</a></li>
					  </ul>
				</div>
                <blockquote class="layui-elem-quote">
                    <span class="text-danger">可提现：<?php echo ($info['balance']); ?> 元</span>
                    <span style="margin:0 30px;" class="text-muted">冻结：<?php echo ($info['blockedbalance']); ?> 元</span>
                    <span class="text-warning">结算：T+<?php echo ($tkconfig[t1zt]); ?></span>
                </blockquote>
                <div class="layui-inline">
                <button type="button" class="layui-btn layui-btn-danger" onclick="addRow()"><span
                        class="glyphicon glyphicon-plus"></span> 新增
                </button>
                </div>
                <form class="layui-form" id="df_form">
                <table class="layui-table">
                        <thead>
                        <tr>
                            <th>序号</th>
                            <th>结算金额</th>
                            <th>银行卡</th>
                            <?php if(is_array($extend_fields)): $i = 0; $__LIST__ = $extend_fields;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><th><?php echo ($vo); ?></th><?php endforeach; endif; else: echo "" ;endif; ?>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody id="container">
                            <tr>
                                <td>1</td>
                                <td>
                                    <input type="text" name="item[1][tkmoney]" lay-verify=""  autocomplete="off"
                                           class="layui-input df_item" style="width:150px">
                                </td>
                                <td>
                                    <select name="item[1][bank]" class="layui-select df_item">
                                    <option value=""></option>
                                    <?php if(is_array($bankcards)): $i = 0; $__LIST__ = $bankcards;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$b): $mod = ($i % 2 );++$i;?><option value="<?php echo ($b["id"]); ?>">【<?php echo ($b["accountname"]); ?>】<?php echo ($b["bankname"]); ?>(<?php echo (substr($b["cardnumber"],'-4')); ?>)<?php echo ($b["alias"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </td>
                                <?php if(is_array($extend_fields)): $i = 0; $__LIST__ = $extend_fields;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><td><input type="text" name="item[1][extend][<?php echo ($key); ?>]" lay-verify=""  autocomplete="off"
                                           class="layui-input df_item" style="width:150px"></td><?php endforeach; endif; else: echo "" ;endif; ?>
                                <td>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                    <div class="layui-form-item">
                        <label class="layui-form-label">支付密码：</label>
                        <div class="layui-input-inline">
                            <input type="password" name="password" lay-verify="pass" placeholder="请输入支付密码" autocomplete="off" class="layui-input">
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
                        </div>
                    </div>
                </form>
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
    var rowCount=1;
    //添加行
    function addRow(){
        rowCount++;
        var newRow='<tr id="option'+rowCount+'">' + '<td>'+rowCount+'</td> <td>'+
            '<input type="text" name="item['+rowCount+'][tkmoney]" lay-verify=""  autocomplete="off" class="layui-input df_item" style="width:150px"></td><td>'+ '<select name="item['+rowCount+'][bank]" class="layui-select df_item">'+
            '<option value=""></option>';
            <?php if(is_array($bankcards)): $i = 0; $__LIST__ = $bankcards;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$b): $mod = ($i % 2 );++$i;?>newRow+='<option value="<?php echo ($b["id"]); ?>"><?php echo ($b["bankname"]); ?>(<?php echo (substr($b["cardnumber"],'-4')); ?>)<?php echo ($b["alias"]); ?></option>';<?php endforeach; endif; else: echo "" ;endif; ?>
        newRow+='</select></td>';
        <?php if(is_array($extend_fields)): $i = 0; $__LIST__ = $extend_fields;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>newRow+='<td><input type="text" name="item['+rowCount+'][extend][<?php echo ($key); ?>]" lay-verify=""  autocomplete="off" class="layui-input df_item" style="width:150px"></td>';<?php endforeach; endif; else: echo "" ;endif; ?>
        newRow+='<td>';
        newRow+=' <button class="layui-btn layui-btn-small"  onclick="$(this).parent().parent().remove();">删除</button></td></tr>';
        $('#container').append(newRow);
        layui.form.render();
    }
    var issubmit=false;
    layui.use(['form', 'layer','element'], function(){
        var layer = layui.layer //弹层
            ,form = layui.form
            ,element = layui.element; //元素操作

        //监听提交
        form.on('submit(save)', function(data){
            layer.confirm('确定提现发起代付申请？', {
                btn: ['确定','取消'] //按钮
            }, function(){
                var flag = false;
                $('.df_item').each(function(){
                    if($(this).val() == '') {
                        flag = true;
                        return false;
                    }
                });
                if(flag == true) {
                    layer.alert('表格存在空值，请检查后再提交！');
                    return false;
                }
                $.ajax({
                    url:"<?php echo U('Withdrawal/dfsave');?>",
                    type:"post",
                    data:$('#df_form').serialize(),
                    success:function(res){
                        if(res.status){
                            layer.alert("申请成功", {icon: 6},function () {
                                location.reload();
                                var index = parent.layer.getFrameIndex(window.name);
                                parent.layer.close(index);
                            });
                        }else{
                            layer.alert(res.info ? res.info : "申请失败", {icon: 5},function () {
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
</script>
</body>
</html>