<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

use Magento\Catalog\Model\Product;
use Mirakl\Mcm\Helper\Config;
use Mirakl\Mcm\Helper\Data as McmHelper;

class Visibility implements ProcessorInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $configPath;

    /**
     * @param Config $config
     * @param string $configPath
     */
    public function __construct(
        Config $config,
        string $configPath
    ) {
        $this->config = $config;
        $this->configPath = $configPath;
    }

    /**
     * @inheritdoc
     */
    public function process(array &$data, ?array $product = null): void
    {
        // Get variant group code of product if exists
        $newVGC = $data[McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE] ?? null;
        $oldVGC = $product[McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE] ?? null;

        // Handle visibility for new product and when the product does not have VGC anymore
        if (null === $product || ($newVGC != $oldVGC && !$newVGC)) {
            $data['visibility'] = (int) $this->config->getValue($this->configPath);
        }

        // If product has a variant group code, hide it as it is under a configurable product
        if ($newVGC && $data['product_type'] === Product\Type::TYPE_SIMPLE) {
            $data['visibility'] = Product\Visibility::VISIBILITY_NOT_VISIBLE;
        }
    }
}