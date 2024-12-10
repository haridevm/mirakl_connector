<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Generator;

interface GeneratorInterface
{
    /**
     * @param array $data
     * @return string
     */
    public function generate(array $data): string;
}
