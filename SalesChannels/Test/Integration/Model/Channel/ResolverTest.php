<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Model\Channel;

use Mirakl\SalesChannels\Model\Channel\Resolver;
use Mirakl\SalesChannels\Test\Integration\TestCase;

/**
 * @group sales_channels
 * @group model
 * @coversDefaultClass \Mirakl\SalesChannels\Model\Channel\Resolver
 * @covers ::__construct
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
class ResolverTest extends TestCase
{
    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = $this->objectManager->create(Resolver::class);
    }

    /**
     * @covers ::resolve
     * @magentoConfigFixture default/mirakl_connector/sales_channels/enable_channel_pricing 1
     */
    public function testResolveWithoutMapping()
    {
        $channel = $this->resolver->resolve();

        $this->assertNull($channel);
    }

    /**
     * @covers ::resolve
     * @magentoDataFixture Mirakl_SalesChannels::Test/Integration/_fixtures/store_fr.php
     * @magentoConfigFixture default/mirakl_connector/sales_channels/enable_channel_pricing 1
     * @magentoConfigFixture default/mirakl_connector/sales_channels/mirakl_channels {"default":{"store_code":"default","channel_code":"INIT"},"fr":{"store_code":"fr","channel_code":"FR"}}
     */
    public function testResolve()
    {
        $store = $this->storeRepository->get('default');
        $channel = $this->resolver->resolve((int) $store->getId());
        $this->assertSame('INIT', $channel);

        $store = $this->storeRepository->get('fr');
        $channel = $this->resolver->resolve((int) $store->getId());
        $this->assertSame('FR', $channel);
    }
}
