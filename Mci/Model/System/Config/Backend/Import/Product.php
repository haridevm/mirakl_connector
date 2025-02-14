<?php

declare(strict_types=1);

namespace Mirakl\Mci\Model\System\Config\Backend\Import;

use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb as ResourceCollection;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mirakl\Core\Controller\Adminhtml\RawMessagesTrait;
use Mirakl\Core\Helper\Csv as CsvHelper;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends \Magento\Framework\App\Config\Value
{
    use RawMessagesTrait;

    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var ProcessResourceFactory
     */
    private $processResourceFactory;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var CsvHelper
     */
    private $csvHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param Context                 $context
     * @param Registry                $registry
     * @param ScopeConfigInterface    $config
     * @param CacheTypeListInterface  $cacheTypeList
     * @param ProcessFactory          $processFactory
     * @param ProcessResourceFactory  $processResourceFactory
     * @param ProcessHelper           $processHelper
     * @param StoreManagerInterface   $storeManager
     * @param MessageManagerInterface $messageManager
     * @param CsvHelper               $csvHelper
     * @param RequestInterface        $request
     * @param AbstractResource|null   $resource
     * @param ResourceCollection|null $resourceCollection
     * @param array                   $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        CacheTypeListInterface $cacheTypeList,
        ProcessFactory $processFactory,
        ProcessResourceFactory $processResourceFactory,
        ProcessHelper $processHelper,
        StoreManagerInterface $storeManager,
        MessageManagerInterface $messageManager,
        CsvHelper $csvHelper,
        RequestInterface $request,
        AbstractResource $resource = null,
        ResourceCollection $resourceCollection = null,
        array $data = []
    ) {
        $this->processFactory = $processFactory;
        $this->processResourceFactory = $processResourceFactory;
        $this->processHelper = $processHelper;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->csvHelper = $csvHelper;
        $this->request = $request;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Do not save value
     *
     * @return $this
     */
    public function beforeSave()
    {
        $this->setValue('');
        parent::beforeSave();

        return $this;
    }

    /**
     * Import products from uploaded file if present
     *
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        $files = $this->request->getFiles();

        $groups = $files->get('groups') ?? [];
        $fileName = $groups['import_shop_product']['fields']['file']['value']['name'] ?? '';
        $uploadedFile = $groups['import_shop_product']['fields']['file']['value']['tmp_name'] ?? '';

        if (!$fileName) {
            return $this;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION) ?? null;
        $file = $this->processHelper->saveFile($uploadedFile, $extension);

        if (!$file) {
            throw new \Exception('File is empty or could not be loaded.');
        }

        $shop = $this->csvHelper->getShopFromFileName($fileName);
        $importId = $this->csvHelper->getImportIdFromFileName($fileName);

        /** @var Process $process */
        $process = $this->processFactory->create()
            ->setType(Process::TYPE_ADMIN)
            ->setCode(\Mirakl\Mci\Helper\Product\Import::CODE)
            ->setName('MCI products import')
            ->setFile($file)
            ->setHelper(\Mirakl\Mci\Helper\Product\Import::class)
            ->setParams([$shop->getId(), $importId])
            ->setMethod('runFile');

        $this->processResourceFactory->create()->save($process);

        if ($this->isAdmin()) {
            $this->messageManager->addSuccessMessage(
                __('File has been uploaded successfully. Products will be imported in background.')
            );

            if ($process->getId()) {
                $this->addRawSuccessMessage(
                    __('Click <a href="%1">here</a> to view process output.', $process->getUrl())
                );
            }
        }

        parent::afterSave();

        return $this;
    }

    /**
     * @return bool
     */
    private function isAdmin()
    {
        return $this->getScopeId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }
}
