<?php

declare(strict_types=1);

namespace Mirakl\Event\Model;

use Mirakl\Event\Model\ResourceModel\Event as EventResource;
use Mirakl\Process\Model\Process;

class HistoryClearer
{
    /**
     * @var EventResource
     */
    private $eventResource;

    /**
     * @param EventResource $eventResource
     */
    public function __construct(EventResource $eventResource)
    {
        $this->eventResource = $eventResource;
    }

    /**
     * Deletes events created before $beforeDate
     *
     * @param Process $process
     * @param string  $beforeDate
     */
    public function execute(Process $process, string $beforeDate)
    {
        $process->output(__('Deleting all connector events created before %1 ...', $beforeDate), true);

        try {
            $this->deleteEvents($beforeDate);
            $process->output(__('Done!'), true);
        } catch (\Exception $e) {
            $process->output(__('An error occurred: %1', $e->getMessage()), true);
        }
    }

    /**
     * Delete events created before $beforeDate
     *
     * @param string $beforeDate
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function deleteEvents(string $beforeDate)
    {
        $connection = $this->eventResource->getConnection();
        $connection->delete(
            $this->eventResource->getMainTable(),
            ['created_at < ?' => $beforeDate]
        );
    }
}
