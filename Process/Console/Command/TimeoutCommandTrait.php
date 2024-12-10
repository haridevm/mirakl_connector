<?php

declare(strict_types=1);

namespace Mirakl\Process\Console\Command;

use Symfony\Component\Console\Input\InputOption;

trait TimeoutCommandTrait
{
    /**
     * @param array $options
     * @return void
     */
    public function addTimeoutCommandOption(array &$options)
    {
        $options[] = new InputOption(
            'timeout',
            null,
            InputOption::VALUE_OPTIONAL,
            'Delay in minutes after which the process has to be automatically cancelled.'
        );
    }
}
