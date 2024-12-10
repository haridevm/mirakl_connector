<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Action;

use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Exception\StopExecutionException;
use Mirakl\Process\Model\Process;

class StopExecutionActionStub extends AbstractAction
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Stop execution action stub';
    }

    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        throw new StopExecutionException($process, __('Stop me please!'));
    }
}
