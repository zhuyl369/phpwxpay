# php微信支付企业付款、发送现金红包封装类，可适用于 微擎(we7)，thinkphp,原生php应用等框架


>在做一个微擎项目时发现微擎没有封装好的企业付款到零钱和发送现金红包等方法函数，于是自己动手封装了一个。


#### 使用说明

引入类
```
    <?php
        require_once(MODULE_ROOT.'/Wxpay.php');
    ?>
```
### 企业付款到零钱：
```
    <?php
        $pay=new Wxpay\Payment($payConfig);
        $payRes=$pay->toBalance($data);
    ?>
```
Array $payConfig 配置参数：


| 参数        | 类型   |必填|  描述  |
| --------   | -----:  |-----: | :----:  |
|appid|string|是|应用appid (商户号绑定的appid)|
|mchid|string|是|微信支付商户号|
|apikey|string|是|商户支付密钥|
|spbill_create_ip|string|否|商户平台设置的IP白名单，如果不传值，自动获取服务器ip  |
|cert_pem|string|是|商户支付证书(apiclient_cert.pem)，绝对路径|
|key_pem|string|是|支付证书私钥（apiclient_key.pem），绝对路径|

Array $data 参数：

| 参数        | 类型   |必填|  描述  |
| --------   | -----:  |-----: | :----:  |
|openid|string|是|要给付款的用户openid|
|money|float|是|付款金额(如0.5元)|
|desc|string|否|付款备注信息|

### 微信现金红包
```
<?php
		$pay=new Wxpay\Payment($payConfig);
		$payRes=$pay->sendRedPack($data);
?>
```
Array $payConfig 配置参数：


| 参数        | 类型   |必填|  描述  |
| --------   | -----:  |-----: | :----:  |
|appid|string|是|应用appid (商户号绑定的appid)|
|mchid|string|是|微信支付商户号|
|apikey|string|是|商户支付密钥|
|spbill_create_ip|string|否|商户平台设置的IP白名单，如果不传值，自动获取服务器ip  |
|cert_pem|string|是|商户支付证书(apiclient_cert.pem)，绝对路径|
|key_pem|string|是|支付证书私钥（apiclient_key.pem），绝对路径|
|send_name|string|是|红包发送者名称(不能超过10个汉字)|
|wishing|string|是|红包祝福语(不能超过42个汉字)|
|act_name|string|是|活动名称(不能超过10个汉字)|
|send_name|string|是|红包发送者名称(不能超过10个汉字)|

Array $data 参数：

| 参数        | 类型   |必填|  描述  |
| --------   | -----:  |-----: | :----:  |
|openid|string|是|接收红包用户的openid|
|money|float|是|红包金额(如0.3元)|
|desc|string|否|红包备注信息|
