<?php
namespace Mirakl\Process\Controller\Adminhtml\Process;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class View extends AbstractProcessAction
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @deprecated
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     * @param Registry    $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $process = $this->getProcess();

        if (!$process->getId()) {
            return $this->redirectError(__('This process no longer exists.'));
        }

        // @deprecated keep for retro compatibility but not used in the module
        $this->coreRegistry->register('process', $process);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mirakl_Process::process');

        return $resultPage;
    }
}
