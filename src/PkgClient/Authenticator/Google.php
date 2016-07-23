<?php

declare (strict_types = 1);

namespace PkgClient\Authenticator;

use GPSOAuthPHP\GPSOAuthPHP;
use PkgClient\Authenticator;

final class Google implements Authenticator
{
    const AUTH_TYPE = 'google';
    const ANDROID_ID = '9774d56d682e549c';
    const ANDROID_SERVICE = 'audience:server:client_id:848232511240-7so421jotr2609rmqakceuu1luuq0ptb.apps.googleusercontent.com';
    const APP_ID = 'com.nianticlabs.pokemongo';
    const SIG = '321187995bc7cdc2b5fc91b11a96e2baa8602c62';

    public function obtainToken(string $username, string $password) : Token
    {
        $gpsoauth = new GPSOAuthPHP();

        if ($masterToken = $gpsoauth->performMasterLogin($username, $password, self::ANDROID_ID)) {
            if ($auth = $gpsoauth->performOAuth($username, $masterToken['Token'], self::ANDROID_ID, self::ANDROID_SERVICE, self::APP_ID, self::SIG)) {

                return new Token($auth['Auth'], (int) $auth['Expiry'], self::AUTH_TYPE);
            }
        }
    }
}