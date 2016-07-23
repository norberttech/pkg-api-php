<?php

declare (strict_types = 1);

namespace PkgClient\Authenticator;

use GuzzleHttp\Client;
use PkgClient\Authenticator;
use PkgClient\RPCApi;
use PkgClient\SDK;

final class PokemonClub implements Authenticator
{
    const LOGIN_URL = 'https://sso.pokemon.com/sso/login?service=https://sso.pokemon.com/sso/oauth2.0/callbackAuthorize';
    const LOGIN_OAUTH_URL = 'https://sso.pokemon.com/sso/oauth2.0/accessToken';

    const OAUTH_CLIENT_ID = 'mobile-app_pokemon-go';
    const OAUTH_CLIENT_REDIRECT_URI = 'https://www.nianticlabs.com/pokemongo/error';
    const OAUTH_CLIENT_SECRET = 'w8ScCUXJQc6kXKw8FiOhd8Fixzht18Dq3PEVkUCP5ZPxtgyWsbTvWHFLm2wNY0JR';
    const OAUTH_CLIENT_GRANT_TYPE = 'refresh_token';

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
     * @param string $username
     * @param string $password
     * @return Token
     */
    public function obtainToken(string $username, string $password) : Token
    {
        $sessionResponse = $this->httpClient->get(self::LOGIN_URL, [
            'headers' => [
                'User-Agent' => RPCApi::HTTP_USER_AGENT
            ],
        ]);

        $session = json_decode((string) $sessionResponse->getBody(), true);

        $loginResponse = $this->httpClient->post(self::LOGIN_URL, [
            'form_params' => [
                'lt' =>  $session['lt'],
                'execution' => $session['execution'],
                '_eventId' => 'submit',
                'username' => $username,
                'password' => $password
            ],
            'headers' => [
                'User-Agent' => RPCApi::HTTP_USER_AGENT
            ],
            'allow_redirects' => false
        ]);

        $location = current($loginResponse->getHeader('location'));

        parse_str(parse_url($location, PHP_URL_QUERY), $queryParts);

        $tokenResponse = $this->httpClient->post(self::LOGIN_OAUTH_URL, [
            'form_params' => [
                'client_id' => self::OAUTH_CLIENT_ID,
                'redirect_uri' => self::OAUTH_CLIENT_REDIRECT_URI,
                'client_secret' => self::OAUTH_CLIENT_SECRET,
                'grant_type' => self::OAUTH_CLIENT_GRANT_TYPE,
                'code' => $queryParts['ticket']
            ],
            'headers' => [
                'User-Agent' => RPCApi::HTTP_USER_AGENT
            ],
        ]);

        $tokenQuery = (string) $tokenResponse->getBody();
        parse_str($tokenQuery, $tokenData);

        return new Token($tokenData['access_token'], (int) $tokenData['expires'], 'pct');
    }
}