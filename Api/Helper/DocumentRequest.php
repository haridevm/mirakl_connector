<?php
declare(strict_types=1);

namespace Mirakl\Api\Helper;

use Mirakl\Core\Domain\FileWrapper;
use Mirakl\MMP\Common\Domain\Collection\SeekableCollection;
use Mirakl\MMP\Front\Request\DocumentRequest\DownloadAccountingDocumentsRequest;
use Mirakl\MMP\Front\Request\DocumentRequest\GetAccountingDocumentsRequest;
use Mirakl\MMP\FrontOperator\Domain\Order as MiraklOrder;

class DocumentRequest extends ClientHelper\MMP
{
    /**
     * Fetches accounting documents of a single Mirakl order
     *
     * @param MiraklOrder $miraklOrder
     * @return SeekableCollection
     */
    public function getOrderAccountingDocuments(MiraklOrder $miraklOrder): SeekableCollection
    {
        return $this->getOrdersAccountingDocuments([$miraklOrder->getId()]);
    }

    /**
     * (DR72) Fetches accounting documents of multiple Mirakl orders
     *
     * @param array $orderIds
     * @return SeekableCollection
     */
    public function getOrdersAccountingDocuments(array $orderIds): SeekableCollection
    {
        return $this->getAccountingDocuments(['PRODUCT_LOGISTIC_ORDER'], $orderIds);
    }

    /**
     * (DR72) Fetches accounting documents
     *
     * @param array $entityTypes
     * @param array $entityIds
     * @return SeekableCollection
     */
    public function getAccountingDocuments(array $entityTypes = [], array $entityIds = []): SeekableCollection
    {
        $request = new GetAccountingDocumentsRequest();

        if (!empty($entityTypes)) {
            $request->setEntityTypes($entityTypes);
        }

        if (!empty($entityIds)) {
            $request->setEntityIds($entityIds);
        }

        $this->_eventManager->dispatch('mirakl_api_get_accounting_documents_before', [
            'request'      => $request,
            'entity_types' => $entityTypes,
            'entity_ids'   => $entityIds
        ]);

        return $this->send($request);
    }

    /**
     * Downloads a single accounting document
     *
     * @param   string  $docId
     * @return  FileWrapper
     */
    public function downloadAccountingDocument($docId)
    {
        return $this->downloadAccountingDocuments([$docId]);
    }

    /**
     * (DR73) Downloads multiple accounting documents
     *
     * @param   string[]    $docIds
     * @return  FileWrapper
     */
    public function downloadAccountingDocuments(array $docIds)
    {
        $request = new DownloadAccountingDocumentsRequest();
        $request->setDocumentIds($docIds);

        return $this->send($request);
    }
}
