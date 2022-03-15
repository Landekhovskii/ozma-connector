<?php

namespace Landekhovskii\OzmaConnector;

use Symfony\Component\HttpClient\HttpClient;

use Symfony\Contracts\HttpClient\Exception;
use Psr\Cache\InvalidArgumentException;

/**
 * Class Landekhovskii\OzmaConnector\Transactions
 * @author Evgenii Landekhovskii <evgenii@landekhovskii.pro>
 */
class Views
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
     * @var array
     */
    private array $filter = [];

    /**
     * @var array $response
     */
    private array $response;

    /**
     * @param string $viewSchema
     * @param string $viewEntity
     * @param string|null $dataUrl
     */
    public function __construct(string $viewSchema, string $viewEntity, ?string $dataUrl = null)
    {
        $this->httpClient = new HttpClient();
        $this->auth = new Auth();

        $this->setDataUrl($dataUrl ?? $_ENV['OZMA_DATA_URL'] . '/views/by_name/' . $viewSchema . '/' . $viewEntity . '/entries');
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
     * @param array $filter
     */
    public function setFilter(array $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
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

        $columns = $response['info']['columns'];
        $rows = $response['result']['rows'];

        $result = [];
        foreach ($rows as $row) {
            $id = $row['mainId'];

            foreach ($row['values'] as $key => $value) {
                $column = $columns[$key]['name'];

                $result[$id][$column] = $value['value'];
            }
        }

        $this->setResponse($result);
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

        $response = $httpClient->request('GET', $this->getDataUrl(), [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->auth->getAccessToken()
            ],
            'query' => $this->getFilter()
        ]);

        $content = $response->toArray();
        $this->setResponse($content);

        $this->prepareResponse();
    }
}
