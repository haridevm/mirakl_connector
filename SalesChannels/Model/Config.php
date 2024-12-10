<?php
declare(strict_types=1);

namespace Mirakl\SalesChannels\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Config
{
    const XML_PATH_CHANNEL_PRICING_ENABLED = 'mirakl_connector/sales_channels/enable_channel_pricing';
    const XML_PATH_STORE_CHANNEL_MAPPING   = 'mirakl_connector/sales_channels/mirakl_channels';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     */
    public function __construct(ScopeConfigInterface $scopeConfig, Json $json)
    {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
    }

    /**
     * Returns Magento stores and Mirakl channels mapping
     *
     * @return array
     */
    public function getChannelMapping(): array
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_STORE_CHANNEL_MAPPING) ?: '[]';

        return $this->json->unserialize($value);
    }

    /**
     * Returns true if channel pricing is enabled
     *
     * @return bool
     */
    public function isChannelPricingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CHANNEL_PRICING_ENABLED);
    }
}
