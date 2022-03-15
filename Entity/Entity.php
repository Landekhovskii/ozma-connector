<?php

namespace Landekhovskii\OzmaConnector\Entity;

/**
 * Class Landekhovskii\OzmaConnector\Entity
 * @author Evgenii Landekhovskii <evgenii@landekhovskii.pro>
 */
class Entity
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
     * @return array
     */
    public function toArray(): array
    {
        $entity['schema'] = $this->getSchemaName();
        $entity['name'] = $this->getEntityName();

        return $entity;
    }
}
