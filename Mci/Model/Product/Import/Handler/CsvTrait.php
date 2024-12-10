<?php
namespace Mirakl\Mci\Model\Product\Import\Handler;

use Magento\Framework\Filesystem\File\ReadInterface;
use Mirakl\Core\Model\File\CsvFileTrait;

trait CsvTrait
{
    use CsvFileTrait;

    /**
     * If delimiter defined in key fails (CSV with 1 column) try to use fallbacks defined as value
     *
     * @var array
     */
    protected $availableDelimiters = [
        ';' => [','],
        ',' => [';'],
    ];

    /**
     * @param   ReadInterface|resource    $fh
     * @param   string                    $delimiter
     * @param   string                    $enclosure
     * @return  string|false
     */
    public function getValidDelimiter($fh, $delimiter, $enclosure = '"')
    {
        if (!$fh || !isset($this->availableDelimiters[$delimiter])) {
            return $delimiter;
        }

        $delimiters = $this->availableDelimiters[$delimiter];

        $this->rewind($fh);

        while ($delimiter) {
            $cols = $this->getCsv($fh, $delimiter, $enclosure);

            if (empty($cols) || !is_array($cols)) {
                $delimiter = false;
                break;
            }

            if (count($cols) > 1) {
                break;
            }

            $this->rewind($fh);
            $delimiter = current($delimiters);
            next($delimiters);
        }

        $this->rewind($fh);

        return $delimiter;
    }

    /**
     * @param  ReadInterface|resource $fh
     */
    protected function rewind($fh)
    {
        if ($fh instanceof ReadInterface) {
            $fh->seek(0);
        } else {
            rewind($fh);
        }
    }

    /**
     * @param   ReadInterface|resource    $fh
     * @param   string                    $delimiter
     * @param   string                    $enclosure
     * @return  array|bool
     */
    protected function getCsv($fh, $delimiter, $enclosure)
    {
        if ($fh instanceof ReadInterface) {
            return $this->readCsv($fh, 0, $delimiter, $enclosure);
        }

        return fgetcsv($fh, 0, $delimiter, $enclosure);
    }
}
