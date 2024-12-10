<?php
namespace Mirakl\Connector\Test\Unit\Model\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Mirakl\Connector\Model\Order\Converter as OrderConverter;
use Mirakl\MMP\Front\Domain\Order\Create\CreateOrder;

class ConverterTest extends \Mirakl\Core\Test\TestCase
{
    /** @var OrderConverter */
    protected $orderConverter;

    /** @var \Mirakl\Connector\Helper\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    /** @var \Magento\Directory\Model\Country|\PHPUnit_Framework_MockObject_MockObject */
    protected $countryMock;

    /** @var \Magento\Directory\Model\CountryFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $countryFactoryMock;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Tax\Item|\PHPUnit_Framework_MockObject_MockObject */
    protected $orderItemTaxMock;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $orderItemTaxFactoryMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(\Mirakl\Connector\Helper\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock->expects($this->any())
            ->method('isSendOrderLineId')
            ->willReturn(false);

        $this->configMock->expects($this->any())
            ->method('getPaymentWorkflow')
            ->willReturn('PAY_ON_ACCEPTANCE');

        $this->countryFactoryMock = $this->getMockBuilder(\Magento\Directory\Model\CountryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->countryMock = $this->getMockBuilder(\Magento\Directory\Model\Country::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getData', 'setData'])
            ->getMock();

        $this->countryFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->countryMock);

        $this->orderItemTaxMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Tax\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderItemTaxFactoryMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderItemTaxFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->orderItemTaxMock);

        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderConverter = (new ObjectManager($this))->getObject(OrderConverter::class, [
            'config'              => $this->configMock,
            'countryFactory'      => $this->countryFactoryMock,
            'orderItemTaxFactory' => $this->orderItemTaxFactoryMock,
            'eventManager'        => $this->eventManagerMock,
        ]);
    }

    public function testConvertOrder()
    {
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['getItems', 'getStore', 'getBillingAddress', 'getShippingAddress', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $orderData = $this->_getJsonFileContents('order/000000086/order.json');
        $orderMock->setData($orderData);

        $orderMock->expects($this->any())
            ->method('getStore')
            ->willReturn(null);

        $orderMock->expects($this->any())
            ->method('getId')
            ->willReturn($orderMock->getData('entity_id'));

        $orderItemsData = $this->_getJsonFileContents('order/000000086/order_items.json');
        $orderItemsMock = [];

        foreach ($orderItemsData as $itemData) {
            $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
                ->setMethods(['getStore', 'getId'])
                ->disableOriginalConstructor()
                ->getMock();

            $orderItemMock->setOrder($orderMock);
            $orderItemMock->setData($itemData);

            $orderItemMock->expects($this->any())
                ->method('getStore')
                ->willReturn(null);

            $orderItemMock->expects($this->any())
                ->method('getId')
                ->willReturn($orderItemMock->getData('item_id'));

            $orderItemsMock[] = $orderItemMock;
        }

        $orderMock->expects($this->any())
            ->method('getItems')
            ->willReturn($orderItemsMock);

        $billingAddressData = $this->_getJsonFileContents('order/000000086/order_billing_address.json');
        $billingAddressMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Address::class)
            ->setMethods()
            ->disableOriginalConstructor()
            ->getMock();
        $billingAddressMock->setData($billingAddressData);
        $orderMock->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($billingAddressMock);

        $shippingAddressData = $this->_getJsonFileContents('order/000000086/order_shipping_address.json');
        $shippingAddressMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Address::class)
            ->setMethods()
            ->disableOriginalConstructor()
            ->getMock();
        $shippingAddressMock->setData($shippingAddressData);
        $orderMock->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($shippingAddressMock);

        $this->countryMock->expects($this->any())
            ->method('loadByCode')
            ->willReturnSelf();

        $this->countryMock->setData([
            'country_id' => 'FR',
            'iso2_code'  => 'FR',
            'iso3_code'  => 'FRA',
        ]);

        $orderItemsTaxesData = $this->_getJsonFileContents('order/000000086/order_items_taxes.json');
        $this->orderItemTaxMock->expects($this->any())
            ->method('getTaxItemsByOrderId')
            ->willReturn($orderItemsTaxesData);

        $createOrder = $this->orderConverter->convert($orderMock);

        $this->assertInstanceOf(CreateOrder::class, $createOrder);

        $this->assertSame('000000086', $createOrder->getCommercialId());
        $this->assertSame('FR', $createOrder->getShippingZoneCode());
        $this->assertSame('TAX_INCLUDED', $createOrder->getOrderTaxMode());
        $this->assertSame('PAY_ON_ACCEPTANCE', $createOrder->getPaymentWorkflow());
        $this->assertTrue($createOrder->getScored());

        $createOrderCustomer = $createOrder->getCustomer();
        $this->assertSame('Veronica', $createOrderCustomer->getFirstname());
        $this->assertSame('Costello', $createOrderCustomer->getLastname());
        $this->assertSame('roni_cost@example.com', $createOrderCustomer->getEmail());

