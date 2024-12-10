<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\AsyncImport;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Mirakl\Api\Helper\Mcm\Async\Product as ProductAsyncApi;
use Mirakl\Mcm\Model\Product\AsyncImport\Handler\Json as JsonHandler;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Action\RetryableInterface;
use Mirakl\Process\Model\Action\RetryableTrait;
use Mirakl\Process\Model\Exception\ChildProcessException;
use Mirakl\Process\Model\Exception\RetryLaterException;
use Mirakl\Process\Model\Process;

class GetProductsExportFile extends AbstractAction implements RetryableInterface
{
    use RetryableTrait;

    /**
     * @var ProductAsyncApi
     */
    private $api;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var JsonHandler
     */
    private $jsonHandler;

    /**
     * @param ProductAsyncApi       $api
     * @param ProcessHelper         $processHelper
     * @param EventManagerInterface $eventManager
     * @param JsonHandler           $jsonHandler
     * @param array                 $data
     */
    public function __construct(
        ProductAsyncApi $api,
        ProcessHelper $processHelper,
        EventManagerInterface $eventManager,
        JsonHandler $jsonHandler,
        array $data = []
    ) {
        parent::__construct($data);
        $this->api = $api;
        $this->processHelper = $processHelper;
        $this->eventManager = $eventManager;
        $this->jsonHandler = $jsonHandler;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return (string) $this->_getData('key');
    }

    /**
     * @param string $key
     * @return void
     */
    public function setKey($key): void
    {
        $this->setData('key', (string) $key);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'API CM54 #' . $this->getKey();
    }

    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        if (!$process->getFile()) {
            $this->download($process, $params);
        }

        // Run products import
        $this->jsonHandler->run($process);

        return [];
    }

    /**
     * @param Process $process
     * @param array   $params
     * @return void
     * @throws ChildProcessException
     * @throws RetryLaterException
     */
    private function download(Process $process, array $params = []): void
    {
        $url = $params['url'] ?? null;
        if (!$url) {
            throw new ChildProcessException($process, __('Could not find "url" in process params'));
        }

        $process->output(__('Downloading products file through API CM54 ...'));
        $process->output($url);
        try {
            $productsFile = $this->api->getProductsExportAsyncFile($url)->getFile();
            $file = $this->processHelper->saveFile($productsFile, 'json');
            $process->setFile($file);
        } catch (\Exception $e) {
            throw new RetryLaterException(
                $process,
                __('An error occurred while downloading products file: %1', $e->getMessage())
            );
        }
    }
}
