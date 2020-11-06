<?php

namespace Wxpay;

class Payment
{

    protected $appid;                           //申请商户号的appid或商户号绑定的appid
    protected $mchid;                               //微信支付分配的商户号
    protected $apikey;                              //商户支付密钥
    protected $check_name = 'NO_CHECK';             //校验用户姓名选项	NO_CHECK：不校验真实姓名	FORCE_CHECK：强校验真实姓名
    protected $re_user_name = '';                   //收款用户真实姓名。如果check_name设置为FORCE_CHECK，则必填用户真实姓名,如需电子回单，需要传入收款用户姓名
    protected $spbill_create_ip = false;            //Ip地址  该IP同在商户平台设置的IP白名单中的IP没有关联，该IP可传用户端或者服务端的IP。
    protected $partner_trade_no = false;            //商户订单号,需保持唯一性(只能是字母或者数字，不能包含有其它字符)
    protected $amount;                              //企业付款或红包金额，单位为分
    protected $desc;                                //企业付款备注，必填。注意：备注中的敏感词会被转成字符*
    protected $cert_pem;                            //商户支付证书.从商户平台上下载支付证书, 解压并取得其中的 apiclient_cert.pem，
    protected $key_pem;                             //支付证书私钥.从商户平台上下载支付证书, 解压并取得其中的 apiclient_key.pem

    protected $send_name;                           //商户名称(红包发送者名称)
    protected $wishing;                             //红包祝福语
    protected $act_name;                            //活动名称
    private   $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
    private   $sendredpackurl = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';

    public function __construct($config)
    {
        $this->appid                	= isset($config['appid']) ? $config['appid'] : die('appid 不能为空');
        $this->mchid                    = isset($config['mchid']) ? $config['mchid'] : die('mchid 不能为空');
        $this->apikey                   = isset($config['apikey']) ? $config['apikey'] : die('apikey 不能为空');
        $this->spbill_create_ip         = isset($config['spbill_create_ip']) ? $config['spbill_create_ip'] : $this->getips();
        $this->partner_trade_no         = isset($config['partner_trade_no']) ? $config['partner_trade_no'] : date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $this->re_user_name             = isset($config['re_user_name']) ? $config['re_user_name'] : '';
        $this->desc                     = isset($config['desc']) ? $config['desc'] : '';

        $this->cert_pem                 = isset($config['cert_pem']) ? $config['cert_pem'] : die('cert_pem支付证书路径不能为空');
        $this->key_pem                  = isset($config['key_pem']) ? $config['key_pem'] : die('cert_pem支付证书密钥路径不能为空');

        $this->send_name                = isset($config['send_name']) ? $config['send_name'] : '';
        $this->wishing                  = isset($config['wishing']) ? $config['wishing'] : '';
        $this->act_name                 = isset($config['act_name']) ? $config['act_name'] : '';
    }
	/* 
	 *		企业付款到用户零钱
	*/
    public function toBalance($data)
    {

        $this->openid = $data['openid'];
        $this->amount = $data['amount'];

        $resp = $this->curl_post_ssl($this->url, $this->signToXml());

        $content = simplexml_load_string($resp, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (strval($content->return_code) == 'FAIL') {
            die($content->return_msg);
        }
        if (strval($content->result_code) == 'FAIL') {
            print_r(array('err_code' => strval($content->err_code), 'err_code_des' => strval($content->err_code_des)));
            exit;
        }
        $rdata = array(
            'mch_appid' => strval($content->appid),
            'mchid' => strval($content->mchid),
            'device_info' => strval($content->device_info),
            'nonce_str' => strval($content->nonce_str),
            'result_code' => strval($content->result_code),
            'partner_trade_no' => strval($content->partner_trade_no),
            'payment_no' => strval($content->payment_no),
            'payment_time' => strval($content->payment_time),
            'return_code' => strval($content->return_code),
        );
        return $rdata;
    }
	/* 
	 *		发送现金红包
	*/
    public function sendRedPack($data)
    {
        $this->openid = $data['openid'];
        $this->amount = $data['amount'];
        $resp = $this->curl_post_ssl($this->sendredpackurl, $this->signToXmlForRedPack());
        $content = simplexml_load_string($resp, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($content->return_code != 'SUCCESS') {
            die($content->return_msg);
        }
        if ($content->result_code != 'SUCCESS') {
            print_r(array('err_code' => strval($content->err_code), 'err_code_des' => strval($content->err_code_des)));
            exit;
        }
        $rdata = array(
            'mch_appid' => strval($content->appid),
            'mchid' => strval($content->mchid),
            'device_info' => strval($content->device_info),
            'nonce_str' => strval($content->nonce_str),
            'result_code' => strval($content->result_code),
            'partner_trade_no' => strval($content->partner_trade_no),
            'payment_no' => strval($content->payment_no),
            'payment_time' => strval($content->payment_time),
            'return_code' => strval($content->return_code),
        );
        return $rdata;
    }

    private function curl_post_ssl($url, $xmldata, $second = 30, $aHeader = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, $this->cert_pem);
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, $this->key_pem);
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);
        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }

    private function signToXmlForRedPack()
    {
        $pars = array(
            'mch_id' => $this->mchid,
            'wxappid' => $this->appid,
            'nonce_str' => md5(time()),
            'mch_billno' => $this->mchid . date('Ymd') . sprintf('%d', time()),
            're_openid' => $this->openid,
            'total_amount' => floatval($this->amount) * 100,
            'total_num' => 1,
            'wishing' => $this->wishing,
            'remark' => $this->desc,
            'client_ip' => $this->spbill_create_ip,
            'send_name' => $this->send_name,
            'act_name' => $this->act_name,
            'scene_id' => 'PRODUCT_2',
        );
        ksort($pars, SORT_STRING);
        $string1 = '';
        foreach ($pars as $k => $v) {
            $string1 .= "{$k}={$v}&";
        }
        $string1 .= "key={$this->apikey}";
        $pars['sign'] = strtoupper(md5($string1));
        $wget = $this->ArrToXml($pars);
        file_put_contents(MODULE_ROOT . '/DEBUG.TXT', var_export($wget, true));
        return $wget;
    }

    private function ArrToXml($arr)
    {
        if (!is_array($arr) || count($arr) == 0) return '';
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
    private function signToXml()
    {
        $pars = array(
            'mch_appid' => $this->appid,
            'mchid' => $this->mchid,
            'nonce_str' => md5(time()),
            'partner_trade_no' => $this->partner_trade_no,
            'openid' => $this->openid,
            'check_name' => $this->check_name,
            'amount' => floatval($this->amount) * 100,
            'desc' => $this->desc,
            'spbill_create_ip' => $this->spbill_create_ip
        );
        ksort($pars, SORT_STRING);
        $string1 = '';
        foreach ($pars as $k => $v) {
            $string1 .= "{$k}={$v}&";
        }
        $string1 .= "key={$this->apikey}";
        $pars['sign'] = strtoupper(md5($string1));
        $wget = $this->ArrToXml($pars);
        return $wget;
    }
	private function getips()
    {
        $ip = '';
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $ip = $_SERVER['HTTP_CDN_SRC_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }
}

?>