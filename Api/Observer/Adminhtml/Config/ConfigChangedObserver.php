<?php

declare(strict_types=1);

namespace Mirakl\Api\Observer\Adminhtml\Config;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\Api\Helper\Config;
use Mirakl\Api\Model\Client\Authentication\Method\OAuth2\TokenHandlerInterface;
use Psr\Log\LoggerInterface;

class ConfigChangedObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var TokenHandlerInterface
     */
    private $tokenHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config                $config
     * @param TokenHandlerInterface $tokenHandler
     * @param LoggerInterface       $logger
     */
    public function __construct(
        Config $config,
        TokenHandlerInterface $tokenHandler,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->tokenHandler = $tokenHandler;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if ($this->config->getAuthMethod() === 'oauth2') {
            try {
                // Refresh OAuth2 access token if config has changed
                $this->tokenHandler->refresh();
            } catch (\Exception $e) {
                // Reset previous token if it was valid because now the settings seem invalid
                $this->tokenHandler->reset();
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
