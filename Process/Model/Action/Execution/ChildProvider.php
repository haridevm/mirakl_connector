<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Action\Execution;

use Mirakl\Process\Model\Action\ActionInterface;
use Mirakl\Process\Model\ChildProcessFactoryInterface;
use Mirakl\Process\Model\Process;

class ChildProvider implements ChildProviderInterface
{
    /**
     * @var ChildProcessFactoryInterface
     */
    private $childProcessFactory;

    /**
     * @param ChildProcessFactoryInterface $childProcessFactory
     */
    public function __construct(ChildProcessFactoryInterface $childProcessFactory)
    {
        $this->childProcessFactory = $childProcessFactory;
    }

    /**
     * @inheritdoc
     */
    public function get(Process $parent, ActionInterface $childAction): ?Process
    {
        if ($childProcess = $this->getChildProcessPending($parent, $childAction)) {
            $childProcess->setAction($childAction);
            $childProcess->setParent($parent);
            $childAction->setParams($childProcess->getParams());

            return $childProcess; // Always return pending child process if possible
        }

        if ($this->getChildProcessCompleted($parent, $childAction)) {
            return null; // Child process has already been completed, don't execute it again
        }

        return $this->childProcessFactory->create($parent, $childAction);
    }

    /**
     * @param Process         $parent
     * @param ActionInterface $childAction
     * @return Process|null
     */
    private function getChildProcessPending(Process $parent, ActionInterface $childAction): ?Process
    {
        $childProcess = $parent->getChildrenCollection()
            ->addActionFilter($childAction)
            ->addStatusFilter(['in' => [Process::STATUS_IDLE, Process::STATUS_PENDING_RETRY]])
            ->getFirstItem();

        return $childProcess->getId() ? $childProcess : null;
    }

    /**
     * @param Process         $parent
     * @param ActionInterface $childAction
     * @return Process|null
     */
    private function getChildProcessCompleted(Process $parent, ActionInterface $childAction): ?Process
    {
        $childProcess = $parent->getChildrenCollection()
            ->addActionFilter($childAction)
            ->addCompletedFilter()
            ->getFirstItem();

        return $childProcess->getId() ? $childProcess : null;
    }
}