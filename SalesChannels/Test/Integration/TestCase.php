<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration;

use Magento\Store\Api\StoreRepositoryInterface;
use Mirakl\Connector\Model\Offer\AsyncImport\GetOffersExportFile as OffersImport;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Process;
use Mirakl\SalesChannels\Model\Config;

class TestCase extends \Mirakl\Core\Test\Integration\TestCase
{
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        $this->config = $this->objectManager->get(Config::class);
    }

    /**
     * @return string
     */
    protected function getFilesDir()
    {
        return realpath(__DIR__ . '/_files');
    }

    /**
     * @return void
     */
    protected function importSampleOffers(): void
    {
        /** @var Process $process */
        $process = $this->objectManager->create(Process::class);
        $process->setType('TEST OF54 OFFERS IMPORT')
            ->setName('Test offer data overrider')
            ->setStatus(Process::STATUS_IDLE)
            ->setHelper(OffersImport::class)
            ->setMethod('execute');

        /** @var ProcessHelper $processHelper */
        $processHelper = $this->objectManager->create(ProcessHelper::class);
        $jsonFile = $this->getFilePath('OF54_sample_offers_input_with_channel_pricing.json');
        $file = $processHelper->saveFile($jsonFile, 'json');

        $process->setFile($file);
        $process->setQuiet(true);
        $process->run(true);
    }
}
