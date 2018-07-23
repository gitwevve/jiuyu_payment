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
    <div class="col-md-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>短信模板管理</h5>
                <div class="row">
                    <div class="col-sm-2 pull-right">
                        <a href="javascript:;" class="layui-btn layui-btn-small"
                           onclick="smstemplate_add('添加短信模板','<?php echo U('System/addSmsTemplate');?>',540,440)">添加短信模板</a>
                    </div>
                </div>
            </div>
            <div class="ibox-content">
                <!--短信模板列表-->
                <div class="layui-field-box">
                <table class="layui-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>标题</th>
                        <th>模板代码</th>
                        <th>模板内容</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($cache)): $i = 0; $__LIST__ = $cache;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><tr>
                            <td><?php echo ($v["id"]); ?></td>
                            <td><?php echo ($v["title"]); ?></td>
                            <td>
                                <?php echo ($v["template_code"]); ?>
                            </td>
                            <td>
                                <?php echo ($v["template_content"]); ?>
                            </td>

                            <td>

                                <a onclick="smstemplate_edit('编辑短信模板','<?php echo U('System/editSmsTemplate',['id'=>$v[id]]);?>',540,440)"
                                   class="layui-btn layui-btn-mini layui-btn-normal"><i class="layui-icon">&#xe642;</i>编辑</a>
                            </td>
                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
                <?php echo ($page); ?>
            </div>
                <!--短信模板列表-->
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
<script>
    layui.use(['laypage','layer','form'], function() {
        var laypage = layui.laypage,
            $ = layui.jquery;
    });

    /*短信模板-添加*/
    function smstemplate_add(title,url,w,h) {
        x_admin_show(title,url,w,h);
    }
    /*短信模板-编辑*/
    function smstemplate_edit(title,url,w,h) {
        x_admin_show(title,url,w,h);
    }
    /*短信模板-删除*/
    function smstemplate_del(obj,id) {
        layer.confirm('确认要删除吗？',function(index){
            $.ajax({
                url:"<?php echo U('System/deleteAdmin');?>",
                type:'post',
                data:'id='+id,
                success:function(res){
                    if(res.status){
                        $(obj).parents("tr").remove();
                        layer.msg('已删除!',{icon:1,time:1000});
                    }
                }
            });
        });
    }
    function smstemplate_show(title,url,w,h){
        x_admin_show(title,url,w,h);
    }
</script>
</body>
</html>