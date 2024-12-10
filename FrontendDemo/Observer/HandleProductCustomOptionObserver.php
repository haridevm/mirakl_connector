<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Mirakl\FrontendDemo\Helper\Offer as OfferHelper;

class HandleProductCustomOptionObserver implements ObserverInterface
{
    /**
     * @var OfferHelper
     */
    private $offerHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param OfferHelper $offerHelper
     * @param Registry    $registry
     */
    public function __construct(
        OfferHelper $offerHelper,
        Registry $registry
    ) {
        $this->offerHelper = $offerHelper;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $configObj = $observer->getEvent()->getData('configObj');
        $product = $this->getProduct();

        if (!$configObj || !$product) {
            return;
        }

        if ($offer = $this->offerHelper->getBestOffer($product)) {
            $config = $configObj->getConfig();
            $config['offerData'] = $this->offerHelper->getOfferData($offer);
            $configObj->setConfig($config);
        }
    }

    /**
     * @return Product|null
     */
    private function getProduct(): ?Product
    {
        return $this->registry->registry('current_product');
    }
}
