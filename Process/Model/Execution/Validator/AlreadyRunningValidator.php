<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\AlreadyRunningException;
use Mirakl\Process\Model\Process;

class AlreadyRunningValidator implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate(Process $process): void
    {
        if ($process->isProcessing()) {
            throw new AlreadyRunningException($process, __('Process is already running. Please try again later.'));
        }
    }
}
