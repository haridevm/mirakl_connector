<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Execution;

use Mirakl\Process\Model\Process;

class Failure
{
    /**
     * @param Process $process
     * @param string  $status
     * @return void
     */
    public function propagate(Process $process, string $status): void
    {
        $this->stopParents($process, $status);
        $this->cancelChildren($process);
    }

    /**
     * @param Process $process
     * @param string  $status
     * @return void
     */
    private function stopParents(Process $process, string $status): void
    {
        if ($parent = $process->getParent()) {
            $parent->stop($status);
            $this->stopParents($parent, $status);
        }
    }

    /**
     * @param Process $process
     * @return void
     */
    private function cancelChildren(Process $process): void
    {
        $collection = $process->getChildrenCollection()
            ->addFieldToFilter('status', ['in' => [
                Process::STATUS_IDLE,
                Process::STATUS_PENDING,
                Process::STATUS_PENDING_RETRY,
            ]])
            ->cancel();

        /** @var Process $child */
        foreach ($collection->getItems() as $child) {
            $this->cancelChildren($child);
        }
    }
}
