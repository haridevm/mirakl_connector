<?php

declare(strict_types=1);

namespace Mirakl\Core\Test\Unit\Helper;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirakl\Core\Helper\Data as Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group core
 * @group helper
 * @coversDefaultClass \Mirakl\Core\Helper\Data
 * @covers ::__construct
 */
class DataTest extends TestCase
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var FilterManager|MockObject
     */
    private $filterManagerMock;

    /**
     * @var ProductCollectionFactory|MockObject
     */
    private $productCollectionFactoryMock;

    /**
     * @var ConfigurableFactory|MockObject
     */
    private $typeConfigurableFactoryMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolverMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->filterManagerMock = $this->createMock(FilterManager::class);
        $this->productCollectionFactoryMock = $this->createMock(ProductCollectionFactory::class);
        $this->typeConfigurableFactoryMock = $this->createMock(ConfigurableFactory::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->localeResolverMock = $this->createMock(ResolverInterface::class);

        $this->helper = new Helper(
            $this->contextMock,
            $this->storeManagerMock,
            $this->filterManagerMock,
            $this->productCollectionFactoryMock,
            $this->typeConfigurableFactoryMock,
            $this->objectManagerMock
        );
    }

    /**
     * @dataProvider getTestFormatSizeDataProvider
     * @param string $locale
     * @param int    $size
     * @param string $expected
     * @covers ::formatSize
     * @group foo
     */
    public function testFormatSize(string $locale, int $size, string $expected)
    {
        $this->localeResolverMock->expects($this->once())
            ->method('getLocale')
            ->willReturn($locale);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(ResolverInterface::class)
            ->willReturn($this->localeResolverMock);

        $this->assertSame($expected, $this->helper->formatSize($size));
    }

    /**
     * @return array[]
     */
    public function getTestFormatSizeDataProvider(): array
    {
        return [
            ['en_US', 45383240, '43.28 MB'],
            ['fr_FR', 2281907189, '2,13 GB'],
            ['de_DE', 8899, '8,69 KB'],
        ];
    }
}
