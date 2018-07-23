<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
            <meta content="width=320, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
                <meta content="telephone=no" name="format-detection">
                    <title>
                        银联网银支付
                    </title>
                    <link href="Public/bank/common.css" rel="stylesheet">
                        <style type="text/css">
                            .div-bank-list
{
	width:800px;
}

.pay-label li{
	width:190px;
	margin-right: 0px;
}
.pay-label li label{
	height: 50px;
	width: 180px;
}
.pay-label li input{
	height: 1px;
}
span{
	color: #999999;
	font-weight:bold;
}
select{
	width: 120px;
	height: 30px;
	border-radius:5px;
	font-size: 16px;
	padding: 0 0 0 16px;
	font-weight:bold;
}
                        </style>
                    </link>
                </meta>
            </meta>
        </meta>
    </head>
    <body>
        <div class="header">
            <div class="header-main">
                <img height="40px" src="Public/bank/bank.jpg">
                </img>
            </div>
        </div>
        <div class="zhezhao-bg">
        </div>
        <div class="content">
            <div class="top-content">
                <p class="float-l" style="width:250px">
                    订单号：<?php echo ($orderid); ?>
                </p>
                <p class="float-l">
                    商品名：<?php echo ($body); ?>
                </p>
                <p class="float-r" style="margin-top:-10px">
                    订单金额：
                    <span>
                        <?php echo ($money); ?>
                    </span>
                    元
                </p>
            </div>
            <div class="float-clear">
            </div>
            <div class="main-content">
                <div class="main-title">
                    网银快捷支付
                </div>
                <div class="pay-tab">
                    <form action="<?php echo ($rpayUrl); ?>" id="payOrder" method="post">
                        <div class="choose-bank" style="opacity: 1;">
                            <p class="choose-bank-title">
                                <span>
                                </span>
                                请选择支付银行
                            </p>
                            <div class="div-bank-list">
                                <ul class="pay-label" name="bankCode">
                                    <?php if(is_array($bankArray)): foreach($bankArray as $key=>$item): ?><li>
                                            <input checked="checked" id="<?php echo ($item); ?>" name="bankCode" type="radio" value="<?php echo ($item); ?>">
                                                <label for="<?php echo ($item); ?>">
                                                    <span style="background:url(/Public/bank/<?php echo ($key); ?>.png) center center;width:162px; height:47px;display:inline-block">
                                                    </span>
                                                </label>
                                            </input>
                                        </li><?php endforeach; endif; ?>
                                </ul>
                            </div>
                            <div class="disable-screen" style="display:none">
                            </div>
                        </div>
                        <p id="B2BBANKTIPS" style="color:red;display:none">
                            注意：选择企业网银，部分银行需安装网银环境并插入UKEY，才能跳转。
                        </p>
                        <div style="display:block; height:220px">
                            <div class="pay-banknumber">
                                <input name="encryp" type="hidden" value="<?php echo ($encryp); ?>">
                                    <input name="url" type="hidden" value="<?php echo ($url); ?>">
                                        <a class="bcn-btn float-l" href="javascript:" id="btnPay" type="submit" value="确认">
                                            确定
                                        </a>
                                    </input>
                                </input>
                            </div>
                            <div id="error" style="margin-top:40px;position:absolute;display:none;color:red;">
                                错误信息提示
                            </div>
                            <div class="float-clear">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
<div class="bottom-content">
    <p class="bottom-content-title">
        常见问题
    </p>
    <ul class="ul-question">
        <li>
            <p class="q-text">
                如何使用快捷支付进行付款？
            </p>
            <p class="a-text">
                首次选择快捷支付时，需要填写您办理银行卡的身份信息和预留手机号码，信息通过验证后，通过您收到的短信验证码进行开通和支付。开通成功后
下次可凭支付密码和短信验证码进行支付。
            </p>
        </li>
        <li>
            <p class="q-text">
                手机已更换，怎么更新手机号码？
            </p>
            <p class="a-text">
                修改快捷支付银行卡预留手机号时，您需要首先在银行变更手机号码，再重新添加该卡。
下次可凭支付密码和短信验证码进行支付。
            </p>
        </li>
        <li>
            <p class="q-text">
                支付时收不到验证码怎么办？
            </p>
            <p class="a-text">
                请您检查一下是否常用手机，网络是否正常，如因网络拥堵造成短信延迟，可尝试重启手机。
            </p>
        </li>
    </ul>
</div>
<script src="Public/Front/js/jquery.min.js">
</script>
<script src="Public/weui/weui.min.js">
</script>
<script>
    var isbtn=true;
		$("#btnPay").on("click",function(){


		$('#payOrder').submit();
		});
</script>
<script type="text/javascript">
</script>