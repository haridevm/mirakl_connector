<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Controller\Ajax;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\LayoutFactory;
use Mirakl\Connector\Model\Offer\Shipping\Address as ShippingAddress;
use Mirakl\Connector\Model\Offer\Shipping\Methods as MiraklShippingMethods;
use Mirakl\FrontendDemo\Block\Product\Offer\ShippingMethods;

class AddressShippingMethods extends Action implements HttpGetActionInterface
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
     * @var MiraklShippingMethods
     */
    private $miraklShippingMethods;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var ShippingAddress
     */
    private $shippingAddress;

    /**
     * @param Context               $context
     * @param CustomerSession       $customerSession
     * @param JsonFactory           $resultJsonFactory
     * @param LayoutFactory         $layoutFactory
     * @param ShippingAddress       $shippingAddress
     * @param MiraklShippingMethods $miraklShippingMethods
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory,
        ShippingAddress $shippingAddress,
        MiraklShippingMethods $miraklShippingMethods
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->miraklShippingMethods = $miraklShippingMethods;
        $this->shippingAddress = $shippingAddress;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $addressId = $this->getRequest()->getParam('address_id');

        if (!$this->customerSession->isLoggedIn()) {
            $resultJson->setData(['result' => false]);

            return $resultJson;
        }

        $address = $this->shippingAddress->loadAddressById((int) $addressId);

        if (!$address || $address->getCustomerId() !== $this->customerSession->getCustomerId()) {
            $resultJson->setData(['result' => false]);

            return $resultJson;
        }


        $productSku = $this->getRequest()->getParam('product_sku');
        $offerId = $this->getRequest()->getParam('offer_id');

        $layout = $this->layoutFactory->create();

        $shippingMethods = $this->miraklShippingMethods->getShippingMethods([$productSku], $address);
        $offerShippingMethods = $shippingMethods[$productSku][$offerId] ?? null;

        $shippingMethodsBlock = $layout->createBlock(ShippingMethods::class)
                                       ->setShippingMethods($offerShippingMethods)
                                       ->toHtml();

        $resultJson->setData(['result' => $shippingMethodsBlock]);

        return $resultJson;
    }
}
