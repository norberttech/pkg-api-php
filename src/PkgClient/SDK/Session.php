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
     * @var Location
     */
    private $location;

    /**
     * @param Token $token
     * @param Location $location
     * @param string $apiUrl
     */
    public function __construct(Token $token, Location $location, string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
        $this->token = $token;
        $this->location = $location;
    }

    /**
     * @return Token
     */
    public function token() : Token
    {
        return $this->token;
    }

    /**
     * @return Location
     */
    public function location() : Location
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function apiUrl() : string
    {
        return 'https://' . $this->apiUrl . '/rpc';
    }
}