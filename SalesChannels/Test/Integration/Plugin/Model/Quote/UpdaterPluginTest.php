<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Plugin\Model\Quote;

use Magento\Quote\Model\Quote;
use Mirakl\Connector\Helper\Quote as QuoteHelper;
use Mirakl\Connector\Model\Quote\Updater;
use Mirakl\SalesChannels\Plugin\Model\Quote\UpdaterPlugin;
use Mirakl\SalesChannels\Test\Integration\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group sales_channels
 * @group plugin
 * @coversDefaultClass \Mirakl\SalesChannels\Plugin\Model\Quote\UpdaterPlugin
 * @covers ::__construct
 */
class UpdaterPluginTest extends TestCase
{
    /**
     * @var UpdaterPlugin
     */
    private $plugin;

    /**
     * @var QuoteHelper|MockObject
     */
    private $quoteHelperMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->quoteHelperMock = $this->createMock(QuoteHelper::class);
        $this->plugin = $this->objectManager->create(UpdaterPlugin::class, [
            'quoteHelper' => $this->quoteHelperMock,
        ]);
    }

    /**
     * @covers ::beforeSynchronize
     * @magentoConfigFixture default/mirakl_connector/sales_channels/enable_channel_pricing 1
     * @magentoConfigFixture default/mirakl_connector/sales_channels/mirakl_channels {"default":{"store_code":"default","channel_code":"INIT"},"fr":{"store_code":"fr","channel_code":"FR"}}
     */
    public function testBeforeSynchronize()
    {
        $this->quoteHelperMock->expects($this->once())
            ->method('isMiraklQuote')
            ->willReturn(true);

        $updater = $this->objectManager->create(Updater::class);
        $quote = $this->objectManager->create(Quote::class);

        $this->assertNull($quote->getMiraklChannelCode());

        $this->plugin->beforeSynchronize($updater, $quote);

        $this->assertSame('INIT', $quote->getMiraklChannelCode());
    }
}
