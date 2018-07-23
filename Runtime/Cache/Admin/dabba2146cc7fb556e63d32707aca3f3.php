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
        <div class="ibox-content">
            <form class="layui-form" action="" autocomplete="off" id="article">
                <input type="hidden" name="id" value="<?php echo ($a["id"]); ?>">
                <div class="layui-form-item">
                    <label class="layui-form-label">标题：</label>
                    <div class="layui-input-block">
                        <input type="text" name="c[title]" lay-filter="required" autocomplete="off" placeholder="请输入标题" class="layui-input" value="<?php echo ($a["title"]); ?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">搜索选择栏目</label>
                        <div class="layui-input-inline">
                            <select name="c[catid]" lay-filter="required" lay-search="">
                                <option value="">直接选择或搜索选择</option>
                                <?php if(is_array($category)): $i = 0; $__LIST__ = $category;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$c): $mod = ($i % 2 );++$i;?><option <?php if($c[id] == $a[catid]): ?>selected<?php endif; ?> value="<?php echo ($c['id']); ?>">├─<?php echo ($c['name']); ?></option>
                                    <?php if($c[_child]): if(is_array($c[_child])): $i = 0; $__LIST__ = $c[_child];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?><option <?php if($sub[id] == $a[catid]): ?>selected<?php endif; ?> value="<?php echo ($sub['id']); ?>">└─ <?php echo ($sub['name']); ?></option><?php endforeach; endif; else: echo "" ;endif; endif; endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">分组显示</label>
                        <div class="layui-input-inline">
                            <select name="c[groupid]"  lay-search="">
                                <option <?php if($a[groupid] == '0'): ?>selected<?php endif; ?> value="0">平台可见</option>
                                <option <?php if($a[groupid] == '1'): ?>selected<?php endif; ?> value="1">商户可见</option>
                                <option <?php if($a[groupid] == '2'): ?>selected<?php endif; ?> value="2">代理商可见</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">描述：</label>
                    <div class="layui-input-block">
                        <textarea placeholder="请输入描述" name="c[description]" class="layui-textarea"><?php echo ($a ["description"]); ?></textarea>
                    </div>
                </div>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">内容：</label>
                    <div class="layui-input-block">
                        <textarea class="layui-textarea layui-hide" lay-filter="required" name="c[content]" lay-verify="content" id="LAY_demo_editor"><?php echo ($a["content"]); ?></textarea>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">发表日期：</label>
                        <div class="layui-input-block">
                            <input type="text" name="c[createtime]" id="date" autocomplete="off" class="layui-input"
                                   value="<?php echo (date('Y-m-d H:i:s',$a["createtime"])); ?>">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">状态：</label>
                    <div class="layui-input-block">
                        <input type="radio" <?php if($a[status] == 1): ?>checked<?php endif; ?> name="c[status]" value="1" title="显示" checked="">
                        <input type="radio" <?php if($a[status] == 0): ?>checked<?php endif; ?> name="c[status]" value="0" title="隐藏">
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
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
<script>
    layui.use(['form', 'layedit', 'laydate'], function(){
        var form = layui.form
            ,layer = layui.layer
            ,layedit = layui.layedit
            ,laydate = layui.laydate;

        //日期
        laydate.render({
            elem: '#date'
            ,type: 'datetime'
        });
        //创建一个编辑器
        var editIndex = layedit.build('LAY_demo_editor');
        //自定义验证规则
        form.verify({
            content: function(value){
                layedit.sync(editIndex);
            }
        });
        //监听提交
        form.on('submit(demo1)', function(data){
            $.ajax({
                url:"<?php echo U('Content/saveEditArticle');?>",
                type:'post',
                data:$('#article').serialize(),
                success:function(res){
                    if(res.status){
                        layer.alert("编辑成功", {icon: 6},function () {
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