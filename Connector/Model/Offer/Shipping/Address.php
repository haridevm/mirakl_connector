<?php

namespace Mirakl\Connector\Model\Offer\Shipping;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\AddressFactory as QuoteAddressFactory;
use Mirakl\Connector\Model\Quote\Synchronizer as MiraklQuoteSynchronizer;
use Mirakl\Core\Exception\ShippingZoneNotFound;
use Mirakl\Core\Helper\ShippingZone as ShippingZoneHelper;

class Address
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ShippingZoneHelper
     */
    private $shippingZoneHelper;

    /**
     * @var MiraklQuoteSynchronizer
     */
    private $quoteSynchronizer;

    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param CheckoutSession            $checkoutSession
     * @param ShippingZoneHelper         $shippingZoneHelper
     * @param MiraklQuoteSynchronizer    $quoteSynchronizer
     * @param QuoteAddressFactory        $quoteAddressFactory
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ShippingZoneHelper $shippingZoneHelper,
        MiraklQuoteSynchronizer $quoteSynchronizer,
        QuoteAddressFactory $quoteAddressFactory,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->shippingZoneHelper = $shippingZoneHelper;
        $this->quoteSynchronizer = $quoteSynchronizer;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Load customer shipping address by id
     *
     * @param int $addressId
     * @return AddressInterface|null
     */
    public function loadAddressById(int $addressId)
    {
        try {
            $address = $this->addressRepository->getById($addressId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $address;
    }

    /**
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getCustomerDefaultShippingAddress()
    {
        $quote = $this->checkoutSession->getQuote();

        return $this->quoteSynchronizer->getQuoteShippingAddress($quote);
    }

    /**
     * @param QuoteAddress $customerShippingAddress
     * @return string|null
     */
    public function getShippingZoneCode(QuoteAddress $customerShippingAddress)
    {
        try {
            $shippingZoneCode = $this->shippingZoneHelper->getShippingZoneCode($customerShippingAddress);
        } catch (ShippingZoneNotFound $e) {
            return null;
        }

        return $shippingZoneCode;
    }


    /**
     * Check if a customer quote address has a corresponding Mirakl shipping zone
     *
     * @param QuoteAddress $quoteAddress
     * @return bool
     */
    public function isAddressHasShippingZone(QuoteAddress $quoteAddress): bool
    {
        return $this->getShippingZoneCode($quoteAddress) !== null;
    }

    /**
     * Return customer addresses if customer is logged in
     *
     * @return array
     */
    public function getCustomerShippingAddresses()
    {
        $quote = $this->checkoutSession->getQuote();
        $customer = $quote->getCustomer();

        return $customer->getAddresses();
    }

    /**
     * Converts customer address to quote address for shipping zone validation
     *
     * @param AddressInterface $shippingAddress
     * @return QuoteAddress
     */
    public function convertToQuoteAddress($shippingAddress)
    {
        $quoteAddress = $this->quoteAddressFactory->create();
        $quoteAddress->setData([
            'customer_address_id' => $shippingAddress->getId(),
            'postcode'            => $shippingAddress->getPostcode(),
            'region'              => $shippingAddress->getRegion(),
            'region_id'           => $shippingAddress->getRegionId(),
            'country_id'          => $shippingAddress->getCountryId(),
            'city'                => $shippingAddress->getCity(),
            'street'              => $shippingAddress->getStreet(),
        ]);

        return $quoteAddress;
    }

    /**
     * Checks whether a quote address is same as another address object
     *
     * @param QuoteAddress     $address1
     * @param AddressInterface $address2
     * @return bool
     */
    public function isSameAddress(QuoteAddress $address1, $address2)
    {
        return $address1->getRegionId() === $address2->getRegionId()
                && $address1->getPostcode() === $address2->getPostcode()
                && $address1->getStreet() === $address2->getStreet()
                && $address1->getCity() === $address2->getCity()
                && $address1->getCountryId() === $address2->getCountryId();
    }
}
