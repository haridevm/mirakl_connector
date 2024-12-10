<?php

declare(strict_types=1);

namespace Mirakl\Process\Controller\Adminhtml\Process;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Ui\Component\MassAction\Filter;
use Mirakl\Process\Model\DeleteHandler;
use Mirakl\Process\Model\ResourceModel\Process\CollectionFactory;

class MassDelete extends AbstractProcessAction implements HttpPostActionInterface
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DeleteHandler
     */
    private $deleteHandler;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     * @param DeleteHandler     $deleteHandler
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DeleteHandler $deleteHandler
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->deleteHandler = $deleteHandler;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Mirakl\Process\Model\ResourceModel\Process\Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        if (!$collection->count()) {
            return $this->redirectError(__('Please select processes to delete.'), true);
        }

        try {
            $this->deleteHandler->executeCollection($collection);
            $this->messageManager->addSuccessMessage(__('Processes have been deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while deleting processes: %1.', $e->getMessage())
            );
        }

        return $this->redirectReferer();
    }
}
