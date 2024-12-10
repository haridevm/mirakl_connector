<?php

declare(strict_types=1);

namespace Mirakl\Process\Console\Command;

use Mirakl\Process\Model\Process;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @property \Mirakl\Process\Helper\Data $helper
 */
trait ParallelCommandTrait
{
    /**
     * @param array $options
     * @return void
     */
    public function addParallelCommandOptions(array &$options)
    {
        $options[] = new InputOption(
            'max-running-processes',
            null,
            InputOption::VALUE_OPTIONAL,
            'Define the maximum number of processes that can be executed in parallel for this command.',
            1
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function getMaxRunningProcessesOption(InputInterface $input, OutputInterface $output, $default = 1)
    {
        $provided = $input->getOption('max-running-processes');
        $max = max((int) $provided, (int) $default);

        if ($provided != $max) {
            $output->writeln(sprintf('<comment>Bad --max-running-processes provided, using %d.</comment>', $max));
        }

        return $max;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string|null     $hash
     * @return bool
     */
    public function canRunCommand(InputInterface $input, OutputInterface $output, $hash = null)
    {
        $collection = $this->helper->getRunningProcesses($hash);

        foreach ($collection as $process) {
            /** @var Process $process */
            $output->writeln(sprintf('<comment>Process #%d is still running.</comment>', $process->getId()));
        }

        return $collection->count() < $this->getMaxRunningProcessesOption($input, $output);
    }
}
