<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\AsyncImport;

use Mirakl\Api\Helper\Mcm\Async\Product as AsyncProductApi;
use Mirakl\Connector\Helper\Config;
use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Process;

class CreateProductsExport extends AbstractAction
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var AsyncProductApi
     */
    private $api;

    /**
     * @param Config          $config
     * @param AsyncProductApi $api
     * @param array           $data
     */
    public function __construct(
        Config $config,
        AsyncProductApi $api,
        array $data = []
    ) {
        parent::__construct($data);
        $this->config = $config;
        $this->api = $api;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'API CM52';
    }

    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        // We check if updated_since or updated_until parameters were provided for main synchronization process
        $mainProcess = $process->getParent();
        if ($mainProcess) {
            $mainProcessParams = $mainProcess->getParams() ?: [];
        }

        $updatedSince = $params['updated_since'] ?? ($mainProcessParams['updated_since']?? null);
        $updatedUntil = $params['updated_until'] ?? ($mainProcessParams['updated_until']?? null);

        if (!$updatedSince) {
            $updatedSince = $this->config->getSyncDate('mcm_products_import_async');
        }

        // We do not set last synchronization date if updated_until is provided
        if (!$updatedUntil) {
            $this->config->setSyncDate('mcm_products_import_async');
        }

        if ($updatedSince && $updatedUntil) {
            $process->output(__('Creating products export from Mirakl between %1 and %2', $updatedSince->format('Y-m-d H:i:s'), $updatedUntil->format('Y-m-d H:i:s')), true);
        } elseif ($updatedSince) {
            $process->output(__('Creating products export from Mirakl since %1', $updatedSince->format('Y-m-d H:i:s')), true);
        } elseif ($updatedUntil) {
            $process->output(__('Creating entire products export from Mirakl until %1', $updatedUntil->format('Y-m-d H:i:s')), true);
        } else {
            $process->output(__('Creating entire products export from Mirakl'), true);
        }

        $result = $this->api->createProductsExportAsync($updatedSince, $updatedUntil);

        $process->output(__('Done!'));
        $process->output(__('Tracking ID: %1', $result->getTrackingId()));

        return ['tracking_id' => $result->getTrackingId()];
    }
}