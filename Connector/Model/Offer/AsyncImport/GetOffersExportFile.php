<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\AsyncImport;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Mirakl\Api\Helper\Async\Offer as AsyncOfferApi;
use Mirakl\Connector\Helper\Config;
use Mirakl\Connector\Model\Offer\Import\DataBuilderInterface;
use Mirakl\Connector\Model\ResourceModel\Offer as OfferResource;
use Mirakl\Connector\Model\ResourceModel\OfferFactory as OfferResourceFactory;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Action\RetryableInterface;
use Mirakl\Process\Model\Action\RetryableTrait;
use Mirakl\Process\Model\Exception\ChildProcessException;
use Mirakl\Process\Model\Exception\RetryLaterException;
use Mirakl\Process\Model\Process;

class GetOffersExportFile extends AbstractAction implements RetryableInterface
{
    use RetryableTrait;

    /**
     * @var AsyncOfferApi
     */
    private $api;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var OfferResource
     */
    private $offerResource;

    /**
     * @var DataBuilderInterface
     */
    private $dataBuilder;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @param AsyncOfferApi         $api
     * @param Config                $config
     * @param ProcessHelper         $processHelper
     * @param OfferResourceFactory  $offerResourceFactory
     * @param DataBuilderInterface  $dataBuilder
     * @param EventManagerInterface $eventManager
     * @param array                 $data
     */
    public function __construct(
        AsyncOfferApi $api,
        Config $config,
        ProcessHelper $processHelper,
        OfferResourceFactory $offerResourceFactory,
        DataBuilderInterface $dataBuilder,
        EventManagerInterface $eventManager,
        array $data = []
    ) {
        parent::__construct($data);
        $this->api = $api;
        $this->config = $config;
        $this->processHelper = $processHelper;
        $this->offerResource = $offerResourceFactory->create();
        $this->dataBuilder = $dataBuilder;
        $this->eventManager = $eventManager;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return (string) $this->_getData('key');
    }

    /**
     * @param string $key
     * @return void
     */
    public function setKey($key): void
    {
        $this->setData('key', (string) $key);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'API OF54 #' . $this->getKey();
    }

    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        if (!$process->getFile()) {
            $this->download($process, $params);
        }

        $file = $process->getFile();
        $stream = $this->processHelper->openFile($file);
        $offers = json_decode($stream->readAll(), true);

        $process->output(__('Offers count: %1', count($offers)));
        $process->output(__('Building offers data ...'), true);

        $data = [];

        foreach ($offers as $offer) {
            $data[] = $this->dataBuilder->build($offer);
        }

        $process->output(__('Importing data ...'), true);
        $this->offerResource->importData($data, $this->config->isOffersAsyncUseDirectDatabaseImport());
        $process->output(__('Done!'));

        $this->eventManager->dispatch('mirakl_offer_import_after', [
            'process' => $process,
            'file'    => $file,
            'skus'    => array_column($data, 'product_sku'),
        ]);

        $process->output(__('Removing deleted offers ...'));
        $this->offerResource->clearDeletedOffers();
        $process->output(__('Done!'));

        return [];
    }

    /**
     * @param Process $process
     * @param array   $params
     * @return void
     * @throws ChildProcessException
     * @throws RetryLaterException
     */
    private function download(Process $process, array $params = []): void
    {
        $url = $params['url'] ?? null;

        if (!$url) {
            throw new ChildProcessException($process, __('Could not find "url" in process params'));
        }

        $process->output(__('Downloading offers through API OF54 ...'));
        $process->output($url);

        try {
            $offersFile = $this->api->getOffersExportAsyncFile($url)->getFile();
            $file = $this->processHelper->saveFile($offersFile, 'json');
            $process->setFile($file);
        } catch (\Exception $e) {
            throw new RetryLaterException(
                $process,
                __('An error occurred while downloading offers: %1', $e->getMessage())
            );
        }
    }
}
