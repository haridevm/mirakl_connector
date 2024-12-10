<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\File;

interface FileHandlerInterface
{
    /**
     * @param string $file
     * @return bool
     */
    public function removeFile(string $file): bool;

    /**
     * @param string $dir
     * @return bool
     */
    public function removeDir(string $dir): bool;

    /**
     * @param string $dir
     * @return bool
     */
    public function removeEmptyDirs(string $dir): bool;
}
