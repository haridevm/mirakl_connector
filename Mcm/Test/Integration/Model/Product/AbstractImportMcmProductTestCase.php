<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Test\Integration\Model\Product;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ProductResourceFactory;
use Mirakl\Core\Test\Integration\TestCase;
use Mirakl\Mcm\Helper\Data as McmDataHelper;
use Mirakl\Mcm\Model\Product\Import\Handler\Csv as McmHandler;
use Mirakl\Mcm\Model\Product\AsyncImport\Handler\Json as McmAsyncHandler;
use Mirakl\Process\Model\Process as ProcessModel;

/**
 * Abstract class for testing product Mcm import scenarios
 */
abstract class AbstractImportMcmProductTestCase extends TestCase
{
    /** @var \Magento\Framework\Filesystem */
    protected $fileSystem;

    /** @var ProductResource */
    protected $productResource;

    /** @var McmDataHelper */
    protected $mcmDatahelper;

    /** @var McmHandler */
    protected $mcmImportHandler;

    /** @var \Mirakl\Core\Helper\Data */
    protected $coreHelper;

    /** @var \Mirakl\Process\Helper\Data */
    protected $processHelper;

    /** @var ProductResourceFactory */
    protected $productResourceFactory;

    /** @var  \Mirakl\Mcm\Helper\Product\Import\Process */
    protected $importHelper;

    /** @var string[] */
    protected $fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileSystem             = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->productResource        = $this->objectManager->create(ProductResource::class);
        $this->mcmDatahelper          = $this->objectManager->get(McmDataHelper::class);
        $this->mcmImportHandler       = $this->objectManager->get(McmDataHelper::class);
        $this->importHelper           = $this->objectManager->create(\Mirakl\Mcm\Helper\Product\Import\Process::class);
        $this->coreHelper             = $this->objectManager->create(\Mirakl\Core\Helper\Data::class);
        $this->processHelper          = $this->objectManager->create(\Mirakl\Process\Helper\Data::class);
        $this->productResourceFactory = $this->objectManager->create(ProductResourceFactory::class);
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
     * Execute fixtures
     *
     * @param ProcessModel $process
     * @param string       $json
     * @return void
     */
    protected function addJsonFile(ProcessModel $process, $json)
    {
        if (!empty($json)) {
            $file = $this->processHelper->saveFile($this->getFilePath($json));
            $process->setFile($file);
        }
    }

    /**
     * Create process to run MCM import
     *
     * @return ProcessModel
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function createProcess()
    {
        $process = $this->processFactory->create();
        $process->setType('TEST MCM IMPORT')
            ->setCode(McmHandler::CODE)
            ->setName('Test of the MCM products import')
            ->setStatus(ProcessModel::STATUS_PENDING)
            ->setHelper(McmHandler::class)
            ->setMethod('run')
            ->setParams([$since = null, $sendReport = false]);

        return $process;
    }

    /**
     * Create process to run asynchronous MCM products import
     *
     * @return ProcessModel
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function createAsyncProcess()
    {
        $process = $this->processFactory->create();
        $process->setType('TEST MCM ASYNC IMPORT')
            ->setName('Test of the MCM products asynchronous import')
            ->setStatus(ProcessModel::STATUS_PENDING)
            ->setHelper(McmAsyncHandler::class)
            ->setMethod('run');

        $process->setParams([$sendReport = false]);

        return $process;
    }

    /**
     * Test product values
     *
     * @param array $miraklProductIds
     * @param array $values
     */
    public function validateAllProductValues($miraklProductIds, $values)
    {
        foreach ($miraklProductIds as $miraklProductId) {
            $newProduct = $this->mcmDatahelper->findSimpleProductByDeduplication($miraklProductId);
            $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $newProduct);
            $this->assertNotNull($newProduct);
            $this->assertEquals($miraklProductId, $newProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID));
            $this->assertEquals($values['mirakl_category_id'], $newProduct->getData('mirakl_category_id'));
            $this->assertEquals($values['name'], $newProduct->getData('name'));
            $this->assertEquals($values['description'], $newProduct->getData('description'));
            $this->assertEquals($values['color'], $newProduct->getData('color'));
            $this->assertEquals($values['size'], $newProduct->getData('size'));
            $this->assertEquals($values['material'], $newProduct->getData('material'));
            $this->assertEquals($values['status'], $newProduct->getStatus());
            $this->assertEquals($values['mirakl_image_1'], $newProduct->getData('mirakl_image_1'));
            $this->assertEquals($values['brand'], $newProduct->getData('brand'));
        }
    }

    /**
     * Run a MCM import
     *
     * @param string $csv
     * @return ProcessModel
     */
    protected function runImport($csv)
    {
        $process = $this->createProcess();

        try {
            $this->addCsvFile($process, $csv);
            $process->setQuiet(true);
            $process->run();
        } catch (\Exception $e) {
        }

        return $process;
    }

    /**
     * Run an MCM async import
     *
     * @param string $jsonFileName
     * @return ProcessModel
     */
    protected function runAsyncImport($jsonFileName)
    {
        $process = $this->createAsyncProcess();

        try {
            $this->addJsonFile($process, $jsonFileName);
            $process->setQuiet(true);
            $process->run();
        } catch (\Exception $e) {
        }

        return $process;
    }
}
