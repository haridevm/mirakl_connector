<?php

declare(strict_types=1);

namespace Mirakl\Mci\Test\Integration\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ProductResourceFactory;
use Mirakl\Core\Test\Integration\TestCase;
use Mirakl\Core\Helper\Data as CoreHelper;
use Mirakl\Mci\Helper\Data as MciDataHelper;
use Mirakl\Mci\Helper\Product\Import\Finder;
use Mirakl\Mci\Model\Product\Import\Handler\Csv as MciHandler;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Process as ProcessModel;

/**
 * Abstract class for testing product Mci import scenarios
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractImportProductTestCase extends TestCase
{
    /** @var \Magento\Framework\Filesystem */
    protected $fileSystem;

    /** @var ProductResource */
    protected $productResource;

    /** @var MciDataHelper */
    protected $mciDatahelper;

    /** @var MciHandler */
    protected $mciImportHandler;

    /** @var CoreHelper */
    protected $coreHelper;

    /** @var ProductResourceFactory */
    protected $productResourceFactory;

    /** @var MciHandler */
    protected $mciHandler;

    /** @var Finder */
    protected $finder;

    /** @var ProcessHelper */
    protected $processHelper;

    /** @var string[] */
    protected $fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileSystem             = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->productResource        = $this->objectManager->create(ProductResource::class);
        $this->mciDatahelper          = $this->objectManager->get(MciDataHelper::class);
        $this->mciImportHandler       = $this->objectManager->get(MciHandler::class);
        $this->coreHelper             = $this->objectManager->create(CoreHelper::class);
        $this->productResourceFactory = $this->objectManager->create(ProductResourceFactory::class);
        $this->mciHandler             = $this->objectManager->create(MciHandler::class);
        $this->finder                 = $this->objectManager->create(Finder::class);
        $this->processHelper          = $this->objectManager->create(ProcessHelper::class);
    }

    /**
     * Create process to run Mci import
     *
     * @param string $csv
     * @param string $shopId
     * @return ProcessModel
     */
    protected function createProcess($csv, $shopId)
    {
        $process = $this->processFactory->create();
        $process->setType(ProcessModel::TYPE_IMPORT)
            ->setCode(\Mirakl\Mci\Helper\Product\Import::CODE)
            ->setName('TEST MCI products import from path')
            ->setStatus(ProcessModel::STATUS_PENDING)
            ->setHelper(\Mirakl\Mci\Helper\Product\Import::class)
            ->setMethod('runFile')
            ->setParams([$shopId, $shopId]);

        $this->addCsvFile($process, $csv);

        return $process;
    }

    /**
     * Execute fixtures
     *
     * @param ProcessModel $process
     * @param string       $csv
     * @return void
     */
    protected function addCsvFile(ProcessModel $process, $csv)
    {
        if (!empty($csv)) {
            $file = $this->processHelper->saveFile($this->getFilePath($csv));
            $process->setFile($file);
        }
    }

    /**
     * Test product values
     *
     * @param string $shopId
     * @param array  $values
     * @return Product
     */
    public function validateAllProductValues($shopId, $values)
    {
        $newProduct = $this->finder->findProductByDeduplication($values, Product\Type::TYPE_SIMPLE);
        $this->assertInstanceOf(Product::class, $newProduct);
        $this->assertNotNull($newProduct);

        if (isset($values['ean'])) {
            $this->assertEquals($values['ean'], $newProduct->getData('ean'));
        }

        if (isset($values['shop_skus'])) {
            $this->assertEquals($values['shop_skus'], $newProduct->getData(MciDataHelper::ATTRIBUTE_SHOPS_SKUS));
        }

        if (isset($values['mirakl_sync'])) {
            $this->assertEquals($values['mirakl_sync'], $newProduct->getData('mirakl_sync'));
        }

        $this->assertStringContainsString($shopId, $newProduct->getData(MciDataHelper::ATTRIBUTE_SHOPS_SKUS));
        $this->assertEquals($values['name'], $newProduct->getData('name'));
        $this->assertEquals($values['description'], $newProduct->getData('description'));
        $this->assertEquals($values['color'], $newProduct->getData('color'));
        $this->assertEquals($values['size'], $newProduct->getData('size'));
        $this->assertEquals($values['status'], $newProduct->getStatus());
        $this->assertEquals($values['mirakl_image_1'], $newProduct->getData('mirakl_image_1'));
        $this->assertEquals($values['brand'], $newProduct->getData('brand'));

        return $newProduct;
    }

    /**
     * Run a Mci import
     *
     * @param string $shopId
     * @param string $csv
     * @return ProcessModel
     */
    protected function runImport($shopId, $csv)
    {
        $process = $this->createProcess($csv, $shopId);
        $process->setQuiet(true);
        $process->run();

        return $process;
    }

    /**
     * Creates a Mirakl process
     *
     * @param array $productIds
     * @return ProcessModel
     */
    protected function createImageCommandProcess(array $productIds)
    {
        $process = $this->processFactory->create();
        $process->setType(ProcessModel::TYPE_CLI)
            ->setName('Products images import')
            ->setStatus(ProcessModel::STATUS_PENDING)
            ->setHelper(\Mirakl\Mci\Helper\Product\Image::class)
            ->setMethod('runByProductIds')
            ->setParams([$productIds]);

        return $process;
    }
}
