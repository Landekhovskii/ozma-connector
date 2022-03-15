<?php

namespace Landekhovskii\OzmaConnector;

use Landekhovskii\OzmaConnector\Entity\Entity;
use Landekhovskii\OzmaConnector\Entity\Operation;

use Symfony\Contracts\HttpClient\Exception;
use Psr\Cache\InvalidArgumentException;

/**
 * Class Landekhovskii\OzmaConnector\Transactions
 * @author Evgenii Landekhovskii <evgenii@landekhovskii.pro>
 */
class OzmaConnector
{
    /**
     * @var string $schemaName
     */
    private string $schemaName;

    /**
     * @var string $entityName
     */
    private string $entityName;

    /**
     * @param string $schemaName
     * @param string $entityName
     */
    public function __construct(string $schemaName, string $entityName)
    {
        $this->setSchemaName($schemaName);
        $this->setEntityName($entityName);
    }

    /**
     * @param string $schemaName
     */
    public function setSchemaName(string $schemaName): void
    {
        $this->schemaName = $schemaName;
    }

    /**
     * @return string
     */
    public function getSchemaName(): string
    {
        return $this->schemaName;
    }

    /**
     * @param string $entityName
     */
    public function setEntityName(string $entityName): void
    {
        $this->entityName = $entityName;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @param array $rows
     * @return array
     * @throws Exception\ClientExceptionInterface
     * @throws Exception\DecodingExceptionInterface
     * @throws Exception\RedirectionExceptionInterface
     * @throws Exception\ServerExceptionInterface
     * @throws Exception\TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function insert(array $rows): array
    {
        $entity = new Entity($this->getSchemaName(), $this->getEntityName());

        $transactions = new Transactions();

        foreach ($rows as $entries) {
            $operation = new Operation(Operation::TYPE_INSERT, $entity);
            $operation->setEntries($entries);
            $transactions->pushOperation($operation);
        }

        $transactions->send();

        return $transactions->getResponse();
    }

    /**
     * @param array $filter
     * @return array
     * @throws Exception\ClientExceptionInterface
     * @throws Exception\DecodingExceptionInterface
     * @throws Exception\RedirectionExceptionInterface
     * @throws Exception\ServerExceptionInterface
     * @throws Exception\TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function select(array $filter = []): array
    {
        $views = new Views($this->getSchemaName(), $this->getEntityName());
        $views->setFilter($filter);
        $views->send();

        return $views->getResponse();
    }

    /**
     * @param array $rows
     * @return array
     * @throws Exception\ClientExceptionInterface
     * @throws Exception\DecodingExceptionInterface
     * @throws Exception\RedirectionExceptionInterface
     * @throws Exception\ServerExceptionInterface
     * @throws Exception\TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function update(array $rows): array
    {
        $entity = new Entity($this->getSchemaName(), $this->getEntityName());

        $transactions = new Transactions();

        foreach ($rows as $id => $entries) {
            $operation = new Operation(Operation::TYPE_UPDATE, $entity);
            $operation->setId($id);
            $operation->setEntries($entries);
            $transactions->pushOperation($operation);
        }

        $transactions->send();

        return $transactions->getResponse();
    }

    /**
     * @param array $ids
     * @return array
     * @throws Exception\ClientExceptionInterface
     * @throws Exception\DecodingExceptionInterface
     * @throws Exception\RedirectionExceptionInterface
     * @throws Exception\ServerExceptionInterface
     * @throws Exception\TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function delete(array $ids): array
    {
        $entity = new Entity($this->getSchemaName(), $this->getEntityName());

        $transactions = new Transactions();

        foreach ($ids as $id) {
            $operation = new Operation(Operation::TYPE_DELETE, $entity);
            $operation->setId($id);
            $transactions->pushOperation($operation);
        }

        $transactions->send();

        return $transactions->getResponse();
    }
}
