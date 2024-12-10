<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Execution;

use Mirakl\Process\Model\Exception\BadMethodException;
use Mirakl\Process\Model\Exception\ProcessException;
use Mirakl\Process\Model\Process;

class Executor
{
    /**
     * @var Validator
     */
    private $executeProcessValidator;

    /**
     * @param Validator $executeValidator
     */
    public function __construct(Validator $executeValidator)
    {
        $this->executeProcessValidator = $executeValidator;
    }

    /**
     * @param Process $process
     * @return mixed
     * @throws ProcessException
     */
    public function execute(Process $process)
    {
        $this->executeProcessValidator->validate($process);

        $process->setStatus(Process::STATUS_PROCESSING);

        $helper = $process->getHelperInstance();
        $method = $process->getMethod();

        if (!method_exists($helper, $method)) {
            throw new BadMethodException($process, __("Invalid helper method specified '%1'", $method));
        }

        $process->output(__(
            '<date>%1</date> Running %2::%3()',
            date('Y-m-d H:i:s'),
            get_class($helper),
            $method
        )->render(), true);

        $args = [$process];
        if ($process->getParams()) {
            $args = array_merge($args, $process->getParams());
        }

        return call_user_func_array([$helper, $method], $args);
    }
}
