<?php
declare(strict_types=1);

namespace Mirakl\Process\Model;

use Mirakl\Process\Helper\Config;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\ResourceModel\Process\Collection as ProcessCollection;

class TimeoutManager
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var string[]
     */
    private $longProcessCodes;

    /**
     * @param Config        $config
     * @param ProcessHelper $processHelper
     * @param array         $longProcessCodes
     */
    public function __construct(
        Config $config,
        ProcessHelper $processHelper,
        array $longProcessCodes = []
    ) {
        $this->config = $config;
        $this->processHelper = $processHelper;
        $this->longProcessCodes = $longProcessCodes;
    }

    /**
     * Applies timeout to processes and returns the collection of processes in timeout
     *
     * @param string|null $hash
     * @return ProcessCollection
     */
    public function applyTimeout($hash = null)
    {
        $collection = $this->processHelper->getRunningProcesses($hash);

        /** @var Process $process */
        foreach ($collection as $key => $process) {
            if (!$this->isProcessTimedOut($process) || ($process->isParent() && $process->hasProcessingChild())) {
                // Return only timed out processes
                $collection->removeItemByKey($key);
                continue;
            }

            $process->markAsTimeout();

            if (!$parent = $process->getParent()) {
                continue;
            }

            $children = $parent->getChildrenCollection();
            foreach ($children as $child) {
                /** @var Process $child */
                // Cancel processing, pending and idle children
                if ($child->isProcessing() || $child->isPending() || $child->isStatusIdle()) {
                    $child->cancel();
                }
            }

            // Mark parent as timeout
            $parent->markAsTimeout();
            if (!in_array($parent->getId(), $collection->getColumnValues('id'))) {
                $collection->addItem($parent);
            }
        }

        return $collection;
    }

    /**
     * Indicates if given process is timed out or not.
     * Condition for a process to be timed out:
     *
     * - Process has status "processing"
     * - A timeout delay is configured in admin
     * - Configured delay has expired since the process execution has began
     *
     * @param Process $process
     * @return bool
     */
    private function isProcessTimedOut(Process $process)
    {
        if (!$process->isProcessing()) {
            return false;
        }

        if (!$delay = $process->getTimeout()) {
            $code = $process->getCode();
            $delay = in_array($code, $this->longProcessCodes) ? $this->config->getLongTimeoutDelay() : $this->config->getShortTimeoutDelay();
        }

        if (!$delay) {
            return false;
        }

        $timeoutDate = new \DateTime();
        $timeoutDate->sub(new \DateInterval(sprintf('PT%dM', $delay)));

        $updatedAt = new \DateTime($process->getUpdatedAt());

        return $updatedAt < $timeoutDate;
    }
}
