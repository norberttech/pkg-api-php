<?php

declare (strict_types = 1);

namespace PkgClient;

use PkgClient\Authenticator\Token;

interface Authenticator
{
    /**
     * @param string $username
     * @param string $password
     *
     * @return Token
     */
    public function obtainToken(string $username, string $password) : Token;
}