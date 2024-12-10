<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Observer\Api;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\Core\Client\AbstractApiClient;
use Mirakl\Mcm\Helper\Config;

class AddProductsImportUserAgentObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var AbstractApiClient $client */
        $client = $observer->getEvent()->getClient();
        $userAgent = $client->getUserAgent();
        $userAgent .= sprintf(' CM51-Import-Mode/%s', $this->config->getProductsImportMode());
        $client->setUserAgent($userAgent);
    }
}
