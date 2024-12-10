<?php

declare(strict_types=1);

namespace Mirakl\Api\Model\Client\Authentication\Token\Storage;

interface StorageInterface
{
    /**
     * @return string|null
     */
    public function load(): ?string;

    /**
     * @param string $value
     * @return void
     */
    public function save(string $value): void;

    /**
     * @return void
     */
    public function reset(): void;
}
