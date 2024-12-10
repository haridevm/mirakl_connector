<?php

declare(strict_types=1);

namespace Mirakl\Connector\Test\Unit\Model\Offer\Shipping;

use Magento\Tax\Helper\Data as TaxHelper;
use Mirakl\Connector\Helper\Config;
use Mirakl\Connector\Helper\Tax as ConnectorTaxHelper;
use Mirakl\Connector\Model\Offer\Shipping\Price;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group offer
 * @group model
 * @group shipping
 * @coversDefaultClass \Mirakl\Connector\Model\Offer\Shipping\Price
 * @covers ::__construct
 */
class PriceTest extends TestCase
{
    /**
     * @var ConnectorTaxHelper|MockObject
     */
    private $connectorTaxHelperMock;

    /**
     * @var TaxHelper|MockObject
     */
    private $taxHelperMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Price
     */
    private $price;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->connectorTaxHelperMock = $this->createMock(ConnectorTaxHelper::class);
        $this->taxHelperMock = $this->createMock(TaxHelper::class);
        $this->configMock = $this->createMock(Config::class);
        $this->price = new Price($this->configMock, $this->connectorTaxHelperMock, $this->taxHelperMock);
    }

    /**
     * @covers ::getShippingPrice
     */
    public function testGetShippingPriceExcludingTaxWithMiraklPricesExcludingTax()
    {
        $this->taxHelperMock->expects($this->once())
                            ->method('displayShippingPriceExcludingTax')
                            ->willReturn(true);
        $this->configMock->expects($this->once())
                         ->method('getShippingPricesIncludeTax')
                         ->willReturn(false);

        $this->assertEquals(3.99, $this->price->getShippingPrice(3.99));
    }

    /**
     * @covers ::getShippingPrice
     */
    public function testGetShippingPriceExcludingTaxWithMiraklPricesIncludingTax()
    {
        $this->taxHelperMock->expects($this->once())
                            ->method('displayShippingPriceExcludingTax')
                            ->willReturn(true);
        $this->configMock->expects($this->once())
                         ->method('getShippingPricesIncludeTax')
                         ->willReturn(true);
        $this->connectorTaxHelperMock->expects($this->once())
                                     ->method('getShippingPriceExclTax')
                                     ->with(3.99)
                                     ->willReturn(3.65);

        $this->assertEquals(3.65, $this->price->getShippingPrice(3.99));
    }

    /**
     * @covers ::getShippingPrice
     */
    public function testGetShippingPriceIncludingTaxWithMiraklPricesExcludingTax()
    {
        $this->taxHelperMock->expects($this->once())
                            ->method('displayShippingPriceExcludingTax')
                            ->willReturn(false);
        $this->configMock->expects($this->once())
                         ->method('getShippingPricesIncludeTax')
                         ->willReturn(false);
        $this->connectorTaxHelperMock->expects($this->once())
                                     ->method('getShippingPriceInclTax')
                                     ->with(3.99)
                                     ->willReturn(4.33);

        $this->assertEquals(4.33, $this->price->getShippingPrice(3.99));
    }

    /**
     * @covers ::getShippingPrice
     */
    public function testGetShippingPriceIncludingTaxWithMiraklPricesIncludingTax()
    {
        $this->taxHelperMock->expects($this->once())
                            ->method('displayShippingPriceExcludingTax')
                            ->willReturn(false);
        $this->configMock->expects($this->once())
                         ->method('getShippingPricesIncludeTax')
                         ->willReturn(true);

        $this->assertEquals(3.99, $this->price->getShippingPrice(3.99));
    }
}
