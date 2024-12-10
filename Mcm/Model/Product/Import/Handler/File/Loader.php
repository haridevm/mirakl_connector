<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Handler\File;

use Mirakl\Api\Helper\Mcm\Product as Api;
use Mirakl\Connector\Helper\Config as ConnectorConfig;
use Mirakl\Core\Helper\Data as CoreHelper;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Process;

class Loader
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var ConnectorConfig
     */
    protected $connectorConfig;

    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    /**
     * @var ProcessHelper
     */
    protected $processHelper;

    /**
     * @param Api $api
     * @param ConnectorConfig $connectorConfig
     * @param CoreHelper $coreHelper
     * @param ProcessHelper $processHelper
     */
    public function __construct(
        Api $api,
        ConnectorConfig $connectorConfig,
        CoreHelper $coreHelper,
        ProcessHelper $processHelper
    ) {
        $this->api = $api;
        $this->connectorConfig = $connectorConfig;
        $this->coreHelper = $coreHelper;
        $this->processHelper = $processHelper;
    }

    /**
     * @param Process $process
     * @param \DateTime|null $since
     * @param \DateTime|null $until
     * @return string|null
     */
    public function load(Process $process, ?\DateTime $since, ?\DateTime $until = null): ?string
    {
        if ($file = $process->getFile()) {
            return $file;
        }

        if (!$since && ($lastSyncDate = $this->connectorConfig->getSyncDate('mcm_products_import'))) {
            $since = $lastSyncDate;
        }

        if (!$until) {
            // Save last synchronization date now if file download is too long
            $this->connectorConfig->setSyncDate('mcm_products_import');
        }

        $importParams = [];
        $downloadingMessage = 'Downloading MCM products from Mirakl to Magento';
        $downloadingMessageParams = [];
        if ($since) {
            $downloadingMessage .= ' since %1';
            $downloadingMessageParams[] = $since->format('Y-m-d H:i:s');
            $importParams['updated_since'] = $since->format('Y-m-d\TH:i:sP');
        }
        if ($until) {
            $downloadingMessage .= $since ? ' to %2' : ' until %1';
            $downloadingMessageParams[] = $until->format('Y-m-d H:i:s');
            $importParams['updated_to'] = $until->format('Y-m-d\TH:i:sP');
        }
        $process->output(__($downloadingMessage, $downloadingMessageParams), true);

        $apiFile = $this->api->import($importParams);

        $file = null;
        if ($apiFile->fstat()['size'] > 0 && ($file = $this->processHelper->saveFile($apiFile))) {
            $fileSize = $this->coreHelper->formatSize($this->processHelper->getFileSize($file));
            $process->output(__('File has been saved as "%1" (%2)', basename($file), $fileSize));
            $process->setFile($file);
        }

        if (empty($file)) {
            $process->output(__('No products to import'));
        }

        return $file;
    }
}
