<?php

declare (strict_types = 1);

namespace PkgClient;

use GuzzleHttp\Client;
use PkgClient\Authenticator\Google;
use PkgClient\Authenticator\PokemonClub;
use PkgClient\Exception\AuthenticationException;
use PkgClient\Model\Player;
use PkgClient\Protocol\Communication\Request;
use PkgClient\Protocol\Communication\Response;
use PkgClient\Protocol\Enum\RequestMethod;
use PkgClient\Protocol\Enum\RpcDirection;
use PkgClient\SDK\Session;
use Protobuf\MessageCollection;
use Protobuf\Stream;
use Psr\Log\LoggerInterface;

final class SDK
{
    const AUTH_TYPE_POKEMON_CLUB = 1;
    const AUTH_TYPE_GOOGLE = 2;
    const BASE_API_URL = 'https://pgorelease.nianticlabs.com/plfe/rpc';
    const HTTP_USER_AGENT = 'Niantic App';

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
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        $this->httpClient = new Client(['cookies' => $jar]);

        $this->authenticators = [

            self::AUTH_TYPE_POKEMON_CLUB => new PokemonClub($this->httpClient),
            self::AUTH_TYPE_GOOGLE => new Google()
        ];
        $this->logger = $logger;
    }

    public function login(string $username, string $password, int $authTypeCode = self::AUTH_TYPE_POKEMON_CLUB) : Session
    {
        $token = $this->authenticators[$authTypeCode]->obtainToken($username, $password);

        $authType = ($authTypeCode === self::AUTH_TYPE_POKEMON_CLUB) ? 'ptc' : 'google';

        $rpcRequest = Request::fromArray([
            'auth' => Request\AuthInfo::fromArray([
                'token' => Request\AuthInfo\JWT::fromArray([
                    'contents' => $token->value(),
                    'unknown13' => 59
                ]),
                'provider' => $authType
            ]),
            'direction' => RpcDirection::REQUEST(),
            'rpcId' => 8145806132888207461,
            'latitude' => '50.0773484',
            'longitude' => '19.9093808',
            'altitude' => '0',
            'unknown6' => null,
            'unknown12' => 989,
            'requests' => new MessageCollection([
                Request\Requests::fromArray(['type' => RequestMethod::GET_PLAYER()]),
                Request\Requests::fromArray(['type' => RequestMethod::GET_HATCHED_EGGS()]),
                Request\Requests::fromArray(['type' => RequestMethod::GET_INVENTORY()]),
                Request\Requests::fromArray(['type' => RequestMethod::CHECK_AWARDED_BADGES()]),
                Request\Requests::fromArray(['type' => RequestMethod::DOWNLOAD_SETTINGS()])
            ])
        ]);

        $httpResponse = $this->httpClient->post(self::BASE_API_URL,[
            'body' => (string) $rpcRequest->toStream(),
            'headers' => [
                'User-Agent' => self::HTTP_USER_AGENT
            ]
        ]);

        $response = Response::fromStream(Stream::wrap((string) $httpResponse->getBody()));

        if (!$response->hasApiUrl()) {
            throw AuthenticationException::rpcServerNotAvailable();
        }

        return new Session($token, $authType, $response->getApiUrl());
    }

    public function player(Session $session) : Player
    {
        $rpcRequest = Request::fromArray([
            'direction' => RpcDirection::REQUEST(),
            'auth' => Request\AuthInfo::fromArray([
                'token' => Request\AuthInfo\JWT::fromArray([
                    'contents' => $session->token()->value(),
                    'unknown13' => 59
                ]),
                'provider' => $session->authType()
            ]),
            'rpcId' => 8145806132888207461,
            'latitude' => '50.0773484',
            'longitude' => '19.9093808',
            'altitude' => '0',
            'unknown12' => 989,
            'requests' => new MessageCollection([
                Request\Requests::fromArray(['type' => RequestMethod::GET_PLAYER()]),
            ])
        ]);


        $httpResponse = $this->httpClient->post($session->apiUrl(),[
            'body' => (string) $rpcRequest->toStream(),
            'headers' => [
                'User-Agent' => self::HTTP_USER_AGENT
            ]
        ]);

        $response = Response::fromStream((string) Stream::wrap((string) $httpResponse->getBody()));


        $player = \PkgClient\Protocol\Message\Player::fromStream($response->getResponsesList()[0]);

        return new Player(
            $player->getProfile()->getUsername(),
            $player->getProfile()->getItemStorage(),
            $player->getProfile()->getPokeStorage()
        );
    }
}