<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\File\FieldCollector;

use Magento\Framework\Filesystem\File\ReadInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class Json implements CollectorInterface
{
    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @param JsonSerializer $jsonSerializer
     */
    public function __construct(JsonSerializer $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Collect multiple fields along JSON file items
     *
     * $fields parameter must be in format [$fieldCode => $field, $fieldCode => $field, ...]
     *
     * $field must be a string in JSON path format, example: data.category
     * $fieldCode will be used as key in result array
     *
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(ReadInterface $file, array $fields): array
    {
        $data = [];

        if (!$fields) {
            return $data;
        }

        $jsonItems = $this->jsonSerializer->unserialize($file->readAll());

        foreach ($jsonItems as $jsonItem) {
            $values = [];
            $keepValues = false;

            foreach ($fields as $fieldCode => $field) {
                $loopItem = $jsonItem;
                $nodes = explode('.', $field);

                if (!$nodes) {
                    $values[$fieldCode] = null;
                    continue;
                }

                $skip = false;

                foreach ($nodes as $node) {
                    if (isset($loopItem[$node])) {
                        $loopItem = $loopItem[$node];
                    } else {
                        $skip = true;
                    }
                }

                if ($skip || empty($loopItem)) {
                    $values[$fieldCode] = null;
                } else {
                    $values[$fieldCode] = $loopItem;
                    $keepValues = true;
                }
            }

            if ($keepValues) {
                $data[] = $values;
            }
        }

        return $data;
    }
}
