<?php

declare(strict_types=1);

namespace Mirakl\Connector\Test\Unit\Model\Offer\Shipping;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Quote\Model\Quote\AddressFactory as QuoteAddressFactory;
use Mirakl\Connector\Model\Offer\Shipping\Address;
use Mirakl\Connector\Model\Quote\Synchronizer as MiraklQuoteSynchronizer;
use Mirakl\Connector\Test\OfferShippingTestCase;
use Mirakl\Core\Exception\ShippingZoneNotFound;
use Mirakl\Core\Helper\ShippingZone as ShippingZoneHelper;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group offer
 * @group model
 * @group shipping
 * @coversDefaultClass \Mirakl\Connector\Model\Offer\Shipping\Address
 * @covers ::__construct
 */
class AddressTest extends OfferShippingTestCase
{
    /**
     * @var CheckoutSession|MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var AddressRepository|MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var QuoteAddressFactory|MockObject
     */
    private $quoteAddressFactoryMock;

    /**
     * @var ShippingZoneHelper|MockObject
     */
    private $shippingZoneHelperMock;

    /**
     * @var MiraklQuoteSynchronizer|MockObject
     */
    private $quoteSynchronizerMock;

    /**
     * @var Address
     */
    private $address;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->addressRepositoryMock = $this->createMock(AddressRepository::class);
        $this->quoteAddressFactoryMock = $this->createMock(QuoteAddressFactory::class);
        $this->shippingZoneHelperMock = $this->createMock(ShippingZoneHelper::class);
        $this->quoteSynchronizerMock = $this->createMock(MiraklQuoteSynchronizer::class);
        $this->address = new Address(
            $this->checkoutSessionMock,
            $this->shippingZoneHelperMock,
            $this->quoteSynchronizerMock,
            $this->quoteAddressFactoryMock,
            $this->addressRepositoryMock
        );
    }

    /**
     * @covers ::getShippingZoneCode
     */
    public function testGetShippingZoneCode()
    {
        $quoteAddress = $this->createQuoteAddress('John', 'Doe', '123 sample street', '75000', 'Paris', 11, 'FR');
        $this->shippingZoneHelperMock->expects($this->once())
                                     ->method('getShippingZoneCode')
                                     ->with($quoteAddress)
                                     ->willReturn('zone_code');

        $this->assertEquals('zone_code', $this->address->getShippingZoneCode($quoteAddress));
    }

    /**
     * @covers ::getShippingZoneCode
     */
    public function testGetShippingZoneCodeNotFound()
    {
        $quoteAddress = $this->createQuoteAddress('John', 'Doe', '123 sample street', '75000', 'Paris', 11, 'FR');
        $this->shippingZoneHelperMock->expects($this->once())
                                     ->method('getShippingZoneCode')
                                     ->with($quoteAddress)
                                     ->willThrowException(
                                         new ShippingZoneNotFound(__('No shipping zone found for current address'))
                                     );

        $this->assertNull($this->address->getShippingZoneCode($quoteAddress));
    }

    /**
     * @covers ::isAddressHasShippingZone
     */
    public function testIsAddressHasShippingZoneTrue()
    {
        $quoteAddress = $this->createQuoteAddress('John', 'Doe', '123 sample street', '75000', 'Paris', 11, 'FR');
        $this->shippingZoneHelperMock->expects($this->once())
                                     ->method('getShippingZoneCode')
                                     ->with($quoteAddress)
                                     ->willReturn('zone_code');

        $this->assertTrue($this->address->isAddressHasShippingZone($quoteAddress));
    }

    /**
     * @covers ::isAddressHasShippingZone
     */
    public function testIsAddressHasShippingZoneFalse()
    {
        $quoteAddress = $this->createQuoteAddress('John', 'Doe', '123 sample street', '75000', 'Paris', 11, 'FR');
        $this->shippingZoneHelperMock->expects($this->once())
                                     ->method('getShippingZoneCode')
                                     ->with($quoteAddress)
                                     ->willThrowException(
                                         new ShippingZoneNotFound(__('No shipping zone found for current address'))
                                     );

        $this->assertFalse($this->address->isAddressHasShippingZone($quoteAddress));
    }

    /**
     * @covers ::isSameAddress
     */
    public function testIsSameAddressTrue()
    {
        $address1 = $this->createQuoteAddress('John', 'Doe', '123 sample street', '75000', 'Paris', 11, 'FR');
        $address2 = $this->createQuoteAddress('John', 'Doe', '123 sample street', '75000', 'Paris', 11, 'FR');

        $this->assertTrue($this->address->isSameAddress($address1, $address2));
    }

    /**
     * @covers ::isSameAddress
     */
    public function testIsSameAddressFalse()
    {
        $address1 = $this->createQuoteAddress('John', 'Doe', '123 sample street', '75000', 'Paris', 11, 'FR');
        $address2 = $this->createQuoteAddress('John', 'Doe', '321 sample street', '75000', 'Paris', 11, 'FR');

        $this->assertFalse($this->address->isSameAddress($address1, $address2));
    }
}
