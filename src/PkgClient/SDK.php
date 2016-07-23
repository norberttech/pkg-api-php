<?php

declare (strict_types = 1);

namespace PkgClient;

use GuzzleHttp\Client;
use PkgClient\Authenticator\Google;
use PkgClient\Authenticator\PokemonClub;
use PkgClient\Exception\AuthenticationException;
use PkgClient\Model\Player;
use PkgClient\SDK\Location;
use PkgClient\SDK\Session;
use Psr\Log\LoggerInterface;

final class SDK
{
    const AUTH_TYPE_POKEMON_CLUB = 1;
    const AUTH_TYPE_GOOGLE = 2;

    /**
     * @var Authenticator[];
     */
    private $authenticators;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var RPCApi
     */
    private $rpcApi;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        $this->httpClient = new Client(['cookies' => $jar]);
        $this->rpcApi = new RPCApi($this->httpClient);

        $this->authenticators = [
            self::AUTH_TYPE_POKEMON_CLUB => new PokemonClub($this->httpClient),
            self::AUTH_TYPE_GOOGLE => new Google()
        ];
        $this->logger = $logger;
    }

    /**
     * @param string $username
     * @param string $password
     * @param Location $location
     * @param int $authTypeCode
     * @return Session
     *
     * @throws AuthenticationException
     */
    public function login(string $username, string $password, Location $location, int $authTypeCode = self::AUTH_TYPE_POKEMON_CLUB) : Session
    {
        $token = $this->authenticators[$authTypeCode]->obtainToken($username, $password);

        $rpcResponse = $this->rpcApi->getApiUrl($token, $location);

        if (!$rpcResponse->hasApiUrl()) {
            throw AuthenticationException::rpcServerNotAvailable();
        }

        return new Session($token, $location, $rpcResponse->getApiUrl());
    }

    /**
     * @param Session $session
     * @return Player
     */
    public function player(Session $session) : Player
    {
        $rpcResponse = $this->rpcApi->getPlayer($session);

        $player = \PkgClient\Protocol\Message\Player::fromStream($rpcResponse->getResponsesList()[0]);

        return new Player(
            $player->getProfile()->getUsername(),
            $player->getProfile()->getItemStorage(),
            $player->getProfile()->getPokeStorage()
        );
    }
}