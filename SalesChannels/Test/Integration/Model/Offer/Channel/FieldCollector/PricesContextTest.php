<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Model\Offer\Channel\FieldCollector;

use Mirakl\SalesChannels\Model\Offer\Channel\FieldCollector\PricesContext;
use Mirakl\SalesChannels\Model\Offer\ChannelOffer;
use Mirakl\SalesChannels\Test\Integration\TestCase;

/**
 * @group sales_channels
 * @group model
 * @coversDefaultClass \Mirakl\SalesChannels\Model\Offer\Channel\FieldCollector\PricesContext
 * @covers ::__construct
 */
class PricesContextTest extends TestCase
{
    /**
     * @var PricesContext
     */
    private $pricesContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->pricesContext = $this->objectManager->create(PricesContext::class);
    }

    /**
     * @covers ::collect
     * @covers ::getChannelPrices
     */
    public function testCollect()
    {
        $this->importSampleOffers();

        /** @var ChannelOffer $channelOffer */
        $channelOffer = $this->objectManager->create(ChannelOffer::class);
        $channelOffer->load(2220); // @phpstan-ignore-line

        $data = $this->pricesContext->collect($channelOffer);

        $this->assertNull($channelOffer->getChannel());
        $this->assertSame([], $data);

        $channelOffer->setData('channel', 'INIT');
        $data = $this->pricesContext->collect($channelOffer);

        $this->assertSame('INIT', $channelOffer->getChannel());
        $this->assertSame([], $data);

        $channelOffer->setData('channel', 'FR');
        $data = $this->pricesContext->collect($channelOffer);

        $this->assertSame('FR', $channelOffer->getChannel());
        $this->assertEquals([
            'price'               => 20.79,
            'origin_price'        => 21.00,
            'price_ranges'        => '1|21.00,2|20.50,10|19.90',
            'discount_price'      => 20.79,
            'discount_ranges'     => '1|20.79,2|20.19,10|18.90',
            'discount_start_date' => null,
            'discount_end_date'   => null,
            'final_price'         => 20.79,
        ], $data);

        $channelOffer->setData('channel', 'IT');
        $data = $this->pricesContext->collect($channelOffer);

        $this->assertSame('IT', $channelOffer->getChannel());
        $this->assertEquals([
            'price'               => 29.9,
            'origin_price'        => 29.9,
            'price_ranges'        => '1|29.90',
            'discount_price'      => null,
            'discount_ranges'     => '1|25.90',
            'discount_start_date' => '2022-08-11T22:00:00Z',
            'discount_end_date'   => '2022-08-31T21:59:59Z',
        ], $data);
    }
}
