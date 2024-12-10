<?php
declare(strict_types=1);

namespace Mirakl\Core\Model\File\FieldCollector;

use Magento\Framework\Filesystem\File\ReadInterface;
use Mirakl\Core\Model\File\CsvFileTrait;

class Csv implements CollectorInterface
{
    use CsvFileTrait;

    /**
     * @inheritdoc
     */
    public function collect(ReadInterface $file, array $fields): array
    {
        $file->seek(0);
        $cols = $this->readCsv($file);

        if (empty($cols)) {
            return [];
        }

        $data = [];

        while (!$file->eof()) {
            $row = $this->readCsv($file);

            if (!is_array($row) || count($cols) !== count($row)) {
                continue;
            }

            $row = array_combine($cols, $row);

            $values = [];
            $keep = false;
            foreach ($fields as $fieldCode => $field) {
                if (!empty($row[$field])) {
                    $values[$fieldCode] = $row[$field];
                    $keep = true;
                } else {
                    $values[$fieldCode] = null;
                }
            }

            // We keep only values that have at least one non empty field
            if ($keep) {
                $data[] = $values;
            }
        }

        return $data;
    }
}
