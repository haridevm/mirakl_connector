<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Context;

use Mirakl\Process\Model\Process;

class ProcessContext
{
    /**
     * @var Process|null
     */
    private $process;

    /**
     * @param Process $process
     */
    public function setProcess(Process $process): void
    {
        $this->process = $process;
    }

    /**
     * @return Process|null
     */
    public function getProcess(): ?Process
    {
        return $this->process;
    }
}
