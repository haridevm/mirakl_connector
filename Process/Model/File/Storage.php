<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Mirakl\Core\Model\File\FileHandlerInterface;

class Storage implements StorageInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var FileHandlerInterface
     */
    private $fileHandler;

    /**
     * @var string
     */
    private string $dir;

    /**
     * @param Filesystem           $filesystem
     * @param FileHandlerInterface $fileHandler
     * @param string               $dir
     */
    public function __construct(
        Filesystem $filesystem,
        FileHandlerInterface $fileHandler,
        string $dir = 'mirakl/process'
    ) {
        $this->filesystem = $filesystem;
        $this->fileHandler = $fileHandler;
        $this->dir = $dir;
    }

    /**
     * @inheritdoc
     */
    public function getBaseDir(): string
    {
        $dir = $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath();

        return rtrim($dir, DIRECTORY_SEPARATOR);
    }

    /**
     * @inheritdoc
     */
    public function getDir(): string
    {
        return $this->getBaseDir()
            . DIRECTORY_SEPARATOR
            . implode(DIRECTORY_SEPARATOR, explode('/', $this->dir));
    }

    /**
     * @inheritdoc
     */
    public function removeFile(string $file): bool
    {
        return $this->fileHandler->removeFile(
            $this->getBaseDir() . DIRECTORY_SEPARATOR . $file
        );
    }

    /**
     * @inheritdoc
     */
    public function clear(): bool
    {
        return $this->fileHandler->removeDir($this->getDir());
    }

    /**
     * @inheritdoc
     */
    public function cleanUp(): void
    {
        $this->fileHandler->removeEmptyDirs($this->getDir());
    }
}
