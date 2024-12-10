<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Block\Product\Offer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address\Config;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Store\Model\StoreManagerInterface;
use Mirakl\Connector\Model\Offer\Shipping\Address as OfferShippingAddress;
use Mirakl\Core\Helper\Config as CoreConfig;

/**
 * @method int          getOfferId
 * @method $this        setOfferId(int $offerId)
 * @method string       getProductSku()
 * @method $this        setProductSku(string $productSku)
 * @method array        getCustomerAddresses()
 * @method $this        setCustomerAddresses(array $customerAddresses)
 * @method QuoteAddress getCustomerDefaultAddress()
 * @method $this        setCustomerDefaultAddress($customerAddresses)
 */
class ShippingAddresses extends Template
{
    /**
     * @var OfferShippingAddress
     */
    private $shippingAddress;

    /**
     * @var CoreConfig
     */
    private $coreConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $addressConfig;

    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_template = 'product/offer/shipping_addresses.phtml';

    /**
     * @param Template\Context      $context
     * @param OfferShippingAddress  $shippingAddress
     * @param CoreConfig            $coreConfig
     * @param StoreManagerInterface $storeManager
     * @param Config                $addressConfig
     * @param array                 $data
     */
    public function __construct(
        Template\Context $context,
        OfferShippingAddress $shippingAddress,
        CoreConfig $coreConfig,
        StoreManagerInterface $storeManager,
        Config $addressConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->shippingAddress = $shippingAddress;
        $this->coreConfig = $coreConfig;
        $this->storeManager = $storeManager;
        $this->addressConfig = $addressConfig;
    }

    /**
     * @param AddressInterface $address
     * @return bool
     */
    public function isDefaultShippingAddress(AddressInterface $address): bool
    {
        return $this->shippingAddress->isSameAddress(
            $this->getCustomerDefaultAddress(),
            $address
        );
    }

    /**
     * Format customer address in one line
     *
     * @param AddressInterface $address
     * @return string
     */
    public function formatAddress(AddressInterface $address): string
    {
        $locale = $this->coreConfig->getLocale($this->storeManager->getStore());
        $renderer = $this->addressConfig->getFormatByCode(ElementFactory::OUTPUT_FORMAT_ONELINE)
                                        ->getRenderer();

        $formattedAddress = $renderer->renderArray([
            'locale'     => $locale,
            'city'       => $address->getCity(),
            'street'     => $address->getStreet(),
            'postcode'   => $address->getPostcode(),
            'region_id'  => $address->getRegionId(),
            'country_id' => $address->getCountryId(),
        ]);

        return trim($formattedAddress, ' ,');
    }
}
