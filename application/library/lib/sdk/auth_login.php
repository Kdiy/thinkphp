<?php
namespace app\library\lib\sdk;
// 第三方授权登陆
class auth_login{


    /**
     * @desc 授权用户的JWT凭证
     * @param string $identityToken
     * @param string $user
     * @return bool
     */
    public function jwtApple(string $identityToken, string $user) : bool{
        
        $appleSignInPayload = ASDecoder::getAppleSignInPayload($identityToken);
        return $appleSignInPayload->verifyUser($user);

    }
    
    
    
    
    
    
    
}