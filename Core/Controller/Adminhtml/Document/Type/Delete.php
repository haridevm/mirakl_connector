<?php

declare(strict_types=1);

namespace Mirakl\Core\Controller\Adminhtml\Document\Type;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Mirakl\Core\Controller\Adminhtml\Document\Type;

class Delete extends Type implements HttpPostActionInterface
{
    /**
     * Delete document type action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var \Mirakl\Core\Model\Document\Type $model */
                $model = $this->_objectManager->create(\Mirakl\Core\Model\Document\Type::class);
                /** @var \Mirakl\Core\Model\ResourceModel\Document\Type $resource */
                $resource = $this->_objectManager->create(\Mirakl\Core\Model\ResourceModel\Document\Type::class);
                $resource->load($model, $id);
                $resource->delete($model);
                $this->messageManager->addSuccessMessage(__('You deleted the document type.'));
                $this->_redirect('mirakl/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We cannot delete the document type right now. Please review the log and try again.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('mirakl/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We cannot find a document type to delete.'));
        $this->_redirect('mirakl/*/');
    }
}
