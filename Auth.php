<?php

namespace Landekhovskii\OzmaConnector;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

use Symfony\Contracts\HttpClient\Exception;
use Psr\Cache\InvalidArgumentException;

/**
 * Class Landekhovskii\OzmaConnector\Auth
 * @author Evgenii Landekhovskii <evgenii@landekhovskii.pro>
 */
class Auth
{
    /**
     *
     */
    const CACHE_NAMESPACE = 'ozma.tokens';

    /**
     *
     */
    const CACHE_LIFETYME = 0;

    /**
     *
     */
    const CACHE_DIRECTORY = '../var/cache';

    /**
     *
     */
    const CACHE_ACCESS_TOKEN_NAME = 'access_token';

    /**
     *
     */
    const CACHE_REFRESH_TOKEN_NAME = 'refresh_token';

    /**
     *
     */
    const AUTH_GRANT_TYPE_PASSWORD = 'password';

    /**
     *
     */
    const AUTH_GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

    /**
     * @var HttpClient $httpClient
     */
    private HttpClient $httpClient;

    /**
     * @var FilesystemAdapter $cache
     */
    private FilesystemAdapter $cache;

    /**
     * @var string $authUrl
     */
    private string $authUrl;

    /**
     * @var string $clientId
     */
    private string $clientId;

    /**
     * @var string $clientSecret
     */
    private string $clientSecret;

    /**
     * @var string $username
     */
    private string $username;

    /**
     * @var string $password
     */
    private string $password;

    /**
     * @param string|null $authUrl
     * @param string|null $clientId
     * @param string|null $clientSecret
     * @param string|null $username
     * @param string|null $password
     */
    public function __construct(?string $authUrl = null, ?string $clientId = null, ?string $clientSecret = null, ?string $username = null, ?string $password = null)
    {
        $this->httpClient = new HttpClient();
        $this->cache = new FilesystemAdapter(self::CACHE_NAMESPACE, self::CACHE_LIFETYME, self::CACHE_DIRECTORY);

        $this->setAuthUrl($authUrl ?? $_ENV['OZMA_AUTH_URL']);
        $this->setClientId($clientId ?? $_ENV['OZMA_AUTH_CLIENT_ID']);
        $this->setClientSecret($clientSecret ?? $_ENV['OZMA_AUTH_CLIENT_SECRET']);
        $this->setUsername($username ?? $_ENV['OZMA_AUTH_CLIENT_USERNAME']);
        $this->setPassword($password ?? $_ENV['OZMA_AUTH_CLIENT_PASSWORD']);
    }

    /**
     * @param string $authUrl
     */
    public function setAuthUrl(string $authUrl): void
    {
        $this->authUrl = $authUrl;
    }

    /**
     * @return string
     */
    public function getAuthUrl(): string
    {
        return $this->authUrl;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $tokenName
     * @param string $tokenValue
     * @param int $expiresIn
     * @throws InvalidArgumentException
     */
    public function setTokenToCache(string $tokenName, string $tokenValue, int $expiresIn): void
    {
        $token = $this->cache->getItem($tokenName);
        $token->set($tokenValue);
        $token->expiresAfter($expiresIn);
        $this->cache->save($token);
    }

    /**
     * @param string $tokenName
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function getTokenFromCache(string $tokenName): ?string
    {
        $token = $this->cache->getItem($tokenName);

        return $token->isHit() ? $token->get() : null;
    }

    /**
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function getAccessToken(): ?string
    {
        return $this->getTokenFromCache(self::CACHE_ACCESS_TOKEN_NAME);
    }

    /**
     * @param string $accessToken
     * @param int $expiresIn
     * @throws InvalidArgumentException
     */
    public function setAccessToken(string $accessToken, int $expiresIn): void
    {
        $this->setTokenToCache(self::CACHE_ACCESS_TOKEN_NAME, $accessToken, $expiresIn);
    }

    /**
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function getRefreshToken(): ?string
    {
        return $this->getTokenFromCache(self::CACHE_REFRESH_TOKEN_NAME);
    }

    /**
     * @param string $refreshToken
     * @param int $expiresIn
     * @throws InvalidArgumentException
     */
    public function setRefreshToken(string $refreshToken, int $expiresIn): void
    {
        $this->setTokenToCache(self::CACHE_REFRESH_TOKEN_NAME, $refreshToken, $expiresIn);
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public function getRequestBody(): array
    {
        $refresh_token = $this->getRefreshToken();

        $requestBody['client_id'] = $this->getClientId();
        $requestBody['client_secret'] = $this->getClientSecret();

        if (is_null($refresh_token)) {
            $requestBody['grant_type'] = self::AUTH_GRANT_TYPE_PASSWORD;
            $requestBody['username'] = $this->getUsername();
            $requestBody['password'] = $this->getPassword();
        }

        if (!is_null($refresh_token)) {
            $requestBody['grant_type'] = self::AUTH_GRANT_TYPE_REFRESH_TOKEN;
            $requestBody['refresh_token'] = $refresh_token;
        }

        return $requestBody;
    }

    /**
     * @throws Exception\ClientExceptionInterface
     * @throws Exception\DecodingExceptionInterface
     * @throws Exception\RedirectionExceptionInterface
     * @throws Exception\ServerExceptionInterface
     * @throws Exception\TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function auth(): void
    {
        $this->cache->prune();

        if (is_null($this->getAccessToken())) {
            $this->request($this->getRequestBody());
        }
    }

    /**
     * @param array $requestBody
     * @throws Exception\ClientExceptionInterface
     * @throws Exception\DecodingExceptionInterface
     * @throws Exception\RedirectionExceptionInterface
     * @throws Exception\ServerExceptionInterface
     * @throws Exception\TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function request(array $requestBody): void
    {
        $httpClient = $this->httpClient::create();

        $response = $httpClient->request('POST', $this->getAuthUrl(), [
            'body' => $requestBody
        ]);

        $content = $response->toArray();

        $this->setAccessToken($content['access_token'], $content['expires_in'] - 5);
        $this->setRefreshToken($content['refresh_token'], $content['refresh_expires_in'] - 5);
    }
}
