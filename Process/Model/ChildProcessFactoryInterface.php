<?php
declare(strict_types=1);

namespace Mirakl\Process\Model;

use Mirakl\Process\Model\Action\ActionInterface;

interface ChildProcessFactoryInterface
{
    /**
     * @param Process         $parent
     * @param ActionInterface $childAction
     * @return Process
     */
    public function create(Process $parent, ActionInterface $childAction): Process;
}