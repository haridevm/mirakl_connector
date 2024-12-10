<?php
namespace Mirakl\FrontendDemo\Block\Message;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Mirakl\Api\Helper\Message as MessageApi;
use Mirakl\Connector\Model\Order\GetOrderCustomerId;
use Mirakl\MMP\Common\Domain\Collection\SeekableCollection;
use Mirakl\MMP\Common\Domain\Message\Thread\Thread;
use Mirakl\MMP\Common\Domain\Message\Thread\ThreadDetails;
use Mirakl\MMP\FrontOperator\Domain\Order as MiraklOrder;

class Order extends Template
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var MessageApi
     */
    protected $messageApi;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var GetOrderCustomerId
     */
    protected $getOrderCustomerId;

    /**
     * @var string
     */
    protected $tabTitle = '';

    /**
     * @var array
     */
    protected $tabChildren = [];

    /**
     * @param Context            $context
     * @param Registry           $coreRegistry
     * @param MessageApi         $messageApi
     * @param CustomerSession    $customerSession
     * @param GetOrderCustomerId $getOrderCustomerId
     * @param array              $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        MessageApi $messageApi,
        CustomerSession $customerSession,
        GetOrderCustomerId $getOrderCustomerId,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->messageApi = $messageApi;
        $this->customerSession = $customerSession;
        $this->getOrderCustomerId = $getOrderCustomerId;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $threads = $this->getThreads();
        $nbThreads = $threads->getCollection()->count();

        if ($nbThreads == 0) {
            $this->tabChildren[] = $this->addBlock('marketplace.message.form.order', FormOrder::class);
        } else if ($nbThreads == 1) {
            $thread = $this->getThread($threads->getCollection()->first())->toArray();
            $this->tabTitle = $thread['topic']['value'] ?? '';
            $this->tabChildren[] = $this->addBlock('marketplace.message.form.new', FormNew::class)->setAsModal(true);
            $this->tabChildren[] = $this->addBlock('marketplace.message.view', View::class);
            $this->tabChildren[] = $this->addBlock('marketplace.message.form.reply', FormReply::class);
        } else {
            $this->tabChildren[] = $this->addBlock('marketplace.message.form.new', FormNew::class)->setAsModal(true);
            $this->tabChildren[] = $this->addBlock('marketplace.message.index', Index::class);
        }
    }

    /**
     * @return  array
     */
    public function getTabChildren()
    {
        return $this->tabChildren;
    }

    /**
     * @return  SeekableCollection
     */
    public function getThreads()
    {
        if ($threads = $this->coreRegistry->registry('mirakl_threads')) {
            return $threads;
        }

        $order = $this->getOrder();
        $customer = $this->customerSession->getCustomer();
        $customerIdForThread = $this->getCustomerIdForThread($order, $customer);

        $threads = $this->messageApi->getThreads(
            $customerIdForThread,
            'MMP_ORDER',
            $this->getMiraklOrder()->getId()
        );

        $this->coreRegistry->register('mirakl_threads', $threads);

        return $threads;
    }

    /**
     * @param   Thread  $thread
     * @return  ThreadDetails
     */
    public function getThread(Thread $thread)
    {
        if (!$this->coreRegistry->registry('mirakl_thread')) {

            $order = $this->getOrder();
            $customer = $this->customerSession->getCustomer();
            $customerIdForThread = $this->getCustomerIdForThread($order, $customer);

            $threadDetails = $this->messageApi->getThreadDetails(
                $thread->getId(),
                $customerIdForThread
            );

            $this->coreRegistry->register('mirakl_thread', $threadDetails);
        }

        return $this->coreRegistry->registry('mirakl_thread');
    }

    /**
     * @param OrderInterface $order
     * @param Customer       $customer
     * @return string
     */
    private function getCustomerIdForThread(OrderInterface $order, Customer $customer)
    {
        return $this->getOrderCustomerId->execute($order, $customer->getDataModel());
    }

    /**
     * @return  string
     */
    public function getTabTitle()
    {
        return $this->tabTitle;
    }

    /**
     * @return  MiraklOrder
     */
    public function getMiraklOrder()
    {
        return $this->coreRegistry->registry('mirakl_order');
    }

    /**
     * @return OrderInterface
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * @param   string  $blockName
     * @param   string  $blockClass
     * @return  BlockInterface
     */
    public function addBlock($blockName, $blockClass)
    {
        $block = $this->getLayout()->getBlock($blockName);
        if (!$block) {
            $block = $this->getLayout()->addBlock($blockClass, $blockName, $this->_nameInLayout);
        }

        return $block;
    }
}
