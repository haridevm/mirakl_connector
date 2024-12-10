<?php

declare(strict_types=1);

namespace Mirakl\Api\Observer\Client;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\Api\Model\Client\ClientSettingsInterface;
use Mirakl\Core\Client\AbstractApiClient;
use Psr\Log\LoggerInterface;

class SendRequestBeforeObserver implements ObserverInterface
{
    /**
     * @var ClientSettingsInterface
     */
    private $clientSettings;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ClientSettingsInterface $clientSettings
     * @param LoggerInterface         $logger
     */
    public function __construct(ClientSettingsInterface $clientSettings, LoggerInterface $logger)
    {
        $this->clientSettings = $clientSettings;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var AbstractApiClient $client */
            $client = $observer->getEvent()->getData('client');
            $method = $this->clientSettings->getAuthMethod();

            // Refresh API key before sending request to Mirakl in case it has changed
            $client->setApiKey($method->getAuthHeader());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
