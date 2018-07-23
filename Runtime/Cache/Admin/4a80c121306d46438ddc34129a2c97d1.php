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
                <!--条件查询-->
                <div class="ibox-title">
                    <h5>代理交易列表</h5>
                    <div class="ibox-tools">
                        <i class="layui-icon" onclick="location.replace(location.href);" title="刷新"
                           style="cursor:pointer;">ဂ</i>
                    </div>
                </div>
                <!--条件查询-->
                <div class="ibox-content">
                    <form class="layui-form" action="" method="get" autocomplete="off">
                        <input type="hidden" name="m" value="<?php echo ($model); ?>">
                        <input type="hidden" name="c" value="User">
                        <input type="hidden" name="a" value="index">
                        <input type="hidden" name="p" value="1">
                        <div class="layui-form-item">
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <input type="text" name="username" autocomplete="off" placeholder="商户号或用户名"
                                           class="layui-input" value="<?php echo ($_GET['username']); ?>">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <input type="text" name="parentid" autocomplete="off" placeholder="上级商户号或用户名"
                                           class="layui-input" value="<?php echo ($_GET['parentid']); ?>">
                                </div>
                            </div>
                            <div class="layui-inline">
                                <div class="layui-input-inline">
                                    <select name="groupid">
                                        <option value="">代理分类</option>
                                        <?php if(is_array($agentCateSel)): foreach($agentCateSel as $k=>$v): ?><option value="<?php echo ($k); ?>"><?php echo ($v); ?></option><?php endforeach; endif; ?>
                                    </select>
                                </div>
                                <div class="layui-input-inline">
                                    <select name="status">
                                        <option value="">状态</option>
                                        <option value="1">已激活</option>
                                        <option value="0">未激活</option>
                                        <option value="2">禁用</option>
                                    </select>
                                </div>
                                <div class="layui-input-inline">
                                    <select name="authorized">
                                        <option value="">认证</option>
                                        <option value="0">未认证</option>
                                        <option value="2">等待审核</option>
                                        <option value="1">认证用户</option>
                                    </select>
                                </div>
                                <div class="layui-input-inline">
                                    <input type="text" class="layui-input" name="regdatetime" id="regtime"
                                           placeholder="起始时间">
                                </div>
 
                            </div>

                            <div class="layui-inline">
                                <button type="submit" class="layui-btn"><span
                                        class="glyphicon glyphicon-search"></span> 搜索
                                </button>
                                <a href="javascript:;" id="export"
                                   class="layui-btn layui-btn-danger"><span
                                        class="glyphicon glyphicon-export"></span> 导出数据</a>
                                <button  class="layui-btn" onclick="member_edit('编辑','<?php echo U('User/editUser');?>',800,600);return false;"><span
                                        class="glyphicon glyphicon-user"></span> 添加代理商
                                </button>
                            </div>
                        </div>
                    </form>
                    <!--用户列表-->
                    <table class="layui-table" lay-data="{width:'100%',limit:<?php echo ($rows); ?>,id:'userData'}">
                        <thead>
                        <tr>
                            <th lay-data="{field:'id',fixed: true,width:60}"></th>
                            <th lay-data="{field:'memberid', width:80, sort: true, fixed: true}">代理号</th>
                            <th lay-data="{field:'membercount', width:120}">商户数</th>
                            <th lay-data="{field:'todaytrade', width:110}">今日交易量</th>
                            <th lay-data="{field:'todaynum', width:120}">交易笔数</th>
                            <th lay-data="{field:'todayout', width:120}">今日提款</th>
                            <th lay-data="{field:'todayoutnum', width:100}">提款笔数</th>
                            <th lay-data="{field:'trade', width:100}">总交易量</th>
                            <th lay-data="{field:'preprofit', width:120}">待结算分润</th>
                            <th lay-data="{field:'profit', width:120}">可提现分润</th>
                            <th lay-data="{field:'regdatetime', width:120}">注册日期</th>
                            <!--<th lay-data="{field:'op',width:500}">操作</th>-->
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                <td><?php echo ($vo["id"]); ?></td>
                                <td><a href="<?php echo U('User/changeuser',array('userid'=>$vo['id']));?>" target="_blank"><?php echo (shanghubianhao($vo['id'])); ?></a></td>
                                <td><?php echo ((isset($vo["member_count"]) && ($vo["member_count"] !== ""))?($vo["member_count"]):0); ?></td>
                                <td><?php echo ((isset($vo["today_amount"]) && ($vo["today_amount"] !== ""))?($vo["today_amount"]):0); ?></td>
                                <td><?php echo ((isset($vo["today_count"]) && ($vo["today_count"] !== ""))?($vo["today_count"]):0); ?></td>
                                <td><?php echo ((isset($vo["today_withdraw"]) && ($vo["today_withdraw"] !== ""))?($vo["today_withdraw"]):0); ?></td>
                                <td><?php echo ((isset($vo["today_withdraw_count"]) && ($vo["today_withdraw_count"] !== ""))?($vo["today_withdraw_count"]):0); ?></td>
                                <td><?php echo ((isset($vo["total_amount"]) && ($vo["total_amount"] !== ""))?($vo["total_amount"]):0); ?></td>
                                <td><?php echo ((isset($vo["pre_settle"]) && ($vo["pre_settle"] !== ""))?($vo["pre_settle"]):0); ?></td>
                                <td><?php echo ((isset($vo["can_settle"]) && ($vo["can_settle"] !== ""))?($vo["can_settle"]):0); ?></td>
                                <td><?php echo (date("Y-m-d",$vo["regdatetime"])); ?></td>
                                <!--<td>-->
                                    <!--<div class="layui-btn-group">-->

                                        <!--<a href="<?php echo U('User/changeuser',array('userid'=>$vo['id']));?>" target="_blank"><button class="layui-btn layui-btn-small" >登录</button></a>-->

                                        <!--<button class="layui-btn layui-btn-small" onclick="member_withdrawal('提现设置',-->
                                                <!--'<?php echo U('User/userWithdrawal',['uid'=>$vo[id]]);?>')">提现</button>-->

                                      <!--&lt;!&ndash;   <button class="layui-btn layui-btn-small" onclick="member_withdrawal('交易设置',-->
                                                <!--'<?php echo U('Transaction/userConfig',['uid'=>$vo[id]]);?>')">风控</button> &ndash;&gt;-->
