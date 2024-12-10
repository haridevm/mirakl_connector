<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\CannotRunException;
use Mirakl\Process\Model\Process;

class CanRunValidator implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate(Process $process): void
    {
        if (!$process->isPending() && !$process->getForceExecution()) {
            throw new CannotRunException($process, __('Cannot run a process that is not in pending status.'));
        }
    }
}
