<?php
namespace app\library\lib\sdk;
// 生成RSA签名

class RSA2{
    public static function endsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function encoding($array, $to_encoding = 'utf-8', $from_encoding = 'gb2312')
    {
        $encoded = [];
        
        foreach ($array as $key => $value) {
            $encoded[$key] = is_array($value) ? self::encoding($value, $to_encoding, $from_encoding) :
            mb_convert_encoding($value, $to_encoding, $from_encoding);
        }
        
        return $encoded;
    }
    
    public static function getSignContent(array $data, $verify = false): string
    {
        $data = self::encoding($data, $data['charset'] ?? 'gb2312', 'utf-8');
        
        ksort($data);
        
        $stringToBeSigned = '';
        foreach ($data as $k => $v) {
            if ($verify && $k != 'sign' && $k != 'sign_type') {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
            if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
        }
        
        
        return trim($stringToBeSigned, '&');
    }
    public static function generateSign(array $params, string $privateKey){
        
        if (is_null($privateKey)) {
            throw new \Exception('Missing Alipay Config -- [private_key]');
        }
        
        if (self::endsWith($privateKey, '.pem')) {
            $privateKey = openssl_pkey_get_private($privateKey);
        } else {
            $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n".
                wordwrap($privateKey, 64, "\n", true).
                "\n-----END RSA PRIVATE KEY-----";
        }
        
        openssl_sign(self::getSignContent($params), $sign, $privateKey, OPENSSL_ALGO_SHA256);
        
        $sign = base64_encode($sign);
        
        
        if (is_resource($privateKey)) {
            openssl_free_key($privateKey);
        }
        
        return $sign;
    }
    public static function createSign($params,$privateKey)
    {
        return self::generateSign($params,$privateKey);
    }
}