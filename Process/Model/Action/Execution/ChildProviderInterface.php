<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Action\Execution;

use Mirakl\Process\Model\Action\ActionInterface;
use Mirakl\Process\Model\Process;

interface ChildProviderInterface
{
    /**
     * @param Process         $parent
     * @param ActionInterface $childAction
     * @return Process|null
     */
    public function get(Process $parent, ActionInterface $childAction): ?Process;
}
