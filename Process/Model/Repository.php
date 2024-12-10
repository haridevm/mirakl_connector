<?php
declare(strict_types=1);

namespace Mirakl\Process\Model;

use Mirakl\Process\Model\ResourceModel\Process as ProcessResource;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;

class Repository
{
    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var ProcessResource
     */
    private $processResource;

    /**
     * @param ProcessFactory         $processFactory
     * @param ProcessResourceFactory $processResourceFactory
     */
    public function __construct(
        ProcessFactory $processFactory,
        ProcessResourceFactory $processResourceFactory
    ) {
        $this->processFactory = $processFactory;
        $this->processResource = $processResourceFactory->create();
    }

    /**
     * @param int $id
     * @return Process|null
     */
    public function get($id): ?Process
    {
        $process = $this->processFactory->create();
        $this->processResource->load($process, $id);

        return $process->getId() ? $process : null;
    }
}
