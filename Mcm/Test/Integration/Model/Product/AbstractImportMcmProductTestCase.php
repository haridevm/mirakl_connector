<?php
namespace Mirakl\Mcm\Test\Integration\Model\Product;

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

    /** @var \Magento\Catalog\Model\ResourceModel\Product */
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

    /** @var ProcessModel */
    protected $processModel;

    /** @var  \Mirakl\Mcm\Helper\Product\Import\Process */
    protected $importHelper;

    /** @var string[] */
    protected $fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileSystem             = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->productResource        = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product::class);
        $this->mcmDatahelper          = $this->objectManager->get(McmDataHelper::class);
        $this->mcmImportHandler       = $this->objectManager->get(McmDataHelper::class);
        $this->processModel           = $this->objectManager->create(ProcessModel::class);
        $this->importHelper           = $this->objectManager->create(\Mirakl\Mcm\Helper\Product\Import\Process::class);
        $this->coreHelper             = $this->objectManager->create(\Mirakl\Core\Helper\Data::class);
        $this->processHelper          = $this->objectManager->create(\Mirakl\Process\Helper\Data::class);
        $this->productResourceFactory = $this->objectManager->create(ProductResourceFactory::class);
    }

    /**
     * Execute fixtures
     *
     * @param   string  $csv
     * @return  void
     */
    protected function addCsvFile($csv)
    {
        if (!empty($csv)) {
            $file = $this->processHelper->saveFile($this->getFilePath($csv));
            $this->processModel->setFile($file);
        }
    }

    /**
     * Execute fixtures
     *
     * @param string $json
     * @return void
     */
    protected function addJsonFile($json)
    {
        if (!empty($json)) {
            $file = $this->processHelper->saveFile($this->getFilePath($json));
            $this->processModel->setFile($file);
        }
    }

    /**
     * Create process to run MCM import
     */
    protected function createProcess()
    {
        $this->processModel = $this->processFactory->create();
        $this->processModel->setType('TEST MCM IMPORT')
            ->setCode(McmHandler::CODE)
            ->setName('Test of the MCM products import')
            ->setStatus(ProcessModel::STATUS_PENDING)
            ->setHelper(McmHandler::class)
            ->setMethod('run')
            ->setParams([$since = null, $sendReport = false]);

        return $this->processModel;
    }

    /**
     * Create process to run asynchronous MCM products import
     */
    protected function createAsyncProcess()
    {
        $this->processModel = $this->processFactory->create();
        $this->processModel->setType('TEST MCM ASYNC IMPORT')
                           ->setName('Test of the MCM products asynchronous import')
                           ->setStatus(ProcessModel::STATUS_PENDING)
                           ->setHelper(McmAsyncHandler::class)
                           ->setMethod('run');

        $this->processModel->setParams([$sendReport = false]);

        return $this->processModel;
    }

    /**
     * Test product values
     *
     * @param   array   $miraklProductIds
     * @param   array   $values
     */
    public function validateAllProductValues($miraklProductIds, $values)
    {
        foreach ($miraklProductIds as $miraklProductId) {
            $newProduct = $this->mcmDatahelper->findSimpleProductByDeduplication($miraklProductId);
            $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $newProduct);
            $this->assertNotNull($newProduct);
            $this->assertEquals($newProduct->getData(McmDataHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID), $miraklProductId);
            $this->assertEquals($newProduct->getData('mirakl_category_id'), $values['mirakl_category_id']);
            $this->assertEquals($newProduct->getData('name'), $values['name']);
            $this->assertEquals($newProduct->getData('description'), $values['description']);
            $this->assertEquals($newProduct->getData('color'), $values['color']);
            $this->assertEquals($newProduct->getData('size'), $values['size']);
            $this->assertEquals($newProduct->getStatus(), $values['status']);
            $this->assertEquals($newProduct->getData('mirakl_image_1'), $values['mirakl_image_1']);
            $this->assertEquals($newProduct->getData('brand'), $values['brand']);
        }
    }

    /**
     * Run a MCM import
     *
     * @param   string  $csv
     */
    protected function runImport($csv)
    {
        try {
            $this->createProcess();
            $this->addCsvFile($csv);
            $this->processModel->setQuiet(true);
            $this->processModel->run();
        } catch (\Exception $e) {}
    }

    /**
     * Run an MCM async import
     *
     * @param string $jsonFileName
     */
    protected function runAsyncImport($jsonFileName)
    {
        try {
            $this->createAsyncProcess();
            $this->addJsonFile($jsonFileName);
            $this->processModel->setQuiet(true);
            $this->processModel->run();

        } catch (\Exception $e) {}
    }
}
