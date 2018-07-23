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
    <form class="layui-form" id="groupForm" method="post" autocomplete="off" action="">

      <div class="layui-form-item">
        <label class="layui-form-label">等级名称：</label>
        <div class="layui-input-inline">
          <input type="text" name="item[cate_name]" lay-verify="required" placeholder="请输入等级名称" autocomplete="off"  id="cate_name" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">排序：</label>
        <div class="layui-input-inline">
          <input type="text" name="item[sort]"  placeholder="排序" value="99" autocomplete="off" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">备注：</label>
        <div class="layui-input-inline">
          <input type="text" name="item[desc]"  placeholder="备注"  autocomplete="off" class="layui-input">
        </div>
      </div>

      <div class="layui-form-item">
        <div class="layui-input-block">
          <button class="layui-btn" lay-submit lay-filter="user">立即提交</button>
          <button type="reset" class="layui-btn layui-btn-primary">重置</button>
        </div>
      </div>
    </form>
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
  layui.use(['layer', 'form'], function(){
      var $ = layui.jquery
          ,form = layui.form
          ,layer = layui.layer;

      //监听提交
      form.on('submit(user)', function(data){

          $.ajax({
              url:"<?php echo U('User/saveAgentCate');?>",
              type:"post",
              data:$('#groupForm').serialize(),
              success:function(res){
                  if(res.status){
                      layer.alert("操作成功", {icon: 6},function () {
                          parent.location.reload();
                          var index = parent.layer.getFrameIndex(window.name);
                          parent.layer.close(index);
                      });
                  }else{

                      layer.msg(res.msg ? res.msg : "操作失败!", {icon: 5},function () {
                          var index = parent.layer.getFrameIndex(window.name);
                          parent.layer.close(index);
                      });
                      return false;
                  }
              }
          });
          return false;
      });
  });
</script>
</body>
</html>