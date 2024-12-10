<?php
declare(strict_types=1);

namespace Mirakl\Api\Helper\Mcm\Async;

use Mirakl\Api\Helper\ClientHelper;
use Mirakl\Core\Domain\FileWrapper;
use Mirakl\MCM\Front\Domain\Product\Export\ProductExportAsyncStatus;
use Mirakl\MCM\Front\Domain\Product\Export\ProductExportAsyncTracking;
use Mirakl\MCM\Front\Request\Catalog\Product\ProductExportAsyncFileJsonRequest;
use Mirakl\MCM\Front\Request\Catalog\Product\ProductExportAsyncRequest;
use Mirakl\MCM\Front\Request\Catalog\Product\ProductExportAsyncStatusRequest;

class Product extends ClientHelper\MMP
{
    /**
     * (CM52) Export products asynchronously
     *
     * @param \DateTime|null $updatedSince
     * @param \DateTime|null $updatedUntil
     * @param int            $megabytesPerChunk
     * @return ProductExportAsyncTracking
     */
    public function createProductsExportAsync(
        \DateTime $updatedSince = null,
        \DateTime $updatedUntil = null,
        int $megabytesPerChunk = 50
    ): ProductExportAsyncTracking {
        $request = new ProductExportAsyncRequest();
        $request->setMegabytesPerChunk($megabytesPerChunk);

        if ($updatedSince) {
            $request->setUpdatedSince($updatedSince);
        }

        if ($updatedUntil) {
            $request->setUpdatedTo($updatedUntil);
        }

        $request->setExportType('application/json');

        $this->_eventManager->dispatch('mirakl_api_mcm_create_products_export_async_before', [
            'request'       => $request,
            'updated_since' => $updatedSince,
            'updated_until' => $updatedUntil,
        ]);

        return $this->send($request);
    }

    /**
     * (CM53) Poll the status of an asynchronous products export
     *
     * @param string $trackingId
     * @param int    $delay
     * @return ProductExportAsyncStatus
     */
    public function pollProductsExportAsyncStatus(string $trackingId, int $delay = 0): ProductExportAsyncStatus
    {
        $request = new ProductExportAsyncStatusRequest($trackingId);

        if ($delay) {
            $request->addOption('delay', $delay * 1000); // Delay in milliseconds
        }

        $this->_eventManager->dispatch('mirakl_api_mcm_poll_products_export_async_before', [
            'request'     => $request,
            'tracking_id' => $trackingId,
        ]);

        return $this->send($request);
    }

    /**
     * (CM54) Retrieve products JSON file once asynchronous products export is complete
     *
     * @param string $fileUrl
     * @return FileWrapper
     */
    public function getProductsExportAsyncFile(string $fileUrl): FileWrapper
    {
        $request = new ProductExportAsyncFileJsonRequest($fileUrl);

        return $this->send($request);
    }
}