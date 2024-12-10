<?php
declare(strict_types=1);

namespace Mirakl\SalesChannels\Model\Indexer\Offer\Collection\Customizer;

use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection as OfferCollection;
use Mirakl\OfferIndexer\Model\Indexer\Offer\Collection\Customizer\CustomizerInterface;
use Mirakl\SalesChannels\Model\Channel;
use Mirakl\SalesChannels\Model\Config;
use Mirakl\SalesChannels\Model\Offer\ChannelOfferInterface;

class SalesChannels implements CustomizerInterface
{
    /**
     * @var ConfigInterface
     */
    private $objectManagerConfig;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Channel\ResolverInterface
     */
    private $channelResolver;

    /**
     * @param ConfigInterface $objectManagerConfig
     * @param Config $config
     * @param Channel\ResolverInterface $channelResolver
     */
    public function __construct(
        ConfigInterface $objectManagerConfig,
        Config $config,
        Channel\ResolverInterface $channelResolver
    ) {
        $this->objectManagerConfig = $objectManagerConfig;
        $this->config = $config;
        $this->channelResolver = $channelResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function customize(OfferCollection $collection, StoreInterface $store): void
    {
        if (!$this->config->isChannelPricingEnabled()) {
            return;
        }

        $channel = $this->channelResolver->resolve((int) $store->getId());

        if (null !== $channel) {
            $select = $collection->getSelect();
            $select->columns(['channel' => new \Zend_Db_Expr($select->getConnection()->quote($channel))]);
            $class = $this->objectManagerConfig->getPreference(ChannelOfferInterface::class);
            $collection->setItemObjectClass($class);
        }
    }
}
