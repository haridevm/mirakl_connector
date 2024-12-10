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
            ->addPendingFilter()
            ->cancel();

        /** @var Process $process */
        foreach ($collection->getItems() as $process) {
            $this->cancelChildren($process);
        }
    }
}