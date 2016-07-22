<?php

declare (strict_types = 1);

namespace PkgClient\Model;

final class Player
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var int
     */
    private $iteStorage;

    /**
     * @var int
     */
    private $pokeStorage;

    /**
     * @param string $username
     * @param int $iteStorage
     * @param int $pokeStorage
     */
    public function __construct(string $username, int $iteStorage, int $pokeStorage)
    {
        $this->username = $username;
        $this->iteStorage = $iteStorage;
        $this->pokeStorage = $pokeStorage;
    }

    /**
     * @return string
     */
    public function username() : string
    {
        return $this->username;
    }

    /**
     * @return int
     */
    public function iteStorage() : int
    {
        return $this->iteStorage;
    }

    /**
     * @return int
     */
    public function pokeStorage() : int
    {
        return $this->pokeStorage;
    }
}