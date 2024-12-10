<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Shop\Import;

use Mirakl\Api\Helper\Shop as Api;
use Mirakl\Connector\Helper\Config;
use Mirakl\Core\Model\ResourceModel\ShopFactory as ShopResourceFactory;
use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Process;

/**
 * Mirakl shops import handler
 */
class Handler extends AbstractAction
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var ShopResourceFactory
     */
    private $shopResourceFactory;

    /**
     * @param Config              $config
     * @param Api                 $api
     * @param ShopResourceFactory $shopResourceFactory
     */
    public function __construct(
        Config $config,
        Api $api,
        ShopResourceFactory $shopResourceFactory
    ) {
        parent::__construct();
        $this->config = $config;
        $this->api = $api;
        $this->shopResourceFactory = $shopResourceFactory;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'API S20';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Process $process, ...$params): array
    {
        $since = $params['since'] ?? null;
        $full = $params['full'] ?? null;

        if (!$since && !$full) {
            $since = $this->config->getSyncDate('shops');
            $this->config->setSyncDate('shops');
        }

        $shops = $this->api->getAllShops($since);

        if ($shops->count() > 0) {
            if (!$since) {
                $process->output(__('Synchronizing shops in full mode...'));
            } else {
                $process->output(__('Synchronizing shops...'));
            }
            $this->shopResourceFactory->create()->synchronize($shops, $process);
            $process->output(__('Shops have been synchronized successfully.'));
        } else {
            if ($since) {
                $process->output(__('No shop to synchronize since %1.', $since->format('Y-m-d H:i:s')));
            } else {
                $process->output(__('No shop to synchronize.'));
            }
        }

        return ['count' => $shops->count()];
    }
}