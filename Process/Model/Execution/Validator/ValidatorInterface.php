<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\ProcessException;
use Mirakl\Process\Model\Process;

interface ValidatorInterface
{
    /**
     * @param Process $process
     * @return void
     * @throws ProcessException
     */
    public function validate(Process $process): void;
}
