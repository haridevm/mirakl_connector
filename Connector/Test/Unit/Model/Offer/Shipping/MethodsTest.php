<?php

declare(strict_types=1);

namespace Mirakl\Connector\Test\Unit\Model\Offer\Shipping;

use Mirakl\Connector\Model\Offer\Shipping\Address;
use Mirakl\Connector\Model\Offer\Shipping\Methods;
use Mirakl\Connector\Model\Product\Offer\RealtimeProductOffers;
use Mirakl\Connector\Test\OfferShippingTestCase;
use Mirakl\MMP\Common\Domain\Collection\Offer\Shipping\ShippingPriceByZoneAndTypeCollection;
use Mirakl\MMP\FrontOperator\Domain\Product\Offer\OfferOnProduct;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group offer
 * @group model
 * @group shipping
 * @coversDefaultClass \Mirakl\Connector\Model\Offer\Shipping\Methods
 * @covers ::__construct
 */
class MethodsTest extends OfferShippingTestCase
{
    /**
     * @var Address|MockObject
     */
    private $addressMock;

    /**
     * @var RealtimeProductOffers|MockObject
     */
    private $realtimeProductOffersMock;

    /**
     * @var Methods
     */
    private $shippingMethods;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->addressMock = $this->createMock(Address::class);
        $this->realtimeProductOffersMock = $this->createMock(RealtimeProductOffers::class);
        $this->shippingMethods = new Methods($this->realtimeProductOffersMock, $this->addressMock);
    }

    /**
     * @covers ::getShippingMethods
     */
    public function testGetShippingMethodsNoShippingZoneFound()
    {
        $quoteAddress = $this->createQuoteAddress('John', 'Doe', '123 sample street', '75000', 'Paris', 11, 'FR');

        $this->addressMock->expects($this->never())
                          ->method('convertToQuoteAddress');

        $this->assertCount(0, $this->shippingMethods->getShippingMethods(['SKU1', 'SKU2'], $quoteAddress));
    }

    /**
     * @covers ::getShippingMethods
     */
    public function testGetShippingMethodsWithoutActiveOffers()
    {
        $quoteAddress = $this->createQuoteAddress('John', 'Doe', '123 sample street', '75000', 'Paris', 11, 'FR');

        $this->addressMock->expects($this->never())
                          ->method('convertToQuoteAddress');

        $this->addressMock->expects($this->once())
                          ->method('getShippingZoneCode')
                          ->willReturn('FR');

        $this->realtimeProductOffersMock->expects($this->once())
                                        ->method('get')
                                        ->with(['SKU1', 'SKU2'], ['FR'])
                                        ->willReturn(['SKU1' => [], 'SKU2' => []]);

        $shippingMethods = $this->shippingMethods->getShippingMethods(['SKU1', 'SKU2'], $quoteAddress);
        $this->assertCount(2, $shippingMethods);
        $this->assertArrayHasKey('SKU1', $shippingMethods);
        $this->assertArrayHasKey('SKU2', $shippingMethods);
        $this->assertCount(0, $shippingMethods['SKU1']);
        $this->assertCount(0, $shippingMethods['SKU2']);
    }

    /**
     * @covers ::getShippingMethods
     */
    public function testGetShippingMethodsWithActiveOffers()
    {
        $quoteAddress = $this->createQuoteAddress('John', 'Doe', '123 sample street', '75000', 'Paris', 11, 'FR');

        $offersOnProductsData = [
            ['sku'    => 'SKU1',
             'offers' =>
                 [
                     [
                         'offer_id'       => 2024,
                         'shipping_types' => [
                             [
                                 'code'          => 'STD',
                                 'label'         => 'Standard',
                                 'price'         => 2.99,
                                 'earliest_days' => 2,
                                 'latest_days'   => 3,
                                 'earliest_date' => new \DateTime('now +2 day'),
                                 'latest_date'   => new \DateTime('now +3 day'),
                             ],
                             [
                                 'code'          => 'EXP',
                                 'label'         => 'Express',
                                 'price'         => 4.99,
                                 'earliest_days' => 1,
                                 'latest_days'   => 1,
                                 'earliest_date' => new \DateTime('now +1 day'),
                                 'latest_date'   => new \DateTime('now +1 day'),
                             ]
                         ]
                     ]
                 ]
            ],
            ['sku'    => 'SKU2',
             'offers' =>
                 [
                     [
                         'offer_id'       => 2025,
                         'shipping_types' => [
                             [
                                 'code'          => 'STD',
                                 'label'         => 'Standard',
                                 'price'         => 2.99,
                                 'earliest_days' => 2,
                                 'latest_days'   => 3,
                                 'earliest_date' => new \DateTime('now +2 day'),
                                 'latest_date'   => new \DateTime('now +3 day'),
                             ]
                         ]
                     ]
                 ]
            ],
            ['sku'    => 'SKU3',
             'offers' =>
                 []
            ]
        ];

        $this->addressMock->method('convertToQuoteAddress')
                          ->with($quoteAddress)
                          ->willReturn($quoteAddress);

        $this->addressMock->method('getShippingZoneCode')
                          ->willReturn('FR');

        $offersOnProducts = $this->createOffersOnProducts($offersOnProductsData);
        $this->realtimeProductOffersMock->method('get')
                                        ->with(['SKU1', 'SKU2', 'SKU3'], ['FR'])
                                        ->willReturn($offersOnProducts);

        $shippingMethods = $this->shippingMethods->getShippingMethods(['SKU1', 'SKU2', 'SKU3'], $quoteAddress);

        $this->assertCount(3, $shippingMethods);
        $this->assertArrayHasKey('SKU1', $shippingMethods);
        $this->assertCount(1, $shippingMethods['SKU1']);
        $this->assertArrayHasKey(2024, $shippingMethods['SKU1']);
        $this->assertCount(2, $shippingMethods['SKU1'][2024]);

        $this->assertArrayHasKey('SKU2', $shippingMethods);
        $this->assertCount(1, $shippingMethods['SKU2']);
        $this->assertArrayHasKey(2025, $shippingMethods['SKU2']);
        $this->assertCount(1, $shippingMethods['SKU2'][2025]);
    }

    /**
     * @covers ::getShippingMethods
     */
    public function testGetShippingMethodsWithActiveOffersWithoutShippingMethods()
    {
        $quoteAddress = $this->createQuoteAddress('John', 'Doe', '123 sample street', '75000', 'Paris', 11, 'FR');

        $offersOnProductsData = [
            ['sku'    => 'SKU1',
             'offers' =>
                 [
                     [
                         'offer_id' => 2024
                     ]
                 ]
            ]
        ];

        $this->addressMock->method('convertToQuoteAddress')
                          ->with($quoteAddress)
                          ->willReturn($quoteAddress);

        $this->addressMock->method('getShippingZoneCode')
                          ->willReturn('FR');

        $offersOnProducts = $this->createOffersOnProducts($offersOnProductsData);
        $this->realtimeProductOffersMock->method('get')
                                        ->with(['SKU1', 'SKU2'], ['FR'])
                                        ->willReturn($offersOnProducts);

        $shippingMethods = $this->shippingMethods->getShippingMethods(['SKU1', 'SKU2'], $quoteAddress);

        $this->assertCount(2, $shippingMethods);
        $this->assertArrayHasKey('SKU1', $shippingMethods);
        $this->assertArrayHasKey('SKU2', $shippingMethods);
        $this->assertArrayHasKey(2024, $shippingMethods['SKU1']);
        $this->assertCount(0, $shippingMethods['SKU1'][2024]);
        $this->assertCount(0, $shippingMethods['SKU2']);
    }

    /**
     * @covers ::getBestShippingMethodByPrice
     */
    public function testGetBestShippingMethodByPriceWithoutShippingMethods()
    {
        $this->assertEquals(null, $this->shippingMethods->getBestShippingMethodByPrice([]));
    }

    /**
     * @covers ::getBestShippingMethodByPrice
     */
    public function testGetBestShippingMethodByPriceWithMultipleShippingMethods()
    {
        $shippingMethod1 = $this->createShippingMethod('method_1', 'Method 1', 2.00, null, null, null, null);
        $shippingMethod2 = $this->createShippingMethod('method_2', 'Method 2', 4.00, null, null, null, null);
        $shippingMethod3 = $this->createShippingMethod('method_3', 'Method 3', 1.00, null, null, null, null);

        $bestMethod = $this->shippingMethods->getBestShippingMethodByPrice(
            [$shippingMethod1, $shippingMethod2, $shippingMethod3]
        );

        $this->assertEquals('method_3', $bestMethod->getCode());
    }

    /**
     * @covers ::getBestShippingMethodByDate
     */
    public function testGetBestShippingMethodByDateWithoutShippingMethods()
    {
        $this->assertEquals(null, $this->shippingMethods->getBestShippingMethodByDate([]));
    }

    /**
     * @covers ::getBestShippingMethodByDate
     */
    public function testGetBestShippingMethodByDateWithDifferentDates()
    {
        $shippingMethod1 = $this->createShippingMethod(
            'method_1',
            'Method 1',
            3.99,
            3,
            5,
            new \DateTime('now +3 day'),
            new \DateTime('now +5 day')
        );
        $shippingMethod2 = $this->createShippingMethod(
            'method_2',
            'Method 2',
            5.99,
            1,
            1,
            new \DateTime('now +1 day'),
            new \DateTime('now +1 day')
        );
        $shippingMethod3 = $this->createShippingMethod(
            'method_3',
            'Method 3',
            3.99,
            1,
            2,
            new \DateTime('now +1 day'),
            new \DateTime('now +2 day')
        );

        $bestMethod = $this->shippingMethods->getBestShippingMethodByDate(
            [$shippingMethod1, $shippingMethod2, $shippingMethod3]
        );
        $this->assertEquals('method_2', $bestMethod->getCode());
    }

    /**
     * @covers ::getBestShippingMethodByDate
     */
    public function testGetBestShippingMethodByDateWithSameDatesDifferentPrices()
    {
        $shippingMethod1 = $this->createShippingMethod(
            'method_1',
            'Method 1',
            3.99,
            3,
            5,
            new \DateTime('now +3 day'),
            new \DateTime('now +5 day')
        );
        $shippingMethod2 = $this->createShippingMethod(
            'method_2',
            'Method 2',
            4.99,
            1,
            2,
            new \DateTime('now +1 day'),
            new \DateTime('now +2 day')
        );
        $shippingMethod3 = $this->createShippingMethod(
            'method_3',
            'Method 3',
            5.99,
            1,
            1,
            new \DateTime('now +1 day'),
            new \DateTime('now +1 day')
        );
        $shippingMethod4 = $this->createShippingMethod(
            'method_4',
            'Method 4',
            5.00,
            1,
            1,
            new \DateTime('now +1 day'),
            new \DateTime('now +1 day')
        );

        $bestMethod = $this->shippingMethods->getBestShippingMethodByDate(
            [$shippingMethod1, $shippingMethod2, $shippingMethod3, $shippingMethod4]
        );

        $this->assertEquals('method_4', $bestMethod->getCode());
    }

    /**
     * @covers ::getBestShippingMethodByDate
     */
    public function testGetBestShippingMethodByDateWithNoDeliveryTime()
    {
        $shippingMethod1 = $this->createShippingMethod(
            'method_1',
            'Method 1',
            3.99,
            3,
            5,
            new \DateTime('now +3 day'),
            new \DateTime('now +5 day')
        );
        $shippingMethod2 = $this->createShippingMethod(
            'method_2',
            'Method 2',
            4.99,
            1,
            2,
            new \DateTime('now +1 day'),
            new \DateTime('now +2 day')
        );
        $shippingMethod3 = $this->createShippingMethod(
            'method_3',
            'Method 3',
            5.99,
            1,
            1,
            new \DateTime('now +1 day'),
            new \DateTime('now +1 day')
        );
        $shippingMethod4 = $this->createShippingMethod(
            'method_4',
            'Method 4',
            1.00,
            null,
            null,
            null,
            null
        );

        $bestMethod = $this->shippingMethods->getBestShippingMethodByDate(
            [$shippingMethod1, $shippingMethod2, $shippingMethod3, $shippingMethod4]
        );

        $this->assertEquals('method_3', $bestMethod->getCode());
    }

    /**
     * @param array $products
     * @return array
     */
    private function createOffersOnProducts(array $products)
    {
        $offersOnProducts = [];
        foreach ($products as $productData) {
            foreach ($productData['offers'] as $offerData) {
                $offer = new OfferOnProduct();
                $offer->setOfferId($offerData['offer_id']);
                if (isset($offerData['shipping_types'])) {
                    $offer->setShippingTypes(new ShippingPriceByZoneAndTypeCollection());
                    foreach ($offerData['shipping_types'] as $shippingTypeData) {
                        $shippingMethod = $this->createShippingMethod(
                            $shippingTypeData['code'],
                            $shippingTypeData['label'],
                            $shippingTypeData['price'],
                            $shippingTypeData['earliest_days'],
                            $shippingTypeData['latest_days'],
                            $shippingTypeData['earliest_date'],
                            $shippingTypeData['latest_date'],
                        );
                        $offer->getShippingTypes()->add($shippingMethod);
                    }
                }
                $offersOnProducts[$productData['sku']][] = $offer;
            }
        }

        return $offersOnProducts;
    }
}
