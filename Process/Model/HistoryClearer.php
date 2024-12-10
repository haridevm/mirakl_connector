<?php

declare(strict_types=1);

namespace Mirakl\Process\Model;

use Mirakl\Process\Model\ResourceModel\Process\CollectionFactory as ProcessCollectionFactory;

class HistoryClearer
{
    public const FILES_DELETE_STEP = 1000;

    /**
     * @var ProcessCollectionFactory
     */
    private $processCollectionFactory;

    /**
     * @var DeleteHandler
     */
    private $deleteHandler;

    /**
     * @var int
     */
    private int $deleteStep;

    /**
     * @param ProcessCollectionFactory $processCollectionFactory
     * @param DeleteHandler            $deleteHandler
     * @param int                      $deleteStep
     */
    public function __construct(
        ProcessCollectionFactory $processCollectionFactory,
        DeleteHandler $deleteHandler,
        int $deleteStep = self::FILES_DELETE_STEP
    ) {
        $this->processCollectionFactory = $processCollectionFactory;
        $this->deleteHandler = $deleteHandler;
        $this->deleteStep = $deleteStep;
    }

    /**
     * Deletes processes and associated files created before $beforeDate
     *
     * @param Process|null $process
     * @param string|null  $beforeDate
     * @throws \Exception
     */
    public function execute(?Process $process = null, ?string $beforeDate = null)
    {
        try {
            if ($process && $beforeDate) {
                $process->output(__('Deleting all Mirakl processes and files created before %1...', $beforeDate), true);
            } elseif ($process) {
                $process->output(__('Deleting all Mirakl processes and files...'), true);
            }

            if ($beforeDate) {
                $this->deleteBeforeDate($beforeDate);
            } else {
                $this->deleteHandler->executeAll();
            }

            $process?->output(__('Done!', true));
        } catch (\Exception $e) {
            if ($process) {
                $process->output(__('An error occurred: %1', $e->getMessage()), true);
            } else {
                throw new \Exception($e->getMessage());
            }
        }
    }

    /**
     * Deletes all process files/directories created before $beforeDate
     *
     * @param string $beforeDate
     * @return void
     */
    private function deleteBeforeDate(string $beforeDate): void
    {
        while (true) {
            // Fetch processes step by step and delete associated files
            $collection = $this->processCollectionFactory->create();
            $collection->addFieldToFilter('created_at', ['lt' => $beforeDate]);
            $collection->getSelect()
                ->limit($this->deleteStep)
                ->order('id ASC');

            if (!$collection->count()) {
                break;
            }

            $this->deleteHandler->executeCollection($collection);
        }
    }
}
