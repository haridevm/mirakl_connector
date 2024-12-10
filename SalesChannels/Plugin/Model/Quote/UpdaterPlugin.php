<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Plugin\Model\Quote;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Mirakl\Connector\Helper\Quote as QuoteHelper;
use Mirakl\Connector\Model\Quote\Updater;
use Mirakl\SalesChannels\Model\Channel;
use Mirakl\SalesChannels\Model\Config;

class UpdaterPlugin
{
    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Channel\ResolverInterface
     */
    private $channelResolver;

    /**
     * @param QuoteHelper               $quoteHelper
     * @param Config                    $config
     * @param Channel\ResolverInterface $channelResolver
     */
    public function __construct(
        QuoteHelper $quoteHelper,
        Config $config,
        Channel\ResolverInterface $channelResolver
    ) {
        $this->quoteHelper = $quoteHelper;
        $this->config = $config;
        $this->channelResolver = $channelResolver;
    }

    /**
     * @param Updater       $subject
     * @param CartInterface $quote
     * @return void
     */
    public function beforeSynchronize(Updater $subject, CartInterface $quote)
    {
        /** @var Quote $quote */
        $quote->setMiraklChannelCode(null);

        if ($this->quoteHelper->isMiraklQuote($quote) && $this->config->isChannelPricingEnabled()) {
            $channel = $this->channelResolver->resolve((int) $quote->getStoreId());
            $quote->setMiraklChannelCode($channel);
        }
    }
}
