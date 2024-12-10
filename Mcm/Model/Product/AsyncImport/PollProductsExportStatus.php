<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\AsyncImport;

use Mirakl\Api\Helper\Mcm\Async\Product as ProductAsyncApi;
use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Action\DelayableInterface;
use Mirakl\Process\Model\Action\DelayableTrait;
use Mirakl\Process\Model\Action\RetryableInterface;
use Mirakl\Process\Model\Action\RetryableTrait;
use Mirakl\Process\Model\Exception\ChildProcessException;
use Mirakl\Process\Model\Exception\RetryLaterException;
use Mirakl\Process\Model\Exception\StopExecutionException;
use Mirakl\Process\Model\Process;

class PollProductsExportStatus extends AbstractAction implements DelayableInterface, RetryableInterface
{
    use DelayableTrait;
    use RetryableTrait;

    /**
     * @var ProductAsyncApi
     */
    private $api;

    /**
     * @param ProductAsyncApi $api
     * @param array           $data
     */
    public function __construct(ProductAsyncApi $api, array $data = [])
    {
        parent::__construct($data);
        $this->api = $api;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'API CM53';
    }

    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        $trackingId = $params['tracking_id'] ?? null;
        $retryCount = $params['retry_count'] ?? 0;
        if (!$trackingId) {
            throw new ChildProcessException($process, __('Could not find "tracking_id" in process params'));
        }

        $process->output(__('Using tracking ID: %1', $trackingId));
        $attempt = 1;
        $delay = $this->getDefaultDelay();
        while (true) {
            $process->output(__('Calling API CM53 with a delay of %1 seconds ...', $delay));
            $result = $this->api->pollProductsExportAsyncStatus($trackingId, $delay);
            $process->output(__('Mirakl Status: %1', $result->getStatus()), true);
            if ($retryCount > 0 || $result->getStatus() !== 'PENDING' || $attempt >= $this->getMaxAttempts()) {
                break;
            }
            $attempt++;
            $delay *= 2;
        }

        if ($result->getStatus() === 'PENDING') {
            // We reached max number of attempts, quit and delegate the next call to the cron or manual execution
            throw new RetryLaterException(
                $process,
                __('Products export is still pending in Mirakl. Waiting for next execution.')
            );
        }

        $urls = $result->getUrls() ?? [];
        if (!count($urls)) {
            throw new StopExecutionException($process, __('No URLs to process'), Process::STATUS_COMPLETED);
        }

        $process->output(__('%1 URL(s) to process', count($urls)));

        return [
            'tracking_id' => $trackingId,
            'status'      => $result->getStatus(),
            'urls'        => $urls,
        ];
    }
}
