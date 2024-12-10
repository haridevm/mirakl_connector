<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Token\Storage;

use Magento\Framework\Exception\NoSuchEntityException;

class StoragePool implements StoragePoolInterface
{
    /**
     * @var StorageInterface[]
     */
    private array $storages;

    /**
     * @param array $storages
     */
    public function __construct(array $storages = [])
    {
        $this->storages = $storages;
    }

    /**
     * @inheritdoc
     */
    public function get(string $code): StorageInterface
    {
        if (isset($this->storages[$code])) {
            return $this->storages[$code];
        }

        throw new NoSuchEntityException(__('Could not find storage with code %1', $code));
    }

    /**
     * @inheritdoc
     */
    public function getAll(): array
    {
        return $this->storages;
    }
}
