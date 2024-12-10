<?php
namespace Mirakl\FrontendDemo\Controller\Order;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Registry;
use Magento\Framework\Session\Generic as GenericSession;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Sales\Controller\OrderInterface;
use Mirakl\Api\Helper\Factory as ApiFactory;
use Mirakl\Api\Helper\Order as OrderApi;
use Mirakl\Connector\Helper\Order as OrderHelper;
use Mirakl\Core\Domain\FileWrapper;
use Psr\Log\LoggerInterface;

abstract class AbstractOrder extends \Magento\Sales\Controller\AbstractController\View implements OrderInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var GenericSession
     */
    protected $session;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var OrderApi
     */
    protected $orderApi;

    /**
     * @var ApiFactory
     */
    protected $apiFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Action\Context       $context
     * @param OrderLoaderInterface $orderLoader
     * @param PageFactory          $resultPageFactory
     * @param Registry             $registry
     * @param CustomerSession      $customerSession
     * @param GenericSession       $session
     * @param FormKeyValidator     $formKeyValidator
     * @param OrderHelper          $orderHelper
     * @param ApiFactory           $apiFactory
     * @param LoggerInterface      $logger
     */
    public function __construct(
        Action\Context $context,
        OrderLoaderInterface $orderLoader,
        PageFactory $resultPageFactory,
        Registry $registry,
        CustomerSession $customerSession,
        GenericSession $session,
        FormKeyValidator $formKeyValidator,
        OrderHelper $orderHelper,
        ApiFactory $apiFactory,
        LoggerInterface $logger
    ) {
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->session = $session;
        $this->formKeyValidator = $formKeyValidator;
        $this->redirectFactory = $context->getResultRedirectFactory();
        $this->url = $context->getUrl();
        $this->orderHelper = $orderHelper;
        $this->orderApi = $apiFactory->get('order');
        $this->apiFactory = $apiFactory;
        $this->logger = $logger;
        parent::__construct($context, $orderLoader, $resultPageFactory);
    }

    /**
     * Try to load remote order by remote_id and register it
     *
     * @param   string|null  $remoteId
     * @return  bool|ResultInterface
     */
    protected function loadMiraklOrder($remoteId = null)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->registry->registry('current_order');

        if (null === $remoteId) {
            $remoteId = $this->_request->getParam('remote_id');
        }

        if ($order && $remoteId) {
            try {
                $miraklOrder = $this->orderHelper->getMiraklOrderById($order->getIncrementId(), $remoteId);
                if ($miraklOrder) {
                    $this->registry->register('mirakl_order', $miraklOrder);
                    $order->setMiraklOrderId($miraklOrder->getId());

                    return true;
                }
            } catch (\Exception $e) {
                $this->logger->warning($e->getMessage());
                $this->messageManager->addErrorMessage(
                    __('An error occurred. Please try again later.')
                );
            }
        }

        $resultRedirect = $this->redirectFactory->create();

        return $resultRedirect->setUrl($this->url->getUrl('sales/order/history'));
    }

    /**
     * Initialize order and remote order in session
     *
     * @return  bool|ResultInterface
     */
    protected function initOrders()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\Result\Redirect) {
            $result->setUrl($this->url->getUrl('sales/order/history'));
        }
        if ($result instanceof ResultInterface) {
            return $result;
        }

        return $this->loadMiraklOrder();
    }

    /**
     * @return  array
     */
    protected function buildFiles(): array
    {
        $files = [];
        $fileData = $this->getRequest()->getFiles('file');

        if ($fileData && !empty($fileData['tmp_name'])) {
            $file = new FileWrapper(new \SplFileObject($fileData['tmp_name']));
            $file->setContentType($fileData['type']);
            $file->setFileName($fileData['name']);
            $files[] = $file;
        }

        return $files;
    }

    /**
     * Order view page
     *
     * @return  ResultInterface
     */
    public function execute()
    {
        $result = $this->initOrders();
        if ($result !== true) {
            return $result;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/order/history');
        }

        return $resultPage;
    }

    /**
     * @param   FileWrapper $file
     * @param   string|null $fileName
     * @return  Raw
     */
    protected function downloadFile(FileWrapper $file, $fileName = null)
    {
        /** @var Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $contentSize = $file->getFile()->fstat()['size'];

        $result->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $file->getContentType(), true)
            ->setHeader('Content-Length', $contentSize);

        if (!$fileName && $file->getFileName()) {
            $fileName = $file->getFileName();
        }

        if ($fileName) {
            $result->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        }

        $result->setContents($file->getFile()->fread($contentSize));

        return $result;
    }
}
