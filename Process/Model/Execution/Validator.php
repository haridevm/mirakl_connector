<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Execution;

use Mirakl\Process\Model\Exception\ProcessException;
use Mirakl\Process\Model\Execution\Validator\ValidatorInterface;
use Mirakl\Process\Model\Process;

class Validator
{
    /**
     * @var ValidatorInterface[]
     */
    private array $validators;

    /**
     * @param array $validators
     */
    public function __construct(array $validators = [])
    {
        $this->validators = $validators;
    }

    /**
     * @param Process $process
     * @throws ProcessException
     */
    public function validate(Process $process): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate($process);
        }
    }
}
