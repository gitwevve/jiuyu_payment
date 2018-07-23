<?php
$pay_orderid = 'E'.date("YmdHis").rand(100000,999999);    //订单号

?>
<!DOCTYPE html>
<html lang=zh>
<head>
    <meta charset=UTF-8>
    <title>聚合收银台</title>
    <link href="cashier.css" rel="stylesheet">
</head>
<body>
<div class="tastesdk-box">
    <div class="header clearfix">
        <div class="title">
            <p class="logo">
                <span>收银台</span>
            </p>
            <div class="right">
                <div class="clearfix">
                    <ul class="clearfix">

                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="main">
        <div class="typedemo">
            <div class="demo-pc">
                <div class="pay-jd">
                    <form action="index1.php" method="post" autocomplete="off">
                        <input type="hidden" name="orderid" value="<?php echo $pay_orderid;?>">
                        
                        <div class="two-step">
                            <p><strong>请您及时付款，以便订单尽快处理！</strong>请您在提交订单后<span>24小时</span>内完成支付，否则订单会自动取消。</p>
                            <ul class="pay-infor">
                                <li>商品名称：测试应用-支付功能体验(非商品消费)</li>
                                <li>支付金额：<strong><input type="" name="amount" value="1.00"> <span>元</span></strong></li>
                                <li>订单编号：<span><?php echo $pay_orderid;?></span></li>
                            </ul>
                            <h5>选择支付方式：</h5>
                            <ul class="pay-label">

                                <li>
                                    <input value="905" name="channel" id="QQH5" type="radio">
                                    <label for="QQH5"><img src="weixin.png" alt="QQH5"><span>QQH5</span></label>
                                </li>
                                <li>
                                    <input value="901" name="channel" id="wx1" type="radio">
                                    <label for="wx1"><img src="weixin.png" alt="微信支付"><span>微信H5</span></label>
                                </li>
                                <li>
                                    <input value="902" name="channel" id="wx" type="radio">
                                    <label for="wx"><img src="weixin.png" alt="微信支付"><span>微信扫码</span></label>
                                </li>
                                <li>
                                    <input value="903" checked="checked" name="channel" id="zfb" type="radio">
                                    <label for="zfb"><img src="zhifubao.png" alt="支付宝"><span>支付宝扫码</span></label>
                                </li>
                                <li>
                                    <input value="912" checked="checked" name="channel" id="ymf" type="radio">
                                    <label for="ymf"><img src="zhifubao.png" alt="一码付"><span>一码付</span></label>
                                </li>
                                <li>
                                    <input value="904"  name="channel" id="zfb1" type="radio">
                                    <label for="zfb1"><img src="zhifubao.png" alt="支付宝"><span>支付宝手机</span></label>
                                </li>
                                <li>
                                    <input value="907" name="channel" id="bd1" type="radio">
                                    <label for="bd1"><img src="yinlian.png" alt="银联支付"><span>银联支付</span></label>
                                </li>

                                <li>
                                    <input value="908" name="channel" id="bd2" type="radio">
                                    <label for="bd2"><img src="weixin.png" alt="qq线上扫码"><span>qq线上扫码</span></label>
                                </li>

                                <li>
                                    <input value="910" name="channel" id="bd3" type="radio">
                                    <label for="bd3"><img src="weixin.png" alt="京东支付"><span>京东支付</span></label>
                                </li>


                                <li>
                                    <input value="911" name="channel" id="bd4" type="radio">
                                    <label for="bd4"><img src="weixin.png" alt="快捷支付"><span>快捷支付</span></label>
                                </li>



                            </ul>
                            <div class="btns"> <button type="submit" class="pcdemo-btn sbpay-btn" >立即支付</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
