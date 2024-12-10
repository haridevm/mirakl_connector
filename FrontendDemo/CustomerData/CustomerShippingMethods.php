<?php
namespace Mirakl\FrontendDemo\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Mirakl\FrontendDemo\Model\Offer\Shipping as OfferShipping;

class CustomerShippingMethods implements SectionSourceInterface
{
    /**
     * @var OfferShipping
     */
    private $offerShipping;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param OfferShipping          $offerShipping
     * @param CookieManagerInterface $cookieManager
     * @param Json                   $json
     */
    public function __construct(
        OfferShipping $offerShipping,
        CookieManagerInterface $cookieManager,
        Json $json
    ) {
        $this->offerShipping = $offerShipping;
        $this->cookieManager = $cookieManager;
        $this->json = $json;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $shippingOfferIds = $this->cookieManager->getCookie('offer_ids');
        if (!$shippingOfferIds) {
            return [];
        }
        $shippingOfferIds = $this->json->unserialize($shippingOfferIds);

        return [
            'customer-shipping-methods' => $this->offerShipping->getCustomerShippingEstimation($shippingOfferIds)
        ];
    }
}