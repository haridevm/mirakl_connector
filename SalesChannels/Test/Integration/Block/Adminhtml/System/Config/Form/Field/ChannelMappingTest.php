<?php

declare(strict_types=1);

namespace Mirakl\SalesChannels\Test\Integration\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\View\LayoutInterface;
use Mirakl\Api\Helper\Channel as ChannelApi;
use Mirakl\MMP\Common\Domain\Collection\Channel\ChannelCollection;
use Mirakl\SalesChannels\Block\Adminhtml\System\Config\Form\Field\ChannelMapping;
use Mirakl\SalesChannels\Test\Integration\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group sales_channels
 * @group block
 * @coversDefaultClass \Mirakl\SalesChannels\Block\Adminhtml\System\Config\Form\Field\ChannelMapping
 * @covers ::__construct
 * @magentoAppArea adminhtml
 */
class ChannelMappingTest extends TestCase
{
    /**
     * @var ChannelMapping
     */
    private $block;

    /**
     * @var ChannelApi|MockObject
     */
    private $channelApiMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->channelApiMock = $this->createMock(ChannelApi::class);
        $this->block = $this->objectManager->create(LayoutInterface::class)
            ->createBlock(ChannelMapping::class, 'foo', ['channelApi' => $this->channelApiMock])
            ->setElement($this->objectManager->create(Select::class));
    }

    /**
     * @covers ::getMiraklChannels
     * @covers ::getSelectedChannel
     * @covers ::getStores
     * @covers ::_prepareToRender
     * @magentoConfigFixture default/mirakl_connector/sales_channels/enable_channel_pricing 1
     * @magentoConfigFixture default/mirakl_connector/sales_channels/mirakl_channels {"default":{"store_code":"default","channel_code":"INIT"},"fr":{"store_code":"fr","channel_code":"FR"}}
     */
    public function testToHtml()
    {
        $this->channelApiMock->expects($this->once())
            ->method('getChannels')
            ->willReturn(new ChannelCollection([
                ['code' => 'foo', 'label' => 'Foo'],
                ['code' => 'bar', 'label' => 'Bar'],
            ]));

        $this->block->setElement($this->objectManager->create(Select::class));

        $html = $this->block->toHtml();

        $this->assertStringContainsString('<option value="">-- Select Channel --</option>', $html);
        $this->assertStringContainsString('<option value="foo">', $html);
        $this->assertStringContainsString('<option value="bar">', $html);
    }

    /**
     * @covers ::getMiraklChannels
     * @covers ::getSelectedChannel
     */
    public function testToHtmlWithException()
    {
        $this->channelApiMock->expects($this->once())
            ->method('getChannels')
            ->willThrowException(new \Exception('An error occurred'));

        $this->block->setElement($this->objectManager->create(Select::class));

        $html = $this->block->toHtml();

        $this->assertStringContainsString('<option value="" selected>-- Select Channel --</option>', $html);
    }
}
