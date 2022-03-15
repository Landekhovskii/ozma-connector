<?php

namespace Landekhovskii\OzmaConnector\Entity;

/**
 * Class Landekhovskii\OzmaConnector\Operation
 * @author Evgenii Landekhovskii <evgenii@landekhovskii.pro>
 */
class Operation
{
    /**
     *
     */
    const TYPE_INSERT = 'insert';

    /**
     *
     */
    const TYPE_UPDATE = 'update';

    /**
     *
     */
    const TYPE_DELETE = 'delete';

    /**
     * @var string $type
     */
    private string $type;

    /**
     * @var Entity $entity
     */
    private Entity $entity;

    /**
     * @var int|null $id
     */
    private ?int $id = null;

    /**
     * @var array|null $entries
     */
    private ?array $entries = null;

    /**
     * @param string $type
     * @param Entity $entity
     */
    public function __construct(string $type, Entity $entity)
    {
        $this->setType($type);
        $this->setEntity($entity);
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param Entity $entity
     */
    public function setEntity(Entity $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param array $entries
     */
    public function setEntries(array $entries): void
    {
        $this->entries = $entries;
    }

    /**
     * @return array|null
     */
    public function getEntries(): ?array
    {
        return $this->entries;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $operation['type'] = $this->getType();
        $operation['entity'] = $this->getEntity()->toArray();

        if (!is_null($this->getId())) {
            $operation['id'] = $this->getId();
        }

        if (!is_null($this->getEntries())) {
            $operation['entries'] = $this->getEntries();
        }

        return $operation;
    }
}
