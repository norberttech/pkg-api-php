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
     * @param string $value
     * @param int $expires
     */
    public function __construct(string $value, int $expires)
    {
        $this->obtained = new \DateTimeImmutable();
        $this->value = $value;
        $this->expires = $expires;
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
}