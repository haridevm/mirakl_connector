<?php
namespace Mirakl\Connector\Helper\Offer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Mirakl\Api\Helper\Offer as Api;
use Mirakl\Connector\Helper\Config;
use Mirakl\Connector\Model\ResourceModel\Offer as OfferResource;
use Mirakl\Core\Model\File\CsvFileTrait;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Process;

class Import extends AbstractHelper
{
    use CsvFileTrait;

    const CODE = 'OF51';

    const PRODUCT_SKU_POSITION_IN_CSV = 1;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var OfferResource
     */
    private $offerResource;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Context       $context
     * @param Config        $config
     * @param Api           $api
     * @param OfferResource $offerResource
     * @param ProcessHelper $processHelper
     * @param Filesystem    $filesystem
     */
    public function __construct(
        Context $context,
        Config $config,
        Api $api,
        OfferResource $offerResource,
        ProcessHelper $processHelper,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->api = $api;
        $this->offerResource = $offerResource;
        $this->processHelper = $processHelper;
        $this->filesystem = $filesystem;
    }

    /**
     * Retrieve product SKUs associated to offers present in specified CSV file
     *
     * @param   string  $fileName
     * @return  array
     */
    protected function getProductSkusFromOffersFile($fileName)
    {
        $skus = [];
        try {
            $file = $this->filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->openFile($fileName);
        } catch (FileSystemException $e) {
            return $skus;
        }

        // Retrieve product SKUs from given file
        while ($row = $this->readCsv($file)) {
            if (isset($row[self::PRODUCT_SKU_POSITION_IN_CSV])) {
                $skus[] = $row[self::PRODUCT_SKU_POSITION_IN_CSV];
            }
        }

        $file->close();

        // Remove the first value that comes from CSV headers
        array_shift($skus);

        return array_unique($skus);
    }

    /**
     * @param   Process $process
     * @param   bool    $full
     * @return  $this
     */
    public function run(Process $process, $full = false)
    {
        $since = $full ? null : $this->config->getSyncDate('offers');

        if (!$process->getFile()) {
            $this->download($process, $since);
        }

        $file = $process->getFile();
        $skus = $this->getProductSkusFromOffersFile($file);

        $this->_eventManager->dispatch('mirakl_offer_import_before', [
            'process' => $process,
            'file'    => $file,
            'skus'    => $skus,
        ]);

        $process->output(__('Importing offers...'), true);
        $affected = $this->offerResource->importFile($file, $this->config->isOffersUseDirectDatabaseImport());
        $process->output(__('Done! (total: %1)', $affected));

        $this->_eventManager->dispatch('mirakl_offer_import_after', [
            'process' => $process,
            'file'    => $file,
            'skus'    => $skus,
        ]);

        $process->output(__('Removing deleted offers...'));
        $this->offerResource->clearDeletedOffers();

        $process->output(__('Done!'));

        return $this;
    }

    /**
     * @param Process        $process
     * @param \DateTime|null $since
     * @return void
     */
    private function download(Process $process, $since)
    {
        // Save last synchronization date now if file download is too long
        $this->config->setSyncDate('offers');

        if ($since) {
            $process->output(__('Downloading offers from Mirakl since %1', $since->format('Y-m-d H:i:s')));
            // We process the date less 1 minute
            $since->sub(new \DateInterval('PT1M'));
        } else {
            $process->output(__('Downloading all offers from Mirakl'));
        }

        $offersFile = $this->api->getOffersFile($since)->getFile();
        $file = $this->processHelper->saveFile($offersFile);
        $process->setFile($file);
    }
}
