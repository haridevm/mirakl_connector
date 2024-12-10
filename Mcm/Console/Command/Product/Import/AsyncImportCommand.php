<?php
namespace Mirakl\Mcm\Console\Command\Product\Import;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Mirakl\Connector\Helper\Config as ConnectorConfig;
use Mirakl\Core\Console\Command\CommandTrait;
use Mirakl\Mcm\Helper\Config as McmConfig;
use Mirakl\Mcm\Model\Product\AsyncImport\Import as AsyncImport;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\Process as ProcessResource;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AsyncImportCommand extends Command
{
    use CommandTrait;

    const UPDATED_SINCE_OPTION = 'since';
    const UPDATED_UNTIL_OPTION = 'until';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ConfigInterface
     */
    private $configManager;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ConnectorConfig
     */
    private $connectorConfig;

    /**
     * @var McmConfig
     */
    private $mcmConfig;

    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var ProcessResource
     */
    private $processResource;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface        $configManager
     * @param State                  $state
     * @param ConnectorConfig        $connectorConfig
     * @param McmConfig              $mcmConfig
     * @param ProcessFactory         $processFactory
     * @param ProcessResource        $processResource
     * @param string                 $name
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $configManager,
        State $state,
        ConnectorConfig $connectorConfig,
        McmConfig $mcmConfig,
        ProcessFactory $processFactory,
        ProcessResource $processResource,
        $name = null
    ) {
        parent::__construct($name);
        $this->objectManager   = $objectManager;
        $this->configManager   = $configManager;
        $this->appState        = $state;
        $this->connectorConfig = $connectorConfig;
        $this->mcmConfig       = $mcmConfig;
        $this->processFactory  = $processFactory;
        $this->processResource = $processResource;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::UPDATED_SINCE_OPTION,
                null,
                InputOption::VALUE_OPTIONAL,
                'MCM: Export starting date. Given date must respect ISO-8601 format. Example: 2023-10-25T15:15+00Z',
                null
            ),
            new InputOption(
                self::UPDATED_UNTIL_OPTION,
                null,
                InputOption::VALUE_OPTIONAL,
                'MCM: Export end date. Given date must respect ISO-8601 format. Example: 2023-10-25T15:15+00Z (Last Synchronization Date will not be changed with this parameter)',
                null
            ),
        ];

        $this->setName('mirakl:mcm:product:async-import')
            ->setDescription('Handles Mirakl MCM product asynchronous import')
            ->setDefinition($options);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initAuthorization();
        $this->setAreaCode(Area::AREA_ADMINHTML);

        if (!$this->mcmConfig->isAsyncMcmEnabled()) {
            $output->writeln('Mirakl MCM is not activated in your configuration');
        } else {
            $output->writeln('Importing MCM products asynchronously...');
            $updatedSince = $input->getOption(self::UPDATED_SINCE_OPTION);
            $updatedUntil = $input->getOption(self::UPDATED_UNTIL_OPTION);

            $updatedSince = $updatedSince ? new \DateTime($updatedSince) : null;
            $updatedUntil = $updatedUntil ? new \DateTime($updatedUntil) : null;

            $params = [
                'updated_since' => $updatedSince,
                'updated_until' => $updatedUntil,
            ];

            /** @var Process $process */
            $process = $this->processFactory->create();
            $process->setType(Process::TYPE_IMPORT_MCM)
                    ->setStatus(Process::STATUS_PENDING)
                    ->setName('MCM products asynchronous import')
                    ->setHelper(AsyncImport::class)
                    ->setCode(AsyncImport::CODE)
                    ->setParams($params)
                    ->setMethod('execute');

            $this->processResource->save($process);

            $process->run();
        }

        return Cli::RETURN_SUCCESS;
    }
}
