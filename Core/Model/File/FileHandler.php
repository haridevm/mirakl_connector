<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\File;

use Magento\Framework\Filesystem\Io\File;

class FileHandler implements FileHandlerInterface
{
    /**
     * @var File
     */
    private $file;

    /**
     * @param File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @inheritdoc
     */
    public function removeFile(string $file): bool
    {
        if ($file && $this->file->fileExists($file)) {
            return $this->file->rm($file);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function removeDir(string $dir): bool
    {
        return $this->file->rmdir($dir, true);
    }

    /**
     * @inheritdoc
     */
    public function removeEmptyDirs(string $dir): bool
    {
        $empty = true;

        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') as $path) {
            if (is_dir($path)) {
                if (!$this->removeEmptyDirs($path)) {
                    $empty = false;
                }
            } else {
                $empty = false;
            }
        }

        if ($empty && is_dir($dir)) {
            $this->file->rmdir($dir);
        }

        return $empty;
    }
}
