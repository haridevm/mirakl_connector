<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\File;

interface StorageInterface
{
    /**
     * @return string
     */
    public function getBaseDir(): string;

    /**
     * @return string
     */
    public function getDir(): string;

    /**
     * @param string $file
     * @return bool
     */
    public function removeFile(string $file): bool;

    /**
     * @return bool
     */
    public function clear(): bool;

    /**
     * @return void
     */
    public function cleanUp(): void;
}
