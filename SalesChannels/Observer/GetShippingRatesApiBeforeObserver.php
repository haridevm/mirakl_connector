<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\SalesChannels\Model\Channel;
use Mirakl\SalesChannels\Model\Config;

class GetShippingRatesApiBeforeObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Channel\ResolverInterface
     */
    private $channelResolver;

    /**
     * @param Config                    $config
     * @param Channel\ResolverInterface $channelResolver
     */
    public function __construct(
        Config $config,
        Channel\ResolverInterface $channelResolver
    ) {
        $this->config = $config;
        $this->channelResolver = $channelResolver;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isChannelPricingEnabled()) {
            return;
        }

        if ($channel = $this->channelResolver->resolve()) {
            /** @var \Mirakl\MMP\Front\Request\Shipping\GetShippingRatesRequest $request */
            $request = $observer->getEvent()->getRequest();
            $request->setPricingChannelCode($channel);
        }
    }
}
