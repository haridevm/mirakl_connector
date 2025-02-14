<?php

declare(strict_types=1);

namespace Mirakl\Process\Console\Command;

use Magento\Framework\Console\Cli;
use Mirakl\Process\Helper\Data as Helper;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;
use Mirakl\Process\Model\TimeoutManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCommand extends Command
{
    public const PENDING_OPTION         = 'pending'; // Run pending processes
    public const RUN_PROCESS_OPTION     = 'run';     // Run a specific process id
    public const FORCE_EXECUTION_OPTION = 'force';   // Force process execution

    /**
     * Run command without applying timeout
     */
    public const NO_TIMEOUT_OPTION = 'no-timeout';

    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var ProcessResourceFactory
     */
    private $processResourceFactory;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var TimeoutManager
     */
    private $timeoutManager;

    /**
     * @param ProcessFactory         $processFactory
     * @param ProcessResourceFactory $processResourceFactory
     * @param Helper                 $helper
     * @param TimeoutManager         $timeoutManager
     * @param string|null            $name
     */
    public function __construct(
        ProcessFactory $processFactory,
        ProcessResourceFactory $processResourceFactory,
        Helper $helper,
        TimeoutManager $timeoutManager,
        $name = null
    ) {
        parent::__construct($name);
        $this->processFactory = $processFactory;
        $this->processResourceFactory = $processResourceFactory;
        $this->helper = $helper;
        $this->timeoutManager = $timeoutManager;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::PENDING_OPTION,
                null,
                InputOption::VALUE_NONE,
                'Execute the older PENDING process (one by one)'
            ),
            new InputOption(
                self::RUN_PROCESS_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                'Execute a specific process id'
            ),
            new InputOption(
                self::FORCE_EXECUTION_OPTION,
                null,
                InputOption::VALUE_NONE,
                'Force process execution even if not in pending status'
            ),
            new InputOption(
                self::NO_TIMEOUT_OPTION,
                null,
                InputOption::VALUE_NONE,
                'Run command without applying timeout'
            )
        ];

        $this->setName('mirakl:process')
            ->setDescription('Handles Mirakl processes execution and timeout')
            ->setDefinition($options);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption(self::NO_TIMEOUT_OPTION)) {
            // Apply timeout to processes
            $timeoutProcessCollection = $this->timeoutManager->applyTimeout();
            $ids = $timeoutProcessCollection->getColumnValues('id');
            if ($ids) {
                $output->writeln(sprintf(
                    '<comment>%s process(es) in timeout (id: %s)</comment>',
                    count($ids),
                    implode(',', $ids)
                ));
            } else {
                $output->writeln('<info>No processes in timeout</info>');
            }
        }

        if ($processId = $input->getOption(self::RUN_PROCESS_OPTION)) {
            $output->writeln(sprintf('<info>Processing #%s</info>', $processId));
            $process = $this->processFactory->create();
            $this->processResourceFactory->create()->load($process, $processId);
            if (!$process->getId()) {
                throw new \InvalidArgumentException('This process no longer exists.');
            }
            if (!$process->isPending() && !$input->getOption(self::FORCE_EXECUTION_OPTION)) {
                throw new \Exception('This process has already been executed. Use --force option to force execution.');
            }
            $process->addOutput('cli');
            $process->run(true);
        } elseif ($input->getOption(self::PENDING_OPTION)) {
            $process = $this->helper->getPendingProcess();
            if ($process) {
                $output->writeln(sprintf('<info>Processing #%s</info>', $process->getId()));
                $process->addOutput('cli');
                $process->run();
            } else {
                $output->writeln('<comment>Nothing to process</comment>');
            }
        } else {
            $output->writeln('<error>Please provide an option or use help</error>');

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
