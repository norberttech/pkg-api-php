<?php

declare (strict_types = 1);

namespace PkgClient\SDK;

use PkgClient\Authenticator\Token;

final class Session
{
    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var Token
     */
    private $token;

    /**
     * @var string
     */
    private $authType;

    /**
     * @param Token $token
     * @param string $apiUrl
     */
    public function __construct(Token $token, string $authType, string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
        $this->token = $token;
        $this->authType = $authType;
    }

    /**
     * @return Token
     */
    public function token() : Token
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function authType() : string
    {
        return $this->authType;
    }

    /**
     * @return string
     */
    public function apiUrl() : string
    {
        return 'https://' . $this->apiUrl . '/rpc';
    }
}