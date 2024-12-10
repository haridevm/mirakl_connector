<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\StoreSwitcher;

use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcherInterface;
use Mirakl\Api\Helper\Config as ApiConfig;
use Mirakl\FrontendDemo\Helper\Quote as QuoteHelper;
use Mirakl\FrontendDemo\Model\Quote\Updater as QuoteUpdater;

class MiraklQuoteSynchronizer implements StoreSwitcherInterface
{
    /**
     * @var ApiConfig
     */
    private $apiConfig;

    /**
     * @var QuoteHelper
     */
    private $quoteHelper;

    /**
     * @var QuoteUpdater
     */
    private $quoteUpdater;

    /**
     * @param ApiConfig    $apiConfig
     * @param QuoteHelper  $quoteHelper
     * @param QuoteUpdater $quoteUpdater
     */
    public function __construct(
        ApiConfig $apiConfig,
        QuoteHelper $quoteHelper,
        QuoteUpdater $quoteUpdater
    ) {
        $this->apiConfig = $apiConfig;
        $this->quoteHelper = $quoteHelper;
        $this->quoteUpdater = $quoteUpdater;
    }

    /**
     * @inheritdoc
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        if ($this->apiConfig->isEnabled()) {
            $this->quoteUpdater->synchronize($this->getQuote());
        }

        return $redirectUrl;
    }

    /**
     * @return Quote
     */
    private function getQuote(): Quote
    {
        return $this->quoteHelper->getQuote();
    }
}
