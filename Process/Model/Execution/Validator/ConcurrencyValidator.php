<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\AlreadyRunningException;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ResourceModel\Process\Collection;
use Mirakl\Process\Model\ResourceModel\Process\CollectionFactory;

/**
 * This validator prevents multiple processes of the same type being
 * executed at the same time, except for the defined $ignoreProcessCodes
 */
class ConcurrencyValidator implements ValidatorInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var string[]
     */
    private array $ignoreProcessCodes;

    /**
     * @param CollectionFactory $collectionFactory
     * @param string[]          $ignoreProcessCodes
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        array $ignoreProcessCodes = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->ignoreProcessCodes = $ignoreProcessCodes;
    }

    /**
     * @inheritdoc
     */
    public function validate(Process $process): void
    {
        if (in_array($process->getCode(), $this->ignoreProcessCodes)) {
            return;
        }

        $running = $this->getRunningProcesses($process);

        if ($running->count()) {
            throw new AlreadyRunningException($process, __('An identical process is already running.'));
        }
    }

    /**
     * @param Process $process
     * @return Collection
     */
    private function getRunningProcesses(Process $process): Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addProcessingFilter();
        $collection->addFieldToFilter('name', $process->getName());

        if ($process->getCode()) {
            $collection->addFieldToFilter('code', $process->getCode());
        }

        return $collection;
    }
}
