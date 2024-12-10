<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Generator;

class Sku implements GeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate(array $data): string
    {
        return substr(sha1(uniqid()), 0, 8);
    }
}