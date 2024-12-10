<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Parser;

class MultiValue implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public function parse($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return preg_split('/[^\d]/', $value, -1, PREG_SPLIT_NO_EMPTY);
    }
}
