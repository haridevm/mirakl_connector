<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Context;

use Mirakl\Process\Model\Process;

class ParentProcessContext
{
    /**
     * @var Process|null
     */
    private $parentProcess;

    /**
     * @param  Process $process
     * @return void
     */
    public function setParentProcess(Process $process): void
    {
        $this->parentProcess = $process;
    }

    /**
     * @param  Process $currentProcess
     * @return Process|null
     */
    public function getParentProcess(Process $currentProcess): ?Process
    {
        if ($currentProcess->getParentId()) {
            return $currentProcess->getParent();
        }

        if ($this->parentProcess == null || $this->parentProcess->getId() == $currentProcess->getId()) {
            return null;
        }

        return $this->parentProcess;
    }

    /**
     * @param  Process $currentProcess
     * @return int|null
     */
    public function getParentProcessId(Process $currentProcess): ?int
    {
        if ($currentProcess->getParentId()) {
            return (int) $currentProcess->getParentId();
        }

        if ($this->parentProcess == null || $this->parentProcess->getId() == $currentProcess->getId()) {
            return null;
        }

        return (int) $this->parentProcess->getId();
    }

    /**
     * @param Process $currentProcess
     */
    public function resetParent(Process $currentProcess): void
    {
        if ($this->parentProcess != null && $currentProcess->getId() == $this->parentProcess->getId()) {
            $this->parentProcess = null;
        }
    }
}
