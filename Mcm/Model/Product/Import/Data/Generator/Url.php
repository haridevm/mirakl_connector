<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Generator;

use Magento\Catalog\Model\Product;

class Url implements GeneratorInterface
{
    /**
     * @var Product\Url
     */
    private $productUrl;

    /**
     * @param Product\Url $productUrl
     */
    public function __construct(Product\Url $productUrl)
    {
        $this->productUrl = $productUrl;
    }

    /**
     * @param array $data
     * @return string
     */
    public function generate(array $data): string
    {
        $str = $data['name'] . ' ' . $data['sku'];

        return $this->productUrl->formatUrlKey($str);
    }
}
