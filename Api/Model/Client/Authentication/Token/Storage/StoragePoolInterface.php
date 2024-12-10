<?php
declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Token\Storage;

interface StoragePoolInterface
{
    /**
     * @param string $code
     * @return StorageInterface
     */
    public function get(string $code): StorageInterface;

    /**
     * @return StorageInterface[]
     */
    public function getAll(): array;
}