<?php
namespace Mirakl\Mci\Console\Command\Product\Import;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Mirakl\Core\Console\Command\CommandTrait;
use Mirakl\Mci\Helper\Config;
use Mirakl\Mci\Helper\Product\Image\Process as ImageProcessHelper;
use Mirakl\Process\Console\Command\ParallelCommandTrait;
use Mirakl\Process\Console\Command\TimeoutCommandTrait;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\TimeoutManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImagesCommand extends Command
{
    use CommandTrait;
    use ParallelCommandTrait;
    use TimeoutCommandTrait;

    const PROCESS_NAME = 'Products images import';

    const LIMIT_OPTION = 'limit';

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var ProcessHelper
     */
    private $helper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ImageProcessHelper
     */
    private $imageProcessHelper;

    /**
     * @var TimeoutManager
     */
    private $timeoutManager;

    /**
     * @param   State               $state
     * @param   ProcessFactory      $processFactory
     * @param   ProcessHelper       $helper
     * @param   Config              $config
     * @param   ImageProcessHelper  $imageProcessHelper
     * @param   TimeoutManager      $timeoutManager
     * @param   string|null         $name
     */
    public function __construct(
        State $state,
        ProcessFactory $processFactory,
        ProcessHelper $helper,
        Config $config,
        ImageProcessHelper $imageProcessHelper,
        TimeoutManager $timeoutManager,
        $name = null
    ) {
        parent::__construct($name);
        $this->appState = $state;
        $this->processFactory = $processFactory;
        $this->helper = $helper;
        $this->config = $config;
        $this->imageProcessHelper = $imageProcessHelper;
        $this->timeoutManager = $timeoutManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::LIMIT_OPTION,
                null,
                InputOption::VALUE_OPTIONAL,
                'Maximum number of images to import'
            ),
        ];

        $this->addParallelCommandOptions($options);
        $this->addTimeoutCommandOption($options);

        $this->setName('mirakl:mci:product-import-images')
            ->setDescription('Handles MCI product images import')
            ->setDefinition($options);
    }

    /**
     * Creates a Mirakl process
     *
     * @param   InputInterface  $input
     * @return  Process
     */
    private function createProcess(InputInterface $input, OutputInterface $output)
    {
        $limit = $input->getOption('limit');

        if (!$limit) {
            $limit = $this->config->getImagesImportLimit();
            if (null !== $input->getOption('limit')) {
                $output->writeln(sprintf('<comment>Bad --limit provided, using %d.</comment>', $limit));
            }
        }

        $timeout = $input->getOption('timeout');

        return $this->imageProcessHelper->createImportProcess($limit, $timeout, Process::TYPE_CLI, Process::STATUS_IDLE);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setAreaCode(Area::AREA_ADMINHTML);

        // Generate the hash to filter processes by type and name
        $hash = $this->helper->generateHash(Process::TYPE_CLI, self::PROCESS_NAME);

        // Start by cleaning up timed out processes
        $collection = $this->timeoutManager->applyTimeout($hash);

        foreach ($collection as $process) {
            /** @var Process $process */
            $output->writeln(sprintf('<info>Process #%d has been changed to timeout.</info>', $process->getId()));
        }

        // Verify that command can be executed (parallel imports)
        if (!$this->canRunCommand($input, $output, $hash)) {
            $output->writeln('<error>Cannot execute this command at the moment.</error>');

            return Cli::RETURN_FAILURE;
        }

        // Create the process that will make the images import
        $process = $this->createProcess($input, $output);

        if ($process->isStatusIdle()) {
            // Ensure that no error has occurred on process before running it
            // The IDLE status is used to prevent the automatic pending processes execution from the back office
            $process->run(true);
        }

        return Cli::RETURN_SUCCESS;
    }
}
