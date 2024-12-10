<?php

declare(strict_types=1);

namespace Mirakl\Api\Helper\Async;

use Mirakl\Api\Helper\ClientHelper;
use Mirakl\Core\Domain\FileWrapper;
use Mirakl\MMP\Common\Domain\Offer\Async\Export\OffersExportAsyncTrackingResult;
use Mirakl\MMP\Common\Domain\Offer\Async\Export\PollOffersExportAsyncStatusResult;
use Mirakl\MMP\Common\Request\Offer\Async\Export\OffersExportAsyncFileJsonRequest;
use Mirakl\MMP\Common\Request\Offer\Async\Export\PollOffersExportAsyncRequest;
use Mirakl\MMP\FrontOperator\Request\Offer\Async\Export\OffersExportAsyncRequest;

class Offer extends ClientHelper\MMP
{
    /**
     * (OF52) Export offers asynchronously
     *
     * @param \DateTime|null $since
     * @param int            $megabytesPerChunk
     * @return OffersExportAsyncTrackingResult
     */
    public function createOffersExportAsync(\DateTime $since = null, int $megabytesPerChunk = 50)
    {
        $request = new OffersExportAsyncRequest();
        $request->setMegabytesPerChunk($megabytesPerChunk);
        $request->setModels(['MARKETPLACE']);

        if ($since) {
            $request->setLastRequestDate($since);
        }

        $request->setExportType('application/json');

        $this->_eventManager->dispatch('mirakl_api_create_offers_export_async_before', [
            'request' => $request,
            'since'   => $since,
        ]);

        return $this->send($request);
    }

    /**
     * (OF53) Poll the status of an asynchronous offers export (OF52)
     *
     * @param string $trackingId
     * @param int    $delay
     * @return PollOffersExportAsyncStatusResult
     */
    public function pollOffersExportAsyncStatus(string $trackingId, int $delay = 0)
    {
        $request = new PollOffersExportAsyncRequest($trackingId);

        if ($delay) {
            $request->addOption('delay', $delay * 1000); // Delay in milliseconds
        }

        $this->_eventManager->dispatch('mirakl_api_poll_offers_export_async_before', [
            'request'     => $request,
            'tracking_id' => $trackingId,
        ]);

        return $this->send($request);
    }

    /**
     * (OF54) Retrieve offers JSON file once asynchronous offer export is complete (OF52)
     *
     * @param string $fileUrl
     * @return FileWrapper
     */
    public function getOffersExportAsyncFile(string $fileUrl): FileWrapper
    {
        $request = new OffersExportAsyncFileJsonRequest($fileUrl);

        return $this->send($request);
    }
}
