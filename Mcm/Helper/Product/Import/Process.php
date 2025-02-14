<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Helper\Product\Import;

use Mirakl\Process\Model\Process as ProcessModel;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;
use Mirakl\Mcm\Model\Product\Import\Handler\Csv as McmHandler;

class Process
{
    /**
     * @var ProcessFactory
     */
    protected $processFactory;

    /**
     * @var ProcessResourceFactory
     */
    protected $processResourceFactory;

    /**
     * @param ProcessFactory         $processFactory
     * @param ProcessResourceFactory $processResourceFactory
     */
    public function __construct(
        ProcessFactory $processFactory,
        ProcessResourceFactory $processResourceFactory
    ) {
        $this->processFactory = $processFactory;
        $this->processResourceFactory = $processResourceFactory;
    }

    /**
     * Imports products from CM51 into Magento from specified process
     *
     * @param \DateTime      $since
     * @param \DateTime|null $until
     */
    public function runApi($since, $until = null)
    {
        /** @var ProcessModel $process */
        $process = $this->processFactory->create()
            ->setType(ProcessModel::TYPE_IMPORT_MCM)
            ->setCode(McmHandler::CODE)
            ->setStatus(ProcessModel::STATUS_PENDING)
            ->setName('MCM products import')
            ->setParams([$since, true, $until])
            ->setHelper(McmHandler::class)
            ->setMethod('run');

        $this->processResourceFactory->create()->save($process);

        $process->run();
    }
}
