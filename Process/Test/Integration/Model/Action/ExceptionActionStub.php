<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Action;

use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Process;

class ExceptionActionStub extends AbstractAction
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Exception action stub';
    }

    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        throw new \Exception('Damn! I am an exception.');
    }
}
