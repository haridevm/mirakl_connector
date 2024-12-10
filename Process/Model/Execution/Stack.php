<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Execution;

use Mirakl\Process\Model\Process;

class Stack
{
    /**
     * @var Process[]
     */
    private array $stack = [];

    /**
     * @param Process $process
     * @return void
     */
    public function add(Process $process): void
    {
        if (!$process->getId()) {
            return;
        }

        $current = $this->current();
        if ($current && !$process->getParentId()) {
            $process->setParent($current);
        }

        $this->stack[$process->getId()] = $process;
    }

    /**
     * @return Process|null
     */
    public function current(): ?Process
    {
        return end($this->stack) ?: null;
    }

    /**
     * @param Process $process
     * @return void
     */
    public function remove(Process $process): void
    {
        unset($this->stack[$process->getId()]);
    }
}