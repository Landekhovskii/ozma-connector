<?php

namespace Landekhovskii\OzmaConnector;

use Symfony\Component\HttpClient\HttpClient;

use Symfony\Contracts\HttpClient\Exception;
use Psr\Cache\InvalidArgumentException;

/**
 * Class Landekhovskii\OzmaConnector\Transactions
 * @author Evgenii Landekhovskii <evgenii@landekhovskii.pro>
 */
class Transactions
{
    /**
     * @var HttpClient $httpClient
     */
    private HttpClient $httpClient;

    /**
     * @var Auth $auth
     */
    private Auth $auth;

    /**
     * @var string $dataUrl
     */
    private string $dataUrl;

    /**
     * @var Entity\Operation[] $operations
     */
    private array $operations = [];

    /**
     * @var array $response
     */
    private array $response;

    /**
     * @param string|null $dataUrl
     */
    public function __construct(?string $dataUrl = null)
    {
        $this->httpClient = new HttpClient();
        $this->auth = new Auth();

        $this->setDataUrl($dataUrl ?? $_ENV['OZMA_DATA_URL'] . '/transaction');
    }

    /**
     * @param string $dataUrl
     */
    public function setDataUrl(string $dataUrl): void
    {
        $this->dataUrl = $dataUrl;
    }

    /**
     * @return string
     */
    public function getDataUrl(): string
    {
        return $this->dataUrl;
    }

    /**
     * @param Entity\Operation[] $operations
     */
    public function setOperations(array $operations): void
    {
        $this->operations = $operations;
    }

    /**
     * @return Entity\Operation[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param Entity\Operation $operation
     */
    public function pushOperation(Entity\Operation $operation): void
    {
        array_push($this->operations, $operation);
    }

    /**
     * @param array $response
     */
    public function setResponse(array $response): void
    {
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     *
     */
    public function prepareResponse(): void
    {
        $response = $this->getResponse();
        $this->setResponse($response['results']);
    }

    /**
     * @return array
     */
    public function getRequestBody(): array
    {
        $operations = [];

        foreach ($this->getOperations() as $operation) {
            array_push($operations, $operation->toArray());
        }

        return [
            'operations' => $operations
        ];
    }

    /**
     * @throws Exception\ClientExceptionInterface
     * @throws Exception\DecodingExceptionInterface
     * @throws Exception\RedirectionExceptionInterface
     * @throws Exception\ServerExceptionInterface
     * @throws Exception\TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function send(): void
    {
        $this->auth->auth();

        $httpClient = $this->httpClient::create();

        $response = $httpClient->request('POST', $this->getDataUrl(), [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->auth->getAccessToken()
            ],
            'json' => $this->getRequestBody()
        ]);

        $content = $response->toArray();
        $this->setResponse($content);

        $this->prepareResponse();
    }
}
