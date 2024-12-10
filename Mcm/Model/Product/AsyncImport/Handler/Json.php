<?php
namespace Mirakl\Mcm\Model\Product\AsyncImport\Handler;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Mirakl\Api\Helper\Mcm\Product as ProductApiHelper;
use Mirakl\Mci\Model\Product\Import\Exception\ImportException;
use Mirakl\Mci\Model\Product\Import\Exception\SkipException;
use Mirakl\Mci\Model\Product\Import\Report\ReportInterface;
use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Mcm\Model\Product\Import\Adapter\AdapterInterface;
use Mirakl\MCM\Front\Domain\Product\Export\ProductAcceptanceStatus as ProductAcceptance;
use Mirakl\Process\Model\Process;

class Json
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var ReportInterface
     */
    private $successReport;

    /**
     * @var ReportInterface
     */
    private $errorReport;

    /**
     * @var ProductApiHelper
     */
    private $productApiHelper;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var int
     */
    private $cm21BatchSize;

    /**
     * @param AdapterInterface $adapter
     * @param ReportInterface  $successReport
     * @param ReportInterface  $errorReport
     * @param ProductApiHelper $productApiHelper
     * @param Filesystem       $filesystem
     * @param JsonSerializer   $jsonSerializer
     * @param int              $cm21BatchSize
     */
    public function __construct(
        AdapterInterface $adapter,
        ReportInterface $successReport,
        ReportInterface $errorReport,
        ProductApiHelper $productApiHelper,
        Filesystem $filesystem,
        JsonSerializer $jsonSerializer,
        int $cm21BatchSize = 500
    ) {
        $this->adapter          = $adapter;
        $this->successReport    = $successReport;
        $this->errorReport      = $errorReport;
        $this->productApiHelper = $productApiHelper;
        $this->filesystem       = $filesystem;
        $this->jsonSerializer   = $jsonSerializer;
        $this->cm21BatchSize    = $cm21BatchSize;
    }

    /**
     * @param Process $process
     * @param bool    $sendReport
     * @return $this
     * @throws \Exception
     */
    public function run(Process $process, $sendReport = true)
    {
        $fileName = $process->getFile();

        try {
            $file = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
                                     ->openFile($fileName);

            $process->output(__('Importing MCM file...'));

            set_error_handler(function ($errNo, $errStr, $errFile, $errLine) use ($process) {
                if ($errNo === E_WARNING) {
                    $errMsg = sprintf('Warning: %s in %s on line %s.', $errStr, $errFile, $errLine);
                    $process->output($errMsg);
                }
            }, E_WARNING | E_NOTICE | E_DEPRECATED | E_USER_DEPRECATED);

            $this->adapter->setFile($file);
            $this->adapter->setProcess($process);

            $jsonProducts = $this->jsonSerializer->unserialize($file->readAll());

            $process->output(__('Found %1 product(s) to import...', count($jsonProducts)));

            $this->adapter->before();

            // Loop through JSON products
            foreach ($jsonProducts as $jsonProduct) {
                try {
                    $miraklProductId  = $this->validateIdentifier($jsonProduct);
                    $miraklProductSku = $jsonProduct[McmHelper::JSON_MIRAKL_PRODUCT_SKU] ?? null;

                    $sku = $this->adapter->import($jsonProduct);

                    $process->output(__('[OK] Product %1 has been processed (SKU: %2)', $miraklProductId, $sku));

                    // Register report not only on product creation
                    $this->writeSuccessReport($miraklProductId, $sku);

                } catch (SkipException $e) {
                    $process->output(__($e->getMessage()));
                } catch (ImportException $e) {
                    $message = __('Warning: %1', $e->getMessage());
                    $process->output($message);
                    if (isset($miraklProductId) && isset($miraklProductSku)) {
                        $this->writeErrorReport($miraklProductId, $miraklProductSku, $message);
                    }
                } catch (\Exception $e) {
                    if (isset($miraklProductId) && isset($miraklProductSku)) {
                        $error = __('Error when importing product with ID %1: %2', $miraklProductId, $e->getMessage());
                        $this->writeErrorReport($miraklProductId, $miraklProductSku, $error);
                    } else {
                        $error = __('Error: %1', $e->getMessage());
                    }
                    $process->output($error);
                }
            }
        } catch (\Exception $e) {
            $process->fail($e->getMessage());
            throw $e;
        } finally {
            $this->adapter->after();

            restore_error_handler();

            if ($sendReport) {
                $this->sendIntegrationReport($process);
            }
        }

        $process->output(__('Done!'));

        return $this;
    }

    /**
     * Sends integration report to Mirakl (CM21)
     *
     * @param   Process $process
     * @return  void
     */
    private function sendIntegrationReport(Process $process)
    {
        $data = array_merge($this->successReport->getContents(), $this->errorReport->getContents());
        $process->output(__('Sending integration report to Mirakl...'), true);

        foreach (array_chunk($data, $this->cm21BatchSize) as $chunk) {
            $this->productApiHelper->export($chunk);
        }
    }

    /**
     * Validate presence and value of the identifier
     *
     * @param  array $data
     * @return string
     * @throws \Exception
     */
    protected function validateIdentifier($data)
    {
        if (empty($data[McmHelper::JSON_MIRAKL_PRODUCT_ID])) {
            throw new \Exception(
                __('"%1" cannot be empty', McmHelper::JSON_MIRAKL_PRODUCT_ID)
            );
        }

        return $data[McmHelper::JSON_MIRAKL_PRODUCT_ID];
    }

    /**
     * @param string $miraklProductId
     * @param string $productSku
     */
    private function writeSuccessReport($miraklProductId, $productSku)
    {
        $this->successReport->write([
            'mirakl_product_id' => $miraklProductId,
            'product_sku'       => $productSku,
            'acceptance'        => ['status' => ProductAcceptance::STATUS_ACCEPTED],
        ]);
    }

    /**
     * @param string $miraklProductId
     * @param string $productSku
     * @param string $message
     */
    private function writeErrorReport($miraklProductId, $productSku, $message)
    {
        $errorReport = [
            'mirakl_product_id'  => $miraklProductId,
            'integration_errors' => [['message' => $message]]
        ];

        if (!empty($productSku)) {
            $errorReport['product_sku'] = $productSku;
        }

        $this->errorReport->write($errorReport);
    }
}
