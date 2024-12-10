<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\File;

use Magento\Framework\Filesystem\File\ReadInterface;

trait CsvFileTrait
{
    /**
     * @param ReadInterface $file
     * @param int           $length
     * @param string        $delimiter
     * @param string        $enclosure
     * @param string        $escape
     * @return array|bool
     */
    public function readCsv(
        ReadInterface $file,
        int $length = 0,
        string $delimiter = ';',
        string $enclosure = '"',
        string $escape = "\x80"
    ) {
        return $file->readCsv($length, $delimiter, $enclosure, $escape);
    }
}
