<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Action;

use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Exception\ChildProcessException;
use Mirakl\Process\Model\Process;

class ChildProcessExceptionActionStub extends AbstractAction
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Child process exception action stub';
    }

    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        throw new ChildProcessException($process, __('I screwed up, forgive me.'));
    }
}
