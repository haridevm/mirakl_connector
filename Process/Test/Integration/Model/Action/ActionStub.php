<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Action;

use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Process;

class ActionStub extends AbstractAction
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Simple action stub';
    }

    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        $process->output('This is a test');

        return ['foo'];
    }
}
