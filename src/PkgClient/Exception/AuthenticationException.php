<?php

declare (strict_types = 1);

namespace PkgClient\Exception;

final class AuthenticationException extends Exception
{
    public static function rpcServerNotAvailable() : AuthenticationException
    {
        return new static("RPC Server is not available, please try again later");
    }
}