<?php
/* *
 * 功能：代付调试入口页面
 */
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>代付申请</title>
	<link rel="stylesheet" type="text/css" href="df.css">
	<script type="text/javascript" src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
</head>
<body>
   <div class="container">
	   <div class="header">
		   <h3>代付申请</h3>
	   </div>

	<div class="main">
		 <form  method="post" action="dodf.php">
			<ul>

				<li>
					<label>金额</label>
					<input type="text" name="money" value="100" />
				</li>
<!--				<li>-->
<!--					<label>开户行</label>-->
<!--					<input type="text" name="bankname" value="" />-->
<!--				</li>-->
                <li>
                    <label>银行编码</label>
                    <select name="bankid">
                        <option value="BOB">北京银行</option>
                        <option value="BEA">东亚银行</option>
                        <option value="ICBC">中国工商银行</option>
                        <option value="CEB">中国光大银行</option>
                        <option value="GDB">广发银行</option>
                        <option value="HXB">华夏银行</option>
                        <option value="CCB">中国建设银行</option>
                        <option value="BCM">交通银行</option>
                        <option value="CMSB">中国民生银行</option>
                        <option value="NJCB">南京银行</option>
                        <option value="NBCB">宁波银行</option>
                        <option value="ABC">中国农业银行</option>
                        <option value="PAB">平安银行</option>
                        <option value="BOS">上海银行</option>
                        <option value="SPDB">上海浦东发展银行</option>
                        <option value="SDB">深圳发展银行</option>
                        <option value="CIB">兴业银行</option>
                        <option value="PSBC">中国邮政储蓄银行</option>
                        <option value="CMBC">招商银行</option>
                        <option value="CZB">浙商银行</option>
                        <option value="BOC">中国银行</option>
                        <option value="CNCB">中信银行</option>

                    </select>
                </li>
				<li>
					<label>支行名称</label>
					<input type="text" name="subbranch" value="" />
				</li>
				<li>
					<label>开户名</label>
					<input type="text" name="accountname" value="" />
				</li>
				<li>
					<label>银行卡号</label>
					<input type="text" name="cardnumber" value="" />
				</li>
				<li>
					<label>省</label>
					<input type="text" name="province" value=""  />
				</li>
				<li>
					<label>市</label>
					<input type="text" name="city" value=""  />
				</li>
				<li>
					<label>联行号</label>
					<input type="text" name="extends[lhh]" value=""  />
				</li>
                <li>
                    <label>商户id</label>
                    <input type="text" name="mchid" value="10002" />
                </li>
                <li>
                    <label>代付密钥</label>
                    <input type="text" name="df_key" value="t4ig5acnpx4fet4zapshjacjd9o4bhbi" />
                </li>
				<li style="margin-top: 50px">
					<label></label>
					<button type="submit">提交</button>
				</li>
             </ul>
		</form>
	  </div>
    </div>
  </body>
</html>
