<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Test\Unit\Model\Product\Import\Repository;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Mirakl\Mcm\Model\Product\Import\Loader\LoaderInterface;
use Mirakl\Mcm\Model\Product\Import\Repository\Product as ProductRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private const PRODUCT_1 = ['sku' => 'sku_1', 'mirakl_mcm_product_id' => 'mcm_product_id_1', 'attr_1' => 'value_1'];
    private const PRODUCT_2 = ['sku' => 'sku_2', 'mirakl_mcm_product_id' => 'mcm_product_id_2', 'attr_1' => 'value_2'];
    private const PRODUCT_3 = ['sku' => 'sku_3', 'mirakl_mcm_product_id' => null,               'attr_1' => 'value_3'];
    private const PRODUCT_4 = ['sku' => 'sku_4', 'mirakl_mcm_product_id' => null,               'attr_1' => 'value_4'];

    /**
     * @var LoaderInterface|MockObject
     */
    protected $loader;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Collection|MockObject
     */
    protected $productCollection;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->productCollection);

        $this->loader = $this->getMockBuilder(LoaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productRepository = new ProductRepository(
            $this->collectionFactory,
            $this->loader,
            500
        );
    }

    /**
     * @return void
     */
    public function testLoadNewProducts()
    {
        $this->loader->expects($this->once())
            ->method('load')
            ->willReturn([]);

        $this->expectsMethodAddAttributeToFilterWillBeCalled(
            ['mirakl_mcm_product_id', ['mcm_product_id_5', 'mcm_product_id_6', 'mcm_product_id_7']]
        );

        $result = $this->productRepository->load([
            ['sku' => null, 'mirakl_mcm_product_id' => 'mcm_product_id_5'],
            ['sku' => null, 'mirakl_mcm_product_id' => 'mcm_product_id_6'],
            ['sku' => null, 'mirakl_mcm_product_id' => 'mcm_product_id_7'],
        ], 'mirakl_mcm_product_id');

        $this->assertEmpty($result);
    }

    /**
     * @return void
     */
    public function testLoadExistingProductsWithOnlyMcmProductId()
    {
        $this->loader->expects($this->once()) // one load by mirakl_mcm_product_id
            ->method('load')
            ->willReturn([self::PRODUCT_1, self::PRODUCT_2]);

        $this->expectsMethodAddAttributeToFilterWillBeCalled(
            ['mirakl_mcm_product_id', ['mcm_product_id_1', 'mcm_product_id_2']]
        );

        $result = $this->productRepository->load([
            ['sku' => 'sku_1', 'mirakl_mcm_product_id' => 'mcm_product_id_1'],
            ['sku' => null,    'mirakl_mcm_product_id' => 'mcm_product_id_2'],
        ], 'mirakl_mcm_product_id');

        $this->assertSame([
            'mcm_product_id_1' => self::PRODUCT_1,
            'mcm_product_id_2' => self::PRODUCT_2
        ], $result);
    }

    /**
     * @return void
     */
    public function testLoadExistingProductsWithoutMcmProductId()
    {
        $this->loader->expects($this->exactly(2)) // one load by mirakl_mcm_product_id and another load by sku
            ->method('load')
            ->willReturnOnConsecutiveCalls([], [self::PRODUCT_3]);

        $this->expectsMethodAddAttributeToFilterWillBeCalled(
            ['mirakl_mcm_product_id', ['mcm_product_id_3']],
            ['sku', ['sku_3']]
        );

        $result = $this->productRepository->load([
            ['sku' => 'sku_3', 'mirakl_mcm_product_id' => 'mcm_product_id_3', 'attr_1' => 'value_3'],
        ], 'mirakl_mcm_product_id');

        $this->assertSame([
            'mcm_product_id_3' => self::PRODUCT_3,
        ], $result);
    }

    /**
     * @return void
     */
    public function testLoadExistingProductsWithMultipleDeduplicationAttributes()
    {
        $this->loader->expects($this->exactly(3))
            ->method('load')
            ->willReturnOnConsecutiveCalls(
                [self::PRODUCT_1, self::PRODUCT_2],  // loaded by mirakl_mcm_product_id
                [self::PRODUCT_3], // loaded by sku
                [self::PRODUCT_4]  // loaded by attr_1
            );

        $this->expectsMethodAddAttributeToFilterWillBeCalled(
            [
                'mirakl_mcm_product_id',
                ['mcm_product_id_1', 'mcm_product_id_2', 'mcm_product_id_3', 'mcm_product_id_4', 'mcm_product_id_5']
            ],
            ['sku', ['sku_3', 'sku_5']],
            ['attr_1', ['value_4', 'value_5']]
        );

        $result = $this->productRepository->load([
            ['sku' => 'sku_1', 'mirakl_mcm_product_id' => 'mcm_product_id_1', 'attr_1' => 'value_1'],
            ['sku' => null,    'mirakl_mcm_product_id' => 'mcm_product_id_2', 'attr_1' => 'value_2'],
            ['sku' => 'sku_3', 'mirakl_mcm_product_id' => 'mcm_product_id_3', 'attr_1' => 'value_3'],
            ['sku' => null,    'mirakl_mcm_product_id' => 'mcm_product_id_4', 'attr_1' => 'value_4'],
            ['sku' => 'sku_5', 'mirakl_mcm_product_id' => 'mcm_product_id_5', 'attr_1' => 'value_5'],
        ], 'mirakl_mcm_product_id');

        $this->assertSame([
            'mcm_product_id_1' => self::PRODUCT_1,
            'mcm_product_id_2' => self::PRODUCT_2,
            'mcm_product_id_3' => self::PRODUCT_3,
            'mcm_product_id_4' => self::PRODUCT_4,
        ], $result);
    }

    /**
     * @param array ...$expectedConsecutiveParams
     * @return void
     */
    private function expectsMethodAddAttributeToFilterWillBeCalled(array ...$expectedConsecutiveParams)
    {
        $matcher = $this->exactly(count($expectedConsecutiveParams));
        $this->productCollection->expects($matcher)
            ->method('addAttributeToFilter')
            ->willReturnCallback(
                function (string $attribute, array $condition) use ($matcher, $expectedConsecutiveParams) {
                    $expectedParams = $expectedConsecutiveParams[$matcher->getInvocationCount() - 1];
                    $this->assertEquals($expectedParams[0], $attribute);
                    $this->assertArrayHasKey('in', $condition);
                    $this->assertSame($expectedParams[1], array_values($condition['in']));
                }
            );
    }
}
