<?php

declare (strict_types = 1);

namespace PkgClient\Authenticator;

final class Token
{
    /**
     * @var \DateTimeImmutable
     */
    private $obtained;

    /**
     * @var string
     */
    private $value;

    /**
     * @var int
     */
    private $expires;

    /**
     * @var string
     */
    private $authType;

    /**
     * @param string $value
     * @param int $expires
     * @param string $authType
     */
    public function __construct(string $value, int $expires, string $authType)
    {
        $this->obtained = new \DateTimeImmutable();
        $this->value = $value;
        $this->expires = $expires;
        $this->authType = $authType;
    }

    /**
     * @return string
     */
    public function value() : string
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function expires() : int
    {
        return $this->expires;
    }

    /**
     * @return string
     */
    public function authType()
    {
        return $this->authType;
    }
}