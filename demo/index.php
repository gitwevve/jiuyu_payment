<?php
$pay_orderid = 'E' . date("YmdHis") . rand(100000, 999999); //订单号
$pay_amount  = "0.01"; //交易金额

//$lists = M('banks')->where(['status' => 1])->select(['code']);
//var_dump($lists);
//exit();
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
                        <input type="hidden" name="orderid" value="<?php echo $pay_orderid; ?>">
                        <input type="hidden" name="amount" value="<?php echo $pay_amount; ?>">
                        <div class="two-step">
                            <p><strong>请您及时付款，以便订单尽快处理！</strong>请您在提交订单后<span>24小时</span>内完成支付，否则订单会自动取消。</p>
                            <ul class="pay-infor">
                                <li>商品名称：测试应用-支付功能体验(非商品消费)</li>
                                <li>支付金额：<strong><?php echo $pay_amount; ?> <span>元</span></strong></li>
                                <li>订单编号：<span><?php echo $pay_orderid; ?></span></li>
                            </ul>
                            <h5>选择支付方式：</h5>
                            <ul class="pay-label">
                                <!--<li>
                                    <input value="903" checked="checked" name="channel" id="zfb" type="radio">
                                    <label for="zfb"><img src="zhifubao.png" alt="支付宝"><span>支付宝扫码</span></label>
                                </li>-->
                                <li>
                                    <input value="902" name="channel" id="wx" type="radio">
                                    <label for="wx"><img src="weixin.png" alt="微信支付"><span>微信扫码</span></label>
                                </li>

                                <!-- <li>
                                    <input value="904"  name="channel" id="zfb1" type="radio">
                                    <label for="zfb1"><img src="zhifubao.png" alt="支付宝"><span>支付宝手机</span></label>
                                </li> -->
                                <li>
                                    <input value="907" name="channel" id="bd" type="radio">
                                    <label for="bd"><img src="yinlian.png" alt="银联支付"><span>银联支付</span></label>
                                </li>
                                <li>
                                    <input value="908" name="channel" id="qq" type="radio">
                                    <label for="qq"><img src="weixin.png" alt="QQ支付"><span>QQ扫码</span></label>
                                </li>
                            </ul>
                            <div id="bank_map" style="display: none;">
                                <h5>选择支付银行：</h5>
                                <div class="div-bank-list">
                                    <ul class="pay-label" name="bankCode">
                                        <li>
                                            <input value="BOC" checked="checked" name="bankCode" id="BOC" type="radio">
                                            <label for="BOC"><img src="../Public/bank/BOC.png" alt="BOC"><span></span></label>
                                        </li><li>
                                            <input value="CCB" checked="checked" name="bankCode" id="CCB" type="radio">
                                            <label for="CCB"><img src="../Public/bank/CCB.png" alt="CCB"><span></span></label>
                                        </li><li>
                                            <input value="ABC" checked="checked" name="bankCode" id="ABC" type="radio">
                                            <label for="ABC"><img src="../Public/bank/ABC.png" alt="ABC"><span></span></label>
                                        </li><li>
                                            <input value="ICBC" checked="checked" name="bankCode" id="ICBC" type="radio">
                                            <label for="ICBC"><img src="../Public/bank/ICBC.png" alt="ICBC"><span></span></label>
                                        </li><li>
                                            <input value="SPDB" checked="checked" name="bankCode" id="SPDB" type="radio">
                                            <label for="SPDB"><img src="../Public/bank/SPDB.png" alt="SPDB"><span></span></label>
                                        </li><li>
                                            <input value="CEB" checked="checked" name="bankCode" id="CEB" type="radio">
                                            <label for="CEB"><img src="../Public/bank/CEB.png" alt="CEB"><span></span></label>
                                        </li><li>
                                            <input value="SPAB" checked="checked" name="bankCode" id="SPAB" type="radio">
                                            <label for="SPAB"><img src="../Public/bank/SPAB.png" alt="SPAB"><span></span></label>
                                        </li><li>
                                            <input value="CIB" checked="checked" name="bankCode" id="CIB" type="radio">
                                            <label for="CIB"><img src="../Public/bank/CIB.png" alt="CIB"><span></span></label>
                                        </li><li>
                                            <input value="PSBC" checked="checked" name="bankCode" id="PSBC" type="radio">
                                            <label for="PSBC"><img src="../Public/bank/PSBC.png" alt="PSBC"><span></span></label>
                                        </li><li>
                                            <input value="CITIC" checked="checked" name="bankCode" id="CITIC" type="radio">
                                            <label for="CITIC"><img src="../Public/bank/CITIC.png" alt="CITIC"><span></span></label>
                                        </li><li>
                                            <input value="HXB" checked="checked" name="bankCode" id="HXB" type="radio">
                                            <label for="HXB"><img src="../Public/bank/HXB.png" alt="HXB"><span></span></label>
                                        </li><li>
                                            <input value="CMB" checked="checked" name="bankCode" id="CMB" type="radio">
                                            <label for="CMB"><img src="../Public/bank/CMB.png" alt="CMB"><span></span></label>
                                        </li><li>
                                            <input value="GDB" checked="checked" name="bankCode" id="GDB" type="radio">
                                            <label for="GDB"><img src="../Public/bank/GDB.png" alt="GDB"><span></span></label>
                                        </li><li>
                                            <input value="BJB" checked="checked" name="bankCode" id="BJB" type="radio">
                                            <label for="BJB"><img src="../Public/bank/BJB.png" alt="BJB"><span></span></label>
                                        </li><li>
                                            <input value="SHB" checked="checked" name="bankCode" id="SHB" type="radio">
                                            <label for="SHB"><img src="../Public/bank/SHB.png" alt="SHB"><span></span></label>
                                        </li><li>
                                            <input value="CMBC" checked="checked" name="bankCode" id="CMBC" type="radio">
                                            <label for="CMBC"><img src="../Public/bank/CMBC.png" alt="CMBC"><span></span></label>
                                        </li><li>
                                            <input value="COMM" checked="checked" name="bankCode" id="COMM" type="radio">
                                            <label for="COMM"><img src="../Public/bank/COMM.png" alt="COMM"><span></span></label>
                                        </li>
                                    </ul>
                                </div>
                            </div>



                            <div class="btns"> <button type="submit" class="pcdemo-btn sbpay-btn" >立即支付</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../Public/Front/js/jquery.min.js"></script>
<script>
    $("input[name='channel']").click(function () {
        var channel = $(this).val();
        if (channel == '907') {
            $('#bank_map').show();
        } else {
            $("#bank_map").hide();
        }
    });
</script>
</body>
</html>
