# php微信支付企业付款、发送现金红包封装类，可适用于 微擎(we7)，thinkphp,原生php应用等框架


在做一个微擎项目时发现微擎没有封装好的企业付款到零钱和发送现金红包等方法函数，于是自己动手封装了一个。


## Composer安装
安装最新的版本
```bash
$ composer require phpwxpay/payment
```

```php
<?php
    use phpwxpay\Payment;
    // 创建支付通道
    $pay=new Payment($payConfig);
?>
```

## 不使用Composer
git clone 获取代码到本地
```bash
$ git clone git@github.com:zhuyl369/phpwxpay.git
```
拷贝文件Phpwxpay.php到项目目录并引入
```php
<?php
    require_once(__DIR__.DIRECTORY_SEPARATOR.'Phpwxpay.php');
    // 创建支付通道
    $pay=new phpwxpay\Payment($payConfig);
?>
```

> $payConfig (Array) 支付配置参数：

| 参数				| 类型	|必填	|  描述													|
| --------			| -----:|-----:	| :----:												|
|appid				|string	|是		|应用appid (商户号绑定的appid)							    |
|mchid				|string	|是		|微信支付商户号											|
|apikey				|string	|是		|商户支付密钥											    |
|spbill_create_ip	|string	|否		|商户平台设置的IP白名单，如果不传值，自动获取服务器ip	            |
|cert_pem			|string	|是		|商户支付证书(apiclient_cert.pem)，绝对路径				    |
|key_pem			|string	|是		|支付证书私钥（apiclient_key.pem），绝对路径			        |  


*示例代码*
```php
<?php
use phpwxpay\Payment;
$payConfig=array(
    'appid'=>'xxxxxxxxxxxxxxxxxxx',
    'mchid'=>'xxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'apikey'=>'xxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    'spbill_create_ip'=>null,
    'cert_pem'=>__DIR__.DIRECTORY_SEPARATOR.'apiclient_cert.pem',
    'key_pem'=>__DIR__.DIRECTORY_SEPARATOR.'apiclient_key.pem',
);
try{
    $pay=new Payment($payConfig);
}catch (Exception $e){
    die($e->getMessage());
}
```

## 企业付款到零钱：
```php
<?php
    $payRes=$pay->toBalance($data);
?>
```
> $data(Array) 参数：

| 参数		| 类型	|必填	|  描述					|
| --------	| -----:|-----:	| :----:				|
|openid		|string	|是		|要给付款的用户openid	|
|amount		|float	|是		|付款金额(如0.5元)		|
|desc		|string	|否		|付款备注信息			|

> 企业付款到零钱成功返回信息(Array)

|参数				|类型	|必填	|示例值								|描述													|
|--					|--		|--		|--									|--														|
|mch_appid			|string	|是		|wx8888888888888888					|商户appid												|
|mchid				|string	|是		|1234567890							|商户号													|
|device_info		|string	|是		|013467007045764					|微信支付分配的终端设备号								|
|nonce_str			|string	|是		|5K8264ILTKCH16CQ2502SI8ZNMTM67VS	|随机字符串												|
|result_code		|string	|是		|SUCCESS							|业务结果SUCCESS/FAIL									|
|partner_trade_no	|string	|是		|10000098201411111234567890			|商户订单号												|
|payment_no			|string	|是		|1007752501201407033233368018		|企业付款成功，返回的微信付款单号						|
|payment_time		|string	|是		|2015-05-19 15：26：59				|企业付款成功时间										|
|return_code		|string	|是		|SUCCESS							|返回状态码SUCCESS/FAIL（此字段是通信标识，非交易标识）	|

### 微信现金红包
```php
<?php
    $payRes=$pay->toRedpack($data);
?>
```

> $data 参数(Array)：

| 参数		| 类型	|必填	|  描述								|
| --------	| -----:|-----:	| :----:							|
|openid		|string	|是		|接收红包用户的openid				    |
|amount		|float	|是		|红包金额(如0.3元)					|
|send_name	|string	|是		|红包发送者名称(不能超过10个汉字)	        |
|wishing	|string	|是		|红包祝福语(不能超过42个汉字)		    |
|act_name	|string	|是		|活动名称(不能超过10个汉字)			    |
|send_name	|string	|是		|红包发送者名称(不能超过10个汉字)	        |
|desc		|string	|否		|红包备注信息						    |

> 微信现金红包成功返回信息(Array)


|参数		|类型	|必填	|示例值								|描述													|
|--			|--		|--		|--									|--														|
|wxappid	|string	|是		|wx8888888888888888					|商户appid												|
|mch_id		|string	|是		|1234567890							|商户号													|
|send_listid|string	|是		|100000000020150520314766074200		|红包订单的微信单号										    |
|nonce_str	|string	|是		|5K8264ILTKCH16CQ2502SI8ZNMTM67VS	|随机字符串												|
|result_code|string	|是		|SUCCESS							|业务结果SUCCESS/FAIL									    |
|mch_billno	|string	|是		|10000098201411111234567890			|商户订单号												|
|re_openid	|string	|是		|oxTWIuGaIt6gTKsQRLau2M0yL16E		|接受收红包的用户在wxappid下的openid					    |
|return_code|string	|是		|SUCCESS							|返回状态码SUCCESS/FAIL（此字段是通信标识，非交易标识）	        |

