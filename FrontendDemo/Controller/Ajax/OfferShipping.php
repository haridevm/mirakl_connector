<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\LayoutFactory;
use Mirakl\Connector\Model\Offer\Shipping\Address as ShippingAddress;
use Mirakl\Connector\Model\Offer\Shipping\Methods;
use Mirakl\FrontendDemo\Block\Product\Offer\BestShippingMethod;
use Mirakl\FrontendDemo\Block\Product\Offer\ShippingAddresses;
use Mirakl\FrontendDemo\Block\Product\Offer\ShippingMethods;

class OfferShipping extends Action implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Methods
     */
    private $shippingMethods;

    /**
     * @var ShippingAddress
     */
    private $shippingAddress;

    /**
     * @param Context         $context
     * @param JsonFactory     $resultJsonFactory
     * @param LayoutFactory   $layoutFactory
     * @param Json            $json
     * @param Methods         $shippingMethods
     * @param ShippingAddress $shippingAddress
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory,
        Json $json,
        Methods $shippingMethods,
        ShippingAddress $shippingAddress
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->json = $json;
        $this->shippingMethods = $shippingMethods;
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $skus = $this->getRequest()->getParam('product_skus');
        $skus = $this->json->unserialize($skus);

        if (!$skus) {
            $resultJson->setData(['result' => false]);

            return $resultJson;
        }

        $defaultAddress = $this->shippingAddress->getCustomerDefaultShippingAddress();
        $customerAddresses = $this->shippingAddress->getCustomerShippingAddresses();
        $shippingMethodsBySku = $this->shippingMethods->getShippingMethods($skus, $defaultAddress);

        if (!$shippingMethodsBySku) {
            $resultJson->setData(['result' => false]);

            return $resultJson;
        }

        $layout = $this->layoutFactory->create();
        $result = [];

        foreach ($shippingMethodsBySku as $sku => $offersShippingMethods) {
            foreach ($offersShippingMethods as $offerId => $offerShippingMethods) {
                if ($offerShippingMethods) {
                    $bestShippingMethodsByPrice = $this->shippingMethods->getBestShippingMethodByPrice(
                        $offerShippingMethods
                    );
                    $bestShippingMethodsByDate = $this->shippingMethods->getBestShippingMethodByDate(
                        $offerShippingMethods
                    );

                    $shippingMethodsBlock = $layout->createBlock(ShippingMethods::class);
                    $shippingMethodsBlock->setShippingMethods($offerShippingMethods);
                    $shippingMethodsBlock->setShippingAddress($defaultAddress);

                    $bestShippingMethodsBlock = $layout->createBlock(BestShippingMethod::class);
                    $bestShippingMethodsBlock->setBestShippingMethodByPrice($bestShippingMethodsByPrice);
                    $bestShippingMethodsBlock->setBestShippingMethodByDate($bestShippingMethodsByDate);
                    $bestShippingMethodsBlock->setShippingAddress($defaultAddress);

                    $result[$offerId]['offer_shipping'] = $shippingMethodsBlock->toHtml();
                    $result[$offerId]['best_offer_shipping'] = $bestShippingMethodsBlock->toHtml();

                    $customerAddressesBlock = $layout->createBlock(ShippingAddresses::class);
                    $customerAddressesBlock->setProductSku($sku);
                    $customerAddressesBlock->setOfferId($offerId);
                    $customerAddressesBlock->setCustomerDefaultAddress($defaultAddress);
                    $customerAddressesBlock->setCustomerAddresses($customerAddresses);

                    $result[$offerId]['customer_addresses'] = $customerAddressesBlock->toHtml();
                }
            }
        }

        $resultJson->setData(['result' => $result]);

        return $resultJson;
    }
}
