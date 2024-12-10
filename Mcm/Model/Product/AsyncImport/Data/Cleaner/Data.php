<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\AsyncImport\Data\Cleaner;

use Mirakl\Mci\Model\Product\Import\Exception\SkipException;
use Mirakl\Mcm\Model\Product\Import\Data\Cleaner\CleanerInterface;

class Data implements CleanerInterface
{
    /**
     * @inheritdoc
     */
    public function clean(array &$data): void
    {
        if (!isset($data['data'])) {
            throw new SkipException(__("The 'data' node could not be found, skipping."));
        }

        $data = array_merge($data, $data['data']);
    }
}