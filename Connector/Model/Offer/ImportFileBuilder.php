<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model\Offer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\ReadInterface;
use Mirakl\Connector\Model\Offer\Import\DataBuilderInterface;

class ImportFileBuilder
{
    /**
     * @var string
     */
    public $delimiter = ';';

    /**
     * @var string
     */
    public $enclosure = '"';

    /**
     * @var string
     */
    protected $tmpFile;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DataBuilderInterface
     */
    protected $dataBuilder;

    /**
     * @param Filesystem           $filesystem
     * @param DataBuilderInterface $dataBuilder
     */
    public function __construct(
        Filesystem $filesystem,
        DataBuilderInterface $dataBuilder
    ) {
        $this->filesystem = $filesystem;
        $this->dataBuilder = $dataBuilder;
    }

    /**
     * Remove temp file if set
     */
    public function __destruct()
    {
        if ($this->tmpFile) {
            @unlink($this->tmpFile); // phpcs:ignore
        }
    }

    /**
     * @param string $file
     * @return string
     */
    public function build($file)
    {
        return $this->buildFile($this->buildData($file));
    }

    /**
     * Creates a temp file of offers to import
     *
     * @param array $data
     * @return string
     */
    public function buildFile(array $data)
    {
        $this->tmpFile = $this->createTempFile();
        $fhOut = fopen($this->tmpFile, 'w');

        if (!empty($data)) {
            $this->writeCsv($fhOut, array_keys($data[0]));

            foreach ($data as $offer) {
                $this->writeCsv($fhOut, $offer);
            }
        }

        fclose($fhOut);

        return $this->tmpFile;
    }

    /**
     * Builds an array of offers to import
     *
     * @param string $file
     * @return array
     */
    public function buildData($file)
    {
        $data = [];
        $fhIn = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->openFile($file, 'r');
        $fileColumns = $this->cleanFileColumns($this->readCsv($fhIn));

        while ($offer = $this->readCsv($fhIn)) {
            $offer = array_combine($fileColumns, $offer);
            $data[] = $this->dataBuilder->build($offer);
        }

        $fhIn->close();

        return $data;
    }

    /**
     * @param array $cols
     * @return array
     */
    private function cleanFileColumns(array $cols)
    {
        return str_replace('-', '_', $cols);
    }

    /**
     * @return string
     */
    protected function createTempFile()
    {
        return tempnam(sys_get_temp_dir(), 'mirakl_offers_');
    }

    /**
     * @param ReadInterface $fh
     * @return array
     */
    protected function readCsv($fh)
    {
        // We used the char "\x80" as escape_char to avoid problem when we have a \ before a double quote
        return $fh->readCsv(0, $this->delimiter, $this->enclosure, "\x80");
    }

    /**
     * @param resource $fh
     * @param array    $data
     * @return bool|int
     */
    protected function writeCsv($fh, array $data)
    {
        return fputcsv($fh, $data, $this->delimiter, $this->enclosure, "\x80");
    }
}
