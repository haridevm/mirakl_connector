<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Connector;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Mirakl\Api\Helper\Config as ApiConfig;

class IsFreshInstall implements IsFreshInstallInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        // Connector fresh install => Mirakl API URL not set yet
        return !$this->scopeConfig->getValue(ApiConfig::XML_PATH_API_URL);
    }
}
