<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\AlreadyStartedException;
use Mirakl\Process\Model\Process;

class AlreadyStartedValidator implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate(Process $process): void
    {
        if ($process->isStarted()) {
            throw new AlreadyStartedException($process, __('Cannot start a process that is already started.'));
        }
    }
}
