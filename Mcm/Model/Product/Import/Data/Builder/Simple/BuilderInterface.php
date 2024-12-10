<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Builder\Simple;

interface BuilderInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function build(array $data): array;
}