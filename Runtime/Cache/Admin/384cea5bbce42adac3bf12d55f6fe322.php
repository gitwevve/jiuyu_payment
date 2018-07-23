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
                <h5>管理代理分类</h5>
                <div class="row">
                    <div class="col-sm-2 pull-right">
                        <a href="javascript:;" class="layui-btn layui-btn-small"
                           onclick="admin_add('添加代理分类','<?php echo U('User/addAgentCate');?>',540,440)">添加用户等级</a>
                    </div>
                </div>
            </div>
            <div class="ibox-content">
                <!--管理员列表-->
                <div class="layui-field-box">
                <table class="layui-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>代理分类名称</th>
                        <th>排序</th>
                        <th>备注</th>
                        <th>添加时间</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><tr>
                            <td><?php echo ($v["id"]); ?></td>
                            <td><?php echo ($v["cate_name"]); ?></td>
                            <td><?php echo ($v["sort"]); ?></td>
                            <td><?php echo ($v["desc"]); ?></td>
                            <td>
                                <?php echo (date('Y-m-d H:i:s',$v["ctime"])); ?>
                            </td>
                            <td>

                                <a onclick="admin_edit('编辑代理分类','<?php echo U('User/editAgentCate',['id'=>$v[id]]);?>',540,440)"
                                   class="layui-btn layui-btn-mini layui-btn-normal"><i class="layui-icon">&#xe642;</i>编辑</a>
                                <a onclick="admin_del(this,'<?php echo ($v[id]); ?>')" class="layui-btn layui-btn-danger layui-btn-mini"><i class="layui-icon">&#xe640;</i>删除</a>
                            </td>
                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
                <?php echo ($page); ?>
            </div>
                <!--管理员列表-->
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

    /*管理员-添加*/
    function admin_add(title,url,w,h) {
        x_admin_show(title,url,w,h);
    }
    /*管理员-编辑*/
    function admin_edit(title,url,w,h) {
        x_admin_show(title,url,w,h);
    }
    /*管理员-删除*/
    function admin_del(obj,id) {
        layer.confirm('确认要删除吗？',function(index){
            $.ajax({
                url:"<?php echo U('User/deleteAgentCate');?>",
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
    function admin_show(title,url,w,h){
        x_admin_show(title,url,w,h);
    }
</script>
</body>
</html>