<?php

declare (strict_types = 1);

namespace PkgClient;

use GuzzleHttp\Client;
use PkgClient\Authenticator\Token;
use PkgClient\Exception\AuthenticationException;
use PkgClient\Protocol\Communication\Request;
use PkgClient\Protocol\Communication\Response;
use PkgClient\Protocol\Enum\RequestMethod;
use PkgClient\Protocol\Enum\RpcDirection;
use PkgClient\SDK\Location;
use PkgClient\SDK\Session;
use Protobuf\MessageCollection;
use Protobuf\Stream;

final class RPCApi
{
    const API_ENTRY_URL = 'https://pgorelease.nianticlabs.com/plfe/rpc';
    const HTTP_USER_AGENT = 'Niantic App';
    const RPC_ID = 8145806132888207460;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @param Client $httpClient
     */
    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param Token $token
     * @param Location $location
     * @return Response
     * @throws AuthenticationException
     */
    public function getApiUrl(Token $token, Location $location) : Response
    {
        $rpcRequest = Request::fromArray([
            'auth' => Request\AuthInfo::fromArray([
                'token' => Request\AuthInfo\JWT::fromArray([
                    'contents' => $token->value(),
                    'unknown13' => 59
                ]),
                'provider' => $token->authType()
            ]),
            'direction' => RpcDirection::REQUEST(),
            'rpcId' => self::RPC_ID,
            'latitude' => $location->lat(),
            'longitude' => $location->long(),
            'altitude' => $location->alt(),
            'unknown6' => null,
            'unknown12' => 989,
            'requests' => $this->heartBeatMessages()
        ]);

        $httpResponse = $this->httpClient->post(self::API_ENTRY_URL,[
            'body' => (string) $rpcRequest->toStream(),
            'headers' => [
                'User-Agent' => self::HTTP_USER_AGENT
            ]
        ]);

        $response = Response::fromStream(Stream::wrap((string) $httpResponse->getBody()));

        if (!$response->hasApiUrl()) {
            throw AuthenticationException::rpcServerNotAvailable();
        }

        return $response;
    }

    /**
     * @param Session $session
     * @return Response
     */
    public function getPlayer(Session $session) : Response
    {
        $rpcRequest = Request::fromArray([
            'direction' => RpcDirection::REQUEST(),
            'auth' => Request\AuthInfo::fromArray([
                'token' => Request\AuthInfo\JWT::fromArray([
                    'contents' => $session->token()->value(),
                    'unknown13' => 59
                ]),
                'provider' => $session->token()->authType()
            ]),
            'rpcId' => self::RPC_ID,
            'latitude' => $session->location()->lat(),
            'longitude' => $session->location()->long(),
            'altitude' => $session->location()->alt(),
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

        return Response::fromStream((string) Stream::wrap((string) $httpResponse->getBody()));
    }

    /**
     * @return MessageCollection
     */
    private function heartBeatMessages() : MessageCollection
    {
        return new MessageCollection([
            Request\Requests::fromArray(['type' => RequestMethod::GET_PLAYER()]),
            Request\Requests::fromArray(['type' => RequestMethod::GET_HATCHED_EGGS()]),
            Request\Requests::fromArray(['type' => RequestMethod::GET_INVENTORY()]),
            Request\Requests::fromArray(['type' => RequestMethod::CHECK_AWARDED_BADGES()]),
            Request\Requests::fromArray(['type' => RequestMethod::DOWNLOAD_SETTINGS()])
        ]);
    }
}