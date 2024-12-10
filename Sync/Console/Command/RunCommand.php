<?php

declare(strict_types=1);

namespace Mirakl\Sync\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Module\Manager as ModuleManager;
use Mirakl\Catalog\Helper\Config as CatalogConfigHelper;
use Mirakl\Connector\Helper\Config as ConnectorConfig;
use Mirakl\Core\Console\Command\CommandTrait;
use Mirakl\Mci\Helper\Config as MciConfigHelper;
use Mirakl\Mcm\Helper\Config as McmConfigHelper;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;
use Mirakl\Sync\Model\Sync\Script;
use Mirakl\Sync\Model\Sync\ScriptFactory;
use Mirakl\Sync\Model\Sync\Script\CollectionFactory as ScriptCollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RunCommand extends Command
{
    use CommandTrait;

    public const LIST_SCRIPTS_OPTION   = 'list';
    public const RUN_SCRIPT_OPTION     = 'run';
    public const FULL_OPTION           = 'full';
    public const DELETED_SINCE_OPTION  = 'deleted-since';

    /**
     * @var ScriptFactory
     */
    protected $scriptFactory;

    /**
     * @var ScriptCollectionFactory
     */
    protected $scriptCollectionFactory;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var ProcessFactory
     */
    protected $processFactory;

    /**
     * @var ProcessResourceFactory
     */
    protected $processResourceFactory;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var ConnectorConfig
     */
    protected $connectorConfig;

    /**
     * @var array
     */
    public static $scripts = [
        'Mirakl_Connector' => [
            [
                'code'   => \Mirakl\Connector\Helper\Shop::CODE,
                'title'  => 'Import Mirakl shops into Magento',
                'helper' => 'Mirakl\Connector\Helper\Shop',
                'method' => 'synchronize',
            ],
            [
                'code'   => \Mirakl\Connector\Helper\Offer\Import::CODE,
                'title'  => 'Import Mirakl offers into Magento (legacy)',
                'helper' => 'Mirakl\Connector\Helper\Offer\Import',
                'method' => 'run',
                'config' => [ConnectorConfig::XML_PATH_OFFERS_IMPORT_ENABLE => 1],
            ],
            [
                'code'   => \Mirakl\Connector\Model\Offer\AsyncImport\Import::CODE,
                'title'  => 'Import Mirakl offers into Magento',
                'helper' => 'Mirakl\Connector\Model\Offer\AsyncImport\Import',
                'method' => 'execute',
                'config' => [ConnectorConfig::XML_PATH_OFFERS_IMPORT_ASYNC_ENABLE => 1],
            ],
        ],
        'Mirakl_Catalog' => [
            [
                'code'   => \Mirakl\Catalog\Helper\Category::CODE,
                'title'  => 'Export enabled marketplace categories to Mirakl',
                'helper' => 'Mirakl\Catalog\Helper\Category',
                'method' => 'exportAll',
                'config' => [CatalogConfigHelper::XML_PATH_ENABLE_SYNC_CATEGORIES => 1],
            ],
            [
                'code'   => \Mirakl\Catalog\Helper\Product::CODE,
                'title'  => 'Export enabled products to Mirakl',
                'helper' => 'Mirakl\Catalog\Helper\Product',
                'method' => 'exportAll',
                'config' => [CatalogConfigHelper::XML_PATH_ENABLE_SYNC_PRODUCTS => 1],
            ],
        ],
        'Mirakl_Mci' => [
            [
                'code'   => \Mirakl\Mci\Helper\ValueList::CODE,
                'title'  => 'Export all attribute value lists to Mirakl',
                'helper' => 'Mirakl\Mci\Helper\ValueList',
                'method' => 'exportAttributes',
                'config' => [MciConfigHelper::XML_PATH_ENABLE_SYNC_VALUES_LISTS => 1],
            ],
            [
                'code'   => \Mirakl\Mci\Helper\Hierarchy::CODE,
                'title'  => 'Export all Catalog categories to Mirakl',
                'helper' => 'Mirakl\Mci\Helper\Hierarchy',
                'method' => 'exportAll',
                'config' => [MciConfigHelper::XML_PATH_ENABLE_SYNC_HIERARCHIES => 1],
            ],
            [
                'code'   => \Mirakl\Mci\Helper\Attribute::CODE,
                'title'  => 'Export all attributes to Mirakl',
                'helper' => 'Mirakl\Mci\Helper\Attribute',
                'method' => 'exportAll',
                'config' => [MciConfigHelper::XML_PATH_ENABLE_SYNC_ATTRIBUTES => 1],
            ],
        ],
        'Mirakl_Mcm' => [
            [
                'code'   => \Mirakl\Mcm\Helper\Product\Export\Process::CODE,
                'title'  => 'Export all operator products to Mirakl',
                'helper' => 'Mirakl\Mcm\Helper\Product\Export\Process',
                'method' => 'exportAll',
                'config' => [McmConfigHelper::XML_PATH_ENABLE_SYNC_MCM_PRODUCTS => 1],
            ],
            [
                'code'   => \Mirakl\Mcm\Model\Product\Delete\Handler::CODE,
                'title'  => 'Remove deleted MCM products from Magento',
                'helper' => 'Mirakl\Mcm\Model\Product\Delete\Handler',
                'method' => 'run',
                'config' => [McmConfigHelper::XML_PATH_ENABLE_DELETE_MCM_PRODUCTS => 1],
            ],
            [
                'code'   => \Mirakl\Core\Model\Shipping\Type\Synchronizer::CODE,
                'title'  => 'Import all active shipping methods in Magento',
                'helper' => 'Mirakl\Core\Model\Shipping\Type\Synchronizer',
                'method' => 'synchronize',
            ],
        ],
    ];

    /**
     * @param ScriptFactory           $scriptFactory
     * @param ScriptCollectionFactory $scriptCollectionFactory
     * @param State                   $state
     * @param ProcessFactory          $processFactory
     * @param ProcessResourceFactory  $processResourceFactory
     * @param ModuleManager           $moduleManager
     * @param ConnectorConfig         $connectorConfig
     * @param null                    $name
     */
    public function __construct(
        ScriptFactory $scriptFactory,
        ScriptCollectionFactory $scriptCollectionFactory,
        State $state,
        ProcessFactory $processFactory,
        ProcessResourceFactory $processResourceFactory,
        ModuleManager $moduleManager,
        ConnectorConfig $connectorConfig,
        $name = null
    ) {
        parent::__construct($name);
        $this->scriptFactory = $scriptFactory;
        $this->scriptCollectionFactory = $scriptCollectionFactory;
        $this->appState = $state;
        $this->processFactory = $processFactory;
        $this->processResourceFactory = $processResourceFactory;
        $this->moduleManager = $moduleManager;
        $this->connectorConfig = $connectorConfig;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::LIST_SCRIPTS_OPTION,
                null,
                InputOption::VALUE_NONE,
                'List synchronization scripts'
            ),
            new InputOption(
                self::RUN_SCRIPT_OPTION,
                null,
                InputOption::VALUE_OPTIONAL,
                'Execute a synchronization script by its code'
            ),
            new InputOption(
                self::FULL_OPTION,
                null,
                InputOption::VALUE_NONE,
                'Execute in full mode (only for S20 synchronization)'
            ),
            new InputOption(
                self::DELETED_SINCE_OPTION,
                null,
                InputOption::VALUE_OPTIONAL,
                'Remove deleted MCM products since date (yyyy-mm-dd)'
            ),
        ];

        $this->setName('mirakl:sync')
            ->setDescription('Handles synchronization scripts between Magento and the Mirakl platform')
            ->setDefinition($options);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setAreaCode(Area::AREA_GLOBAL);

        if ($input->getOption('list')) {
            /** @var Script $script */
            foreach ($this->getScripts() as $script) {
                $output->writeln(sprintf(
                    '<info>%-6s</info> %s%s',
                    $script->getCode(),
                    $script->getTitle(),
                    $script->isSyncDisable() ? ' <fg=red>[disabled]</>' : ''
                ));
            }
        } elseif ($code = $input->getOption('run')) {
            $script = $this->getScripts()->getItemById($code);

            if (!$script) {
                throw new \InvalidArgumentException('Invalid script code specified.');
            }

            if ($script->isSyncDisable()) {
                $output->writeln(sprintf('<fg=red>Synchronization is disabled for %s</>', $script->getCode()));

                return Cli::RETURN_FAILURE;
            }

            /** @var Process $process */
            $process = $this->processFactory->create();
            $process->setStatus(Process::STATUS_PENDING)
                ->setType(Process::TYPE_CLI)
                ->setCode($script->getCode())
                ->setName($script->getCode() . ' synchronization script')
                ->setHelper($script->getHelper())
                ->setMethod($script->getMethod())
                ->setParams($this->getParams($script->getCode(), $input));

            $this->processResourceFactory->create()->save($process);

            $process->addOutput('cli');
            $process->run();
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @param string         $code
     * @return array
     */
    public function getParams($code, InputInterface $input)
    {
        switch ($code) {
            case \Mirakl\Connector\Helper\Shop::CODE:
                $params = [];
                if (!$input->getOption(self::FULL_OPTION)) {
                    $params[] = $this->connectorConfig->getSyncDate('shops');
                    $this->connectorConfig->setSyncDate('shops');
                }

                return $params;
            case \Mirakl\Mcm\Model\Product\Delete\Handler::CODE:
                $deletedFrom = $input->getOption(self::DELETED_SINCE_OPTION);
                if ($deletedFrom && preg_match('(^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$)', $deletedFrom)) {
                    $datetime = new \DateTime();
                    $params[] = $datetime::createFromFormat('Y-m-d H:i:s', $deletedFrom . ' 00:00:00');
                } else {
                    $params[] = $this->connectorConfig->getSyncDate('mcm_products_delete');
                }
                $this->connectorConfig->setSyncDate('mcm_products_delete');

                return $params;
            default:
                return [];
        }
    }

    /**
     * @return Script\Collection
     */
    protected function getScripts()
    {
        /** @var Script\Collection $scripts */
        $collection = $this->scriptCollectionFactory->create();

        foreach (static::$scripts as $moduleName => $scripts) {
            if ($this->moduleManager->isEnabled($moduleName)) {
                foreach ($scripts as $data) {
                    $collection->addItem($this->scriptFactory->create()->setData($data));
                }
            }
        }

        return $collection;
    }
}
