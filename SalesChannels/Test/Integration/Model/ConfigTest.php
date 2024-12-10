<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Model;

use Mirakl\SalesChannels\Test\Integration\TestCase;

/**
 * @group sales_channels
 * @group model
 * @coversDefaultClass \Mirakl\SalesChannels\Model\Config
 * @covers ::__construct
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
class ConfigTest extends TestCase
{
    /**
     * @covers ::getChannelMapping
     */
    public function testGetChannelMappingEmpty()
    {
        $this->assertSame([], $this->config->getChannelMapping());
    }

    /**
     * @covers ::getChannelMapping
     * @magentoConfigFixture default/mirakl_connector/sales_channels/mirakl_channels {"default":{"store_code":"default","channel_code":"INIT"},"fr":{"store_code":"fr","channel_code":"FR"}}
     */
    public function testGetChannelMapping()
    {
        $expected = [
            'default' => ['store_code' => 'default', 'channel_code' => 'INIT'],
            'fr'      => ['store_code' => 'fr', 'channel_code' => 'FR'],
        ];

        $this->assertSame($expected, $this->config->getChannelMapping());
    }

    /**
     * @covers ::isChannelPricingEnabled
     * @magentoConfigFixture default/mirakl_connector/sales_channels/enable_channel_pricing 1
     */
    public function testIsChannelPricingEnabled()
    {
        $this->assertTrue($this->config->isChannelPricingEnabled());
    }
}
