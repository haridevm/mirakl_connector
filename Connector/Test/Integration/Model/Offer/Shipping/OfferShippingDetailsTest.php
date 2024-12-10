<?php

namespace Mirakl\Connector\Test\Integration\Model\Offer\Shipping;

use Magento\Framework\View\LayoutFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Mirakl\Connector\Model\Offer\Shipping\Methods;
use Mirakl\Connector\Test\OfferShippingTestCase;
use Mirakl\FrontendDemo\Block\Product\Offer\BestShippingMethod;

/**
 * @group connector
 * @group offer
 * @group shipping
 */
class OfferShippingDetailsTest extends OfferShippingTestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testOfferShippingDetailsEnglishVersion()
    {
        /** @var Methods $shippingMethods */
        $miraklShippingMethods = $this->objectManager->get(Methods::class);

        $shippingMethod1 = $this->createShippingMethod(
            'STD',
            'Standard',
            3.99,
            3,
            5,
            new \DateTime('2020-05-07T15:00:00.000Z'),
            new \DateTime('2020-05-09T15:00:00.000Z'),
            'US',
            'United States',
            new \DateTime('now +3 hour +30 minute')
        );
        $shippingMethod2 = $this->createShippingMethod(
            'EXP',
            'Express',
            5.99,
            1,
            2,
            new \DateTime('2020-05-07T15:00:00.000Z'),
            new \DateTime('2020-05-08T15:00:00.000Z'),
            'US',
            'United States',
            new \DateTime('now +3 hour +30 minute')
        );
        $shippingMethod3 = $this->createShippingMethod(
            'NEX',
            'Next Day',
            6.99,
            1,
            1,
            new \DateTime('2020-05-07T15:00:00.000Z'),
            new \DateTime('2020-05-07T15:00:00.000Z'),
            'US',
            'United States',
            new \DateTime('now +3 hour +30 minute')
        );

        $shippingMethods = [$shippingMethod1, $shippingMethod2, $shippingMethod3];

        $bestShippingMethodByPrice = $miraklShippingMethods->getBestShippingMethodByPrice($shippingMethods);
        $bestShippingMethodByDate  = $miraklShippingMethods->getBestShippingMethodByDate($shippingMethods);

        $this->assertEquals('STD', $bestShippingMethodByPrice->getCode());
        $this->assertEquals('NEX', $bestShippingMethodByDate->getCode());

        $layoutFactory = $this->objectManager->get(LayoutFactory::class);
        $layout = $layoutFactory->create();

        $shippingAddress = $this->createQuoteAddress('John', 'Doe', '20 Cooper Square', '10003', 'New York', 11, 'US');

        $bestShippingMethodsBlock = $layout->createBlock(BestShippingMethod::class);
        $bestShippingMethodsBlock->setBestShippingMethodByPrice($bestShippingMethodByPrice);
        $bestShippingMethodsBlock->setBestShippingMethodByDate($bestShippingMethodByDate);
        $bestShippingMethodsBlock->setShippingAddress($shippingAddress);

        $output = 'Shipping from <strong><span class="price">$3.99</span></strong>: delivery between 
<strong>May 7</strong> and <strong>May 9</strong> 
<div class="offer-shipping fastest-shipping"> Or fastest delivery 
<strong>in one day</strong> (from <strong><span class="price">$6.99</span></strong>, order within 
<span class="cut-off-time">3hr 30min</span>) </div> <div class="offer-shipping shipping-destination"> 
<span class="delivery-pin"></span> 
<span>Delivered to United States</span> </div>';

        $this->assertEquals(
            preg_replace('/[\n\r]/', '', $output),
            trim(preg_replace('/\s+/', ' ', $bestShippingMethodsBlock->toHtml()))
        );
    }
}
