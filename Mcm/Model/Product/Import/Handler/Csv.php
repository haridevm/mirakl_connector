<?php
namespace Mirakl\Mcm\Model\Product\Import\Handler;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Mirakl\Api\Helper\Mcm\Product as ProductApiHelper;
use Mirakl\Core\Model\File\CsvFileTrait;
use Mirakl\Mci\Model\Product\Import\Exception\WarningException;
use Mirakl\Mci\Model\Product\Import\Report\ReportInterface;
use Mirakl\Mcm\Helper\Config as McmConfig;
use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Mcm\Model\Product\Import\Adapter\AdapterFactory;
use Mirakl\Mcm\Model\Product\Import\Adapter\AdapterInterface;
use Mirakl\Mcm\Model\Product\Import\Handler\File\Loader;
use Mirakl\MCM\Front\Domain\Product\Export\ProductAcceptanceStatus as ProductAcceptance;
use Mirakl\Process\Model\Process;

class Csv
{
    use CsvFileTrait;

    const CODE = 'CM51';

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var ReportInterface
     */
    protected $successReport;

    /**
     * @var ReportInterface
     */
    protected $errorReport;

    /**
     * @var McmConfig
     */
    protected $config;

    /**
     * @var ProductApiHelper
     */
    protected $productApiHelper;

    /**
     * @var Loader
     */
    protected $loader;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var int
     */
    protected $cm21BatchSize;

    /**
     * @param AdapterFactory $adapterFactory
     * @param ReportInterface $successReport
     * @param ReportInterface $errorReport
     * @param McmConfig $config
     * @param ProductApiHelper $productApiHelper
     * @param Loader $loader
     * @param Filesystem $filesystem
     * @param int $cm21BatchSize
     */
    public function __construct(
        AdapterFactory $adapterFactory,
        ReportInterface $successReport,
        ReportInterface $errorReport,
        McmConfig $config,
        ProductApiHelper $productApiHelper,
        Loader $loader,
        Filesystem $filesystem,
        int $cm21BatchSize = 500
    ) {
        $this->adapter          = $adapterFactory->create();
        $this->successReport    = $successReport;
        $this->errorReport      = $errorReport;
        $this->config           = $config;
        $this->productApiHelper = $productApiHelper;
        $this->loader           = $loader;
        $this->filesystem       = $filesystem;
        $this->cm21BatchSize    = $cm21BatchSize;
    }

    /**
     * @param   Process         $process
     * @param   \DateTime|null  $since
     * @param   \DateTime|null  $until
     * @return  string|null
     */
    public function getApiFile(Process $process, $since, $until = null)
    {
        return $this->loader->load($process, $since, $until);
    }

    /**
     * @param   Process         $process
     * @param   \DateTime|null  $since
     * @param   bool            $sendReport
     * @param   \DateTime|null  $until
     * @return  $this
     * @throws  \Exception
     */
    public function run(Process $process, $since, $sendReport = true, $until = null)
    {
        if (!$this->config->isMcmEnabled()) {
            $process->output(__('Module MCM is disabled. See your Mirakl MCM configuration'));
            return $this;
        }

        if (!$fileName = $this->getApiFile($process, $since, $until)) {
            return $this;
        }

        try {
            $this->adapter->setProcess($process);

            $file = $this->filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->openFile($fileName);

            $process->output(__('Importing MCM file...'));

            $this->adapter->setFile($file);

            set_error_handler(function ($errNo, $errStr, $errFile, $errLine) use ($process) {
                if ($errNo === E_WARNING) {
                    $errMsg = sprintf('Warning: %s in %s on line %s.', $errStr, $errFile, $errLine);
                    $process->output($errMsg);
                }
            }, E_WARNING | E_NOTICE | E_DEPRECATED | E_USER_DEPRECATED);

            $this->adapter->before();

            $i = 0;
            $file->seek(0);
            $cols = $this->readCsv($file); // Used to map keys and values

            // Loop through CSV file
            while (!$file->eof()) {
                try {
                    $row = $this->readCsv($file);
                    $i++; // Line number

                    if (!is_array($row) || count($cols) !== count($row)) {
                        continue;
                    }

                    // Combine column names with values to build an associative array
                    $data = array_combine($cols, $row);

                    $miraklProductId = $this->validateIdentifier($data);
                    $miraklProductSku = $data[McmHelper::CSV_MIRAKL_PRODUCT_SKU];

                    $sku = $this->adapter->import($data);

                    $process->output(__('[OK] Product has been processed for line %1 (SKU: %2)', $i, $sku), $i % 100 === 0);

                    // Register report not only on product creation
                    $this->writeSuccessReport($miraklProductId, $sku);

                } catch (WarningException $e) {
                    $message = __('Warning on line %1: %2', $i, $e->getMessage());
                    $process->output($message);
                    if (isset($miraklProductId) && isset($miraklProductSku)) {
                        $this->writeErrorReport($miraklProductId, $miraklProductSku, $message);
                    }
                } catch (\Exception $e) {
                    $error = __('Error on line %1: %2', $i, $e->getMessage());
                    $process->output($error);
                    if (isset($miraklProductId) && isset($miraklProductSku)) {
                        $this->writeErrorReport($miraklProductId, $miraklProductSku, $error);
                    }
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
        $from = $to = 0;

        $process->output(__('Sending integration report to Mirakl...'), true);

        foreach (array_chunk($data, $this->cm21BatchSize) as $chunk) {
            $to += count($chunk);
            $process->output(__('Sending report %1 to %2...', $from, $to));
            $this->productApiHelper->export($chunk);
            $from += count($chunk);
        }
    }

    /**
     * Validate presence and value of the identifier
     *
     * @param   array   $data
     * @return  string
     * @throws  \Exception
     */
    protected function validateIdentifier($data)
    {
        if (empty($data[McmHelper::CSV_MIRAKL_PRODUCT_ID])) {
            throw new \Exception(
                __('Column "%1" cannot be empty', McmHelper::CSV_MIRAKL_PRODUCT_ID)
            );
        }

        if (!isset($data[McmHelper::CSV_MIRAKL_PRODUCT_SKU])) {
            throw new \Exception(
                __('Could not find "%1" column in product data', McmHelper::CSV_MIRAKL_PRODUCT_SKU)
            );
        }

        return $data[McmHelper::CSV_MIRAKL_PRODUCT_ID];
    }

    /**
     * @param   string  $miraklProductId
     * @param   string  $productSku
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
     * @param   string  $miraklProductId
     * @param   string  $productSku
     * @param   string  $message
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
