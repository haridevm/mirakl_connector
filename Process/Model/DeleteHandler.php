<?php

declare(strict_types=1);

namespace Mirakl\Process\Model;

use Magento\Framework\DB\Select;
use Mirakl\Process\Model\File\StorageInterface;
use Mirakl\Process\Model\ResourceModel\Process as ProcessResource;
use Mirakl\Process\Model\ResourceModel\Process\Collection;

class DeleteHandler
{
    /**
     * @var ProcessResource
     */
    private $processResource;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param ProcessResource  $processResource
     * @param StorageInterface $storage
     */
    public function __construct(
        ProcessResource $processResource,
        StorageInterface $storage
    ) {
        $this->processResource = $processResource;
        $this->storage = $storage;
    }

    /**
     * @return void
     */
    public function executeAll(): void
    {
        // Remove all processes files and folders and truncate process table
        $this->storage->clear();
        $this->processResource->truncate();
    }

    /**
     * @param array[] $processes
     * @return void
     */
    public function executeList(array $processes): void
    {
        if (empty($processes)) {
            return;
        }

        $ids = [];

        // Delete processes associated files
        foreach ($processes as $process) {
            $ids[] = $process['id'];
            $this->storage->removeFile((string) $process['file']);
            $this->storage->removeFile((string) $process['mirakl_file']);
        }

        // Delete processes from database
        $this->processResource->deleteIds($ids);

        $this->storage->cleanUp();
    }

    /**
     * @param Collection $collection
     * @return void
     */
    public function executeCollection(Collection $collection)
    {
        $collection->getSelect()
            ->reset(Select::COLUMNS)
            ->columns(['id', 'file', 'mirakl_file']);

        $this->executeList($collection->getData());
    }
}
