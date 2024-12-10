<?php
namespace Mirakl\Mcm\Model\System\Config\Backend\Import;

use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb as ResourceCollection;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Mirakl\Core\Controller\Adminhtml\RawMessagesTrait;
use Mirakl\Mcm\Model\Product\AsyncImport\Handler\Json as McmHandler;
use Mirakl\Mcm\Model\Product\AsyncImport\Import as McmImport;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;

class ProductAsync extends Value
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
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @param Context                 $context
     * @param Registry                $registry
     * @param ScopeConfigInterface    $config
     * @param CacheTypeListInterface  $cacheTypeList
     * @param ProcessFactory          $processFactory
     * @param ProcessResourceFactory  $processResourceFactory
     * @param ProcessHelper           $processHelper
     * @param MessageManagerInterface $messageManager
     * @param AbstractResource|null   $resource
     * @param ResourceCollection|null $resourceCollection
     * @param array                   $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        CacheTypeListInterface $cacheTypeList,
        ProcessFactory $processFactory,
        ProcessResourceFactory $processResourceFactory,
        ProcessHelper $processHelper,
        MessageManagerInterface $messageManager,
        AbstractResource $resource = null,
        ResourceCollection $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
        $this->processFactory = $processFactory;
        $this->processResourceFactory = $processResourceFactory;
        $this->processHelper = $processHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * Do not save value
     *
     * @return  $this
     */
    public function beforeSave()
    {
        $this->setValue('');
        parent::beforeSave();

        return $this;
    }

    /**
     * Import products from uploaded JSON file if present
     *
     * @return  $this
     * @throws  \Exception
     */
    public function afterSave()
    {
        $fileName     = @$_FILES['groups']['name']['import_product_async']['fields']['file']['value'];
        $uploadedFile = @$_FILES['groups']['tmp_name']['import_product_async']['fields']['file']['value'];

        if (!$fileName) {
            return $this;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION) ?? null;
        $file = $this->processHelper->saveFile($uploadedFile, $extension);

        if (!$file) {
            throw new \Exception('File is empty or could not be loaded.');
        }

        $process = $this->processFactory->create()
            ->setType(Process::TYPE_ADMIN)
            ->setName('MCM products import')
            ->setHelper(McmHandler::class)
            ->setCode(McmImport::CODE)
            ->setMethod('run');

        $process->setFile($file);

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
     * @return  bool
     */
    private function isAdmin()
    {
        return $this->getScopeId() == Store::DEFAULT_STORE_ID;
    }
}
