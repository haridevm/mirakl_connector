<?php

namespace Mirakl\FrontendDemo\Test\Integration\Model\Offer\Shipping;

use Magento\TestFramework\Helper\Bootstrap;
use Mirakl\FrontendDemo\Block\Product\Offer\ShippingDate;
use PHPUnit\Framework\TestCase;

/**
 * @group offer
 * @group shipping
 */
class ShippingDateTest extends TestCase
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
     * @magentoConfigFixture current_store general/locale/locale en_US
     * @magentoConfigFixture current_store general/locale/timezone Pacific/Tongatapu
     */
    public function testShippingSameDayInDifferentTimezone()
    {
        /** @var ShippingDate $shippingDateBlock */
        $shippingDateBlock = $this->objectManager->get(ShippingDate::class);

        // Mirakl shipping dates are always in GMT timezone
        $date = new \DateTime('now');

        if ($date->format('G	') >= 11) {
            $date->modify('+1 day');
        }

        $date->setTime(7, 0);

        // Pacific/Tongatapu timezone is GMT+13
        // Tomorrow at 7AM in GMT corresponds to tomorrow at 20PM in Pacific/Tongatapu timezone
        $expectedDate = new \DateTime('now');
        $expectedDate->setTime(20, 0);

        $this->assertEquals($expectedDate->format('g:i A'), $shippingDateBlock->format($date));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/locale en_US
     * @magentoConfigFixture current_store general/locale/timezone Pacific/Tongatapu
     */
    public function testShippingNotTheSameDayInDifferentTimezone()
    {
        /** @var ShippingDate $shippingDateBlock */
        $shippingDateBlock = $this->objectManager->get(ShippingDate::class);

        // Mirakl shipping dates are always in GMT timezone
        $date = new \DateTime('now +1 day');
        $date->setTime(15, 0);

        // Pacific/Tongatapu timezone is GMT+13
        // Tomorrow at 15PM in GMT corresponds to tomorrow + 1 day at 4AM in Pacific/Tongatapu timezone
        $expectedDate = new \DateTime('now +2 day');

        $this->assertEquals($expectedDate->format('F j'), $shippingDateBlock->format($date));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/locale en_US
     * @magentoConfigFixture current_store general/locale/timezone Africa/Dakar
     */
    public function testShippingSameDayInSameTimezone()
    {
        /** @var ShippingDate $shippingDateBlock */
        $shippingDateBlock = $this->objectManager->get(ShippingDate::class);

        $date = new \DateTime('now');
        $date->setTime(15, 0);

        // Africa/Dakar timezone is GMT+0
        $expectedDate = new \DateTime('now');
        $expectedDate->setTime(15, 0);

        $this->assertEquals($expectedDate->format('g:i A'), $shippingDateBlock->format($date));
    }


    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/locale en_US
     * @magentoConfigFixture current_store general/locale/timezone Africa/Dakar
     */
    public function testShippingNotTheSameDayInSameTimezone()
    {
        /** @var ShippingDate $shippingDateBlock */
        $shippingDateBlock = $this->objectManager->get(ShippingDate::class);

        $date = new \DateTime('now +1 day');
        $date->setTime(15, 0);

        // Africa/Dakar timezone is GMT+0
        $expectedDate = new \DateTime('now +1 day');
        $expectedDate->setTime(15, 0);

        $this->assertEquals($expectedDate->format('F j'), $shippingDateBlock->format($date));
    }
}
