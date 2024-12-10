<?php
namespace Mirakl\Process\Controller\Adminhtml\Process;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Mirakl\Process\Model\ResourceModel\Process\CollectionFactory;

class MassDelete extends AbstractProcessAction
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     */
    public function __construct(Filter $filter, CollectionFactory $collectionFactory, Context $context)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        if (!$collection->count()) {
            return $this->redirectError(__('Please select processes to delete.'), true);
        }

        try {
            $this->getProcessResource()->deleteIds($collection->getAllIds());
            $this->messageManager->addSuccessMessage(__('Processes have been deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while deleting processes: %1.', $e->getMessage())
            );
        }

        return $this->redirectReferer();
    }
}
