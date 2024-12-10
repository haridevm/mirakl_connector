<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Controller\Order;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class DownloadAccounting extends AbstractOrder implements HttpGetActionInterface
{
    /**
     * Download a document
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $result = $this->initOrders();
        if ($result !== true) {
            return $result;
        }

        /** @var \Mirakl\MMP\FrontOperator\Domain\Order $miraklOrder */
        $miraklOrder = $this->registry->registry('mirakl_order');

        try {
            $docId = $this->_request->getParam('doc_id');
            /** @var \Mirakl\Api\Helper\DocumentRequest $api */
            $api = $this->apiFactory->get('documentRequest');
            $accountingDocs = $api->getOrderAccountingDocuments($miraklOrder);
            foreach ($accountingDocs->getCollection() as $accountingDoc) {
                /** @var \Mirakl\MMP\Front\Domain\DocumentRequest\AccountingDocumentResponse $accountingDoc */
                foreach ($accountingDoc->getDocuments() as $doc) {
                    /** @var \Mirakl\MMP\Front\Domain\DocumentRequest\DocumentResponse $doc */
                    if ($doc->getId() == $docId) {
                        $file = $api->downloadAccountingDocument($docId);

                        return $this->downloadFile($file, $doc->getFilename());
                    }
                }
            }
            throw new \Exception(
                __('Could not find the document to download.')->render()
            );
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage());
            $this->messageManager->addErrorMessage(
                __('An error occurred while downloading order accounting document.')
            );
        }

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}
