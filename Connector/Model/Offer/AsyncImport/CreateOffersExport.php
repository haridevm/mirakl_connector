<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer\AsyncImport;

use Mirakl\Api\Helper\Async\Offer as AsyncOfferApi;
use Mirakl\Connector\Helper\Config;
use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Process;

class CreateOffersExport extends AbstractAction
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var AsyncOfferApi
     */
    private $api;

    /**
     * @param Config        $config
     * @param AsyncOfferApi $api
     * @param array         $data
     */
    public function __construct(
        Config $config,
        AsyncOfferApi $api,
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
        return 'API OF52';
    }

    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        $since = $this->config->getSyncDate('offers_async');
        $this->config->setSyncDate('offers_async');

        if ($since) {
            $process->output(__('Creating offers export from Mirakl since %1', $since->format('Y-m-d H:i:s')), true);
            // We process the date less 1 minute
            $since->sub(new \DateInterval('PT1M'));
        } else {
            $process->output(__('Creating entire offers export from Mirakl'), true);
        }

        $result = $this->api->createOffersExportAsync($since);

        $process->output(__('Done!'));
        $process->output(__('Tracking ID: %1', $result->getTrackingId()));

        return ['tracking_id' => $result->getTrackingId()];
    }
}