<!--&lt;!&ndash; -->
                                        <!--<button class="layui-btn layui-btn-small" onclick="member_edit('编辑通道','<?php echo U('User/editUserProduct',['uid'=>$vo[id]]);?>',800)"-->
                                        <!--&gt;通道</button> &ndash;&gt;-->
                                        <!---->
                                        <!--<button class="layui-btn layui-btn-small" onclick="member_rate('编辑费率','<?php echo U('User/userRateEdit',['uid'=>$vo[id]]);?>',640,480)">费率</button>-->

                                        <!--<button class="layui-btn layui-btn-small"-->
                                                <!--onclick="member_edit('编辑','<?php echo U('User/editPassword',['uid'=>$vo[id]]);?>',800,540)"-->
                                        <!--&gt;密码</button>-->

                                        <!--<button class="layui-btn layui-btn-small" onclick="member_edit('编辑','<?php echo U('User/editUser',['uid'=>$vo[id]]);?>',800,600)"-->
                                        <!--&gt;编辑</button>-->

                                        <!--<button class="layui-btn layui-btn-small" onclick="member_del(this,'<?php echo ($vo["id"]); ?>')">删除</button>-->
                                        <!---->
                                    <!--</div>-->
                                <!--</td>-->
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                    <!--用户列表-->
                    <div class="page"><?php echo ($page); ?> 
                        <div class="layui-input-inline">
                        <form class="layui-form" action="" method="get" id="pageForm" autocomplete="off">                                
                            
                            <select name="rows" style="height: 32px;" id="pageList" lay-ignore >
                                <option value="">显示条数</option>
                                <option <?php if($_GET[rows] != '' && $_GET[rows] == 15): ?>selected<?php endif; ?> value="15">15条</option>
                                <option <?php if($_GET[rows] == 30): ?>selected<?php endif; ?> value="30">30条</option>
                                <option <?php if($_GET[rows] == 50): ?>selected<?php endif; ?> value="50">50条</option>
                                <option <?php if($_GET[rows] == 80): ?>selected<?php endif; ?> value="80">80条</option>
                                <option <?php if($_GET[rows] == 100): ?>selected<?php endif; ?> value="100">100条</option>
                                <option <?php if($_GET[rows] == 1000): ?>selected<?php endif; ?> value="1000">1000条</option>
                            </select>
                           

                        </form>
                        </div> 
                    </div>
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
    layui.use(['form','table',  'laydate', 'layer'], function () {
        var form = layui.form
            ,table = layui.table
            , layer = layui.layer
            , laydate = layui.laydate;

        //日期时间范围
        laydate.render({
            elem: '#regtime'
            , type: 'datetime'
            ,theme: 'molv'
            , range: '|'
        });
        //监听表格复选框选择
        table.on('checkbox(userData)', function(obj){
            var child = $(data.elem).parents('table').find('tbody input[lay-filter="ids"]');
            child.each(function(index, item){
                item.checked = data.elem.checked;
            });
            form.render('checkbox');
        });
        //监听工具条
        table.on('tool(test1)', function(obj){
            var data = obj.data;
            if(obj.event === 'detail'){
                layer.msg('ID：'+ data.id + ' 的查看操作');
            } else if(obj.event === 'del'){
                layer.confirm('真的删除行么', function(index){
                    obj.del();
                    layer.close(index);
                });
            } else if(obj.event === 'edit'){
                layer.alert('编辑行：<br>'+ JSON.stringify(data))
            }
        });
        //全选
        form.on('checkbox(allChoose)', function (data) {
            var child = $(data.elem).parents('table').find('tbody input[lay-filter="ids"]');
            child.each(function (index, item) {
                item.checked = data.elem.checked;
            });
            form.render('checkbox');
        });

        //监听用户状态
        form.on('switch(switchStatus)', function (data) {
            var isopen = this.checked ? 1 : 0,
                uid = $(this).attr('data-uid');
            $.ajax({
                url: "<?php echo U('User/editStatus');?>",
                type: 'post',
                data: "uid=" + uid + "&isopen=" + isopen,
                success: function (res) {
                    if (res.status) {
                        layer.tips('温馨提示：开启成功', data.othis);
                    } else {
                        layer.tips('温馨提示：关闭成功', data.othis);
                    }
                }
            });
        });
    });

    //批量删除提交
    function delAll() {
        layer.confirm('确认要删除吗？', function (index) {
            //捉到所有被选中的，发异步进行删除
            layer.msg('删除成功', {icon: 1});
        });
    }

    /*用户-认证*/
    function member_auth(title, url, w, h) {
        x_admin_show(title, url, w, h);
    }

    /*用户-费率*/
    function member_rate(title, url, w, h) {
        x_admin_show(title, url, w, h);
    }

    // 用户-编辑
    function member_add(title, url, id, w, h) {
        x_admin_show(title, url, w, h);
    }

    // 用户-编辑
    function member_edit(title, url, id, w, h) {
        x_admin_show(title, url, w, h);
    }

    // 用户-提现
    function member_withdrawal(title, url, id, w, h) {
        x_admin_show(title, url, w, h);
    }
    // 用户-提现
    function member_money(title, url, id, w, h) {
        x_admin_show(title, url, w, h);
    }

    /*用户-删除*/
    function member_del(obj, id) {
        layer.confirm('确认要删除吗？', function (index) {
            $.ajax({
                url:"<?php echo U('User/delUser');?>",
                type:'post',
                data:'uid='+id,
                success:function(res){
                    if(res.status){
                        $(obj).parents("tr").remove();
                        layer.msg('已删除!',{icon:1,time:1000});
                    }
                }
            });
        });
    }

    function thawing_funds(){
        layer.confirm('温馨提示：解冻数据数量多，可能需要时间非常长，请尽量在交易量低的时间段再提交，<br><br>确认要提交吗？',function(index) {
            layer.load();
            $.ajax({
                'url':'<?php echo U("User/thawingFunds");?>',
                '':'json',
                'type':'get',
                'success':function(info){
                    console.log(info);
                    layer.closeAll('loading');
                    layer.msg(info['msg'], {icon: 1, time: 1000},function () {
                        location.replace(location.href);
                    }); 
                },
                'error':function(){

                },
            });
        });
    }

    $('#pageList').change(function(){
        $('#pageForm').submit();
    });
    $('#export').on('click',function(){
        window.location.href
            ="<?php echo U('Admin/User/exportuser',array('username'=>$_GET[username],'parentid'=>$_GET[parentid],'status'=>$_GET[status],'authorized'=>$_GET[authorized],'groupid'=>$_GET[groupid],'regdatetime'=>$_GET[regdatetime],'is_agent'=>1));?>";
    });

</script>
</body>
</html>