<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Test\Unit\Block\Product\Offer;

use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\View\Element\Template\Context;
use Mirakl\Connector\Test\OfferShippingTestCase;
use Mirakl\FrontendDemo\Block\Product\Offer\ShippingDate;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group offer
 * @group block
 * @group shipping
 * @coversDefaultClass \Mirakl\FrontendDemo\Block\Product\Offer\ShippingDate
 * @covers ::__construct
 */
class ShippingDateTest extends OfferShippingTestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Timezone|MockObject
     */
    private $timezone;

    /**
     * @var ShippingDate
     */
    private $shippingDate;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->contextMock = $this->createMock(Context::class);
        $this->timezone = $this->createMock(Timezone::class);
        $this->shippingDate = new ShippingDate($this->contextMock, $this->timezone);
    }

    /**
     * @covers ::getCutOffTime
     */
    public function testGetCutOffTimeSameDayNotPassed()
    {
        $shippingMethod = $this->createShippingMethod(
            'method_code',
            'Method Label',
            3.99,
            2,
            3,
            new \DateTime('now +2 day'),
            new \DateTime('now +3 day')
        );
        $date = new \DateTime('now +3 hour +43 minute');
        $shippingMethod->setCutOffTime($date->format('H:iP'));
        $shippingMethod->setCutOffNextDate($date->format('c'));

        $this->assertEquals(
            '3hr 43min',
            $this->shippingDate->getCutOffTime($shippingMethod->getCutOffTime(), $shippingMethod->getCutOffNextDate())
        );
    }

    /**
     * @covers ::getCutOffTime
     */
    public function testGetCutOffTimeSameDayPassed()
    {
        $shippingMethod = $this->createShippingMethod(
            'method_code',
            'Method Label',
            3.99,
            2,
            3,
            new \DateTime('now +3 day'),
            new \DateTime('now +4 day')
        );
        $date = new \DateTime('now +1 day -4 hour -18 minutes');
        $shippingMethod->setCutOffTime($date->format('H:iP'));
        $shippingMethod->setCutOffNextDate($date->format('c'));

        $this->assertEquals(
            '19hr 42min',
            $this->shippingDate->getCutOffTime($shippingMethod->getCutOffTime(), $shippingMethod->getCutOffNextDate())
        );
    }

    /**
     * @covers ::getCutOffTime
     */
    public function testGetCutOffTimeWithoutMinutes()
    {
        $shippingMethod = $this->createShippingMethod(
            'method_code',
            'Method Label',
            3.99,
            2,
            3,
            new \DateTime('now +2 day'),
            new \DateTime('now +3 day')
        );
        $date = new \DateTime('now +4 hour');
        $shippingMethod->setCutOffTime($date->format('H:iP'));
        $shippingMethod->setCutOffNextDate($date->format('c'));

        $this->assertEquals(
            '4hr',
            $this->shippingDate->getCutOffTime($shippingMethod->getCutOffTime(), $shippingMethod->getCutOffNextDate())
        );
    }

    /**
     * @covers ::getCutOffTime
     */
    public function testGetCutOffTimeWithoutHours()
    {
        $shippingMethod = $this->createShippingMethod(
            'method_code',
            'Method Label',
            3.99,
            2,
            3,
            new \DateTime('now +2 day'),
            new \DateTime('now +3 day')
        );
        $date = new \DateTime('now +50 minute');
        $shippingMethod->setCutOffTime($date->format('H:iP'));
        $shippingMethod->setCutOffNextDate($date->format('c'));

        $this->assertEquals(
            '50min',
            $this->shippingDate->getCutOffTime($shippingMethod->getCutOffTime(), $shippingMethod->getCutOffNextDate())
        );
    }
}
