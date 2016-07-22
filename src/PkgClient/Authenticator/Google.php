<?php

declare (strict_types = 1);

namespace PkgClient\Authenticator;

use GPSOAuthPHP\GPSOAuthPHP;
use PkgClient\Authenticator;

final class Google implements Authenticator
{
    public function obtainToken(string $username, string $password) : Token
    {
        $androidId = '9774d56d682e549c';
        $service = 'audience:server:client_id:848232511240-7so421jotr2609rmqakceuu1luuq0ptb.apps.googleusercontent.com';
        $app = 'com.nianticlabs.pokemongo';
        $client_sig = '321187995bc7cdc2b5fc91b11a96e2baa8602c62';

        $gpsoauth = new GPSOAuthPHP();

        if ($masterToken = $gpsoauth->performMasterLogin($username, $password, $androidId)) {
            if ($auth = $gpsoauth->performOAuth($username, $masterToken['Token'], $androidId, $service, $app, $client_sig)) {

                return new Token($auth['Auth'], (int) $auth['Expiry']);
            }
        }
    }
}