<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Model\Channel;

use Magento\Store\Model\StoreManagerInterface;
use Mirakl\SalesChannels\Model\Config;

class Resolver implements ResolverInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $channelMapping = [];

    /**
     * @param StoreManagerInterface $storeManager
     * @param Config                $config
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * The default channel resolver behavior is to get the channels mapping from config
     * and verify if a Mirakl channel is associated to the specified store view.
     * If yes, the method returns the Mirakl channel code, null otherwise.
     *
     * @inheritdoc
     */
    public function resolve(int $storeId = null): ?string
    {
        $store = $this->storeManager->getStore($storeId);
        $storeCode = $store->getCode();

        if (!isset($this->channelMapping[$storeCode])) {
            $channelMapping = $this->config->getChannelMapping();
            $miraklChannel = $channelMapping[$storeCode]['channel_code'] ?? null;
            $this->channelMapping[$storeCode] = $miraklChannel ?: null;
        }

        return $this->channelMapping[$storeCode];
    }
}
