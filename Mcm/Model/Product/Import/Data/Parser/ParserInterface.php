<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Parser;

interface ParserInterface
{
    /**
     * @param string|array $value
     * @return array
     */
    public function parse($value): array;
}