        $createOrderBillingAddress = $createOrderCustomer->getBillingAddress();
        $this->assertSame('Veronica', $createOrderBillingAddress->getFirstname());
        $this->assertSame('Costello', $createOrderBillingAddress->getLastname());
        $this->assertSame('12 rue Lubeck', $createOrderBillingAddress->getStreet1());
        $this->assertSame('75016', $createOrderBillingAddress->getZipCode());
        $this->assertSame('Paris', $createOrderBillingAddress->getCity());
        $this->assertSame('Paris', $createOrderBillingAddress->getState());
        $this->assertSame('0987654321', $createOrderBillingAddress->getPhone());
        $this->assertSame('FRA', $createOrderBillingAddress->getCountryIsoCode());

        $createOrderShippingAddress = $createOrderCustomer->getShippingAddress();
        $this->assertSame('Christian', $createOrderShippingAddress->getFirstname());
        $this->assertSame('Clavier', $createOrderShippingAddress->getLastname());
        $this->assertSame('115 avenue Jean Jaurès', $createOrderShippingAddress->getStreet1());
        $this->assertSame('51100', $createOrderShippingAddress->getZipCode());
        $this->assertSame('Reims', $createOrderShippingAddress->getCity());
        $this->assertSame('Marne', $createOrderShippingAddress->getState());
        $this->assertSame('0605040302', $createOrderShippingAddress->getPhone());
        $this->assertSame('FRA', $createOrderShippingAddress->getCountryIsoCode());

        $createOrderOffers = $createOrder->getOffers();

        $createOrderOffer1 = $createOrderOffers->get(0);
        $this->assertSame(2238, $createOrderOffer1->getOfferId());
        $this->assertSame(2, $createOrderOffer1->getQuantity());
        $this->assertSame(10.0, $createOrderOffer1->getShippingPrice());
        $this->assertSame('STD', $createOrderOffer1->getShippingTypeCode());
        $this->assertSame('EUR', $createOrderOffer1->getCurrencyIsoCode());
        $this->assertEquals(19.80, $createOrderOffer1->getPrice());
        $this->assertEquals(9.90, $createOrderOffer1->getOfferPrice());

        $createOrderOffer1Taxes = $createOrderOffer1->getTaxes();

        $createOrderOffer1Taxes1 = $createOrderOffer1Taxes->get(0);
        $this->assertEquals(3.13, $createOrderOffer1Taxes1->getAmount());
        $this->assertSame('TVA20', $createOrderOffer1Taxes1->getCode());

        $createOrderOffer1Taxes2 = $createOrderOffer1Taxes->get(1);
        $this->assertEquals(1.03, $createOrderOffer1Taxes2->getAmount());
        $this->assertSame('FranceTVA5.5', $createOrderOffer1Taxes2->getCode());

        $createOrderOffer1ShippingTaxes = $createOrderOffer1->getShippingTaxes();

        $createOrderOffer1ShippingTaxes1 = $createOrderOffer1ShippingTaxes->get(0);
        $this->assertEquals(0.52, $createOrderOffer1ShippingTaxes1->getAmount());
        $this->assertSame('FranceTVA5.5', $createOrderOffer1ShippingTaxes1->getCode());

        $createOrderOffer1ShippingTaxes2 = $createOrderOffer1ShippingTaxes->get(1);
        $this->assertEquals(1.58, $createOrderOffer1ShippingTaxes2->getAmount());
        $this->assertSame('TVA20', $createOrderOffer1ShippingTaxes2->getCode());

        $createOrderOffer2 = $createOrderOffers->get(1);
        $this->assertSame(2226, $createOrderOffer2->getOfferId());
        $this->assertSame(1, $createOrderOffer2->getQuantity());
        $this->assertSame(2.0, $createOrderOffer2->getShippingPrice());
        $this->assertSame('STD', $createOrderOffer2->getShippingTypeCode());
        $this->assertSame('EUR', $createOrderOffer2->getCurrencyIsoCode());
        $this->assertEquals(69.00, $createOrderOffer2->getPrice());
        $this->assertEquals(69.00, $createOrderOffer2->getOfferPrice());

        $createOrderOffer2Taxes = $createOrderOffer2->getTaxes();
        $this->assertEmpty($createOrderOffer2Taxes);

        $createOrderOffer2ShippingTaxes = $createOrderOffer2->getShippingTaxes();

        $createOrderOffer2ShippingTaxes1 = $createOrderOffer2ShippingTaxes->get(0);
        $this->assertEquals(0.10, $createOrderOffer2ShippingTaxes1->getAmount());
        $this->assertSame('FranceTVA5.5', $createOrderOffer2ShippingTaxes1->getCode());

        $createOrderOffer2ShippingTaxes2 = $createOrderOffer2ShippingTaxes->get(1);
        $this->assertEquals(0.32, $createOrderOffer2ShippingTaxes2->getAmount());
        $this->assertSame('TVA20', $createOrderOffer2ShippingTaxes2->getCode());
    }
}