<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Mirakl\Mcm\Helper\Config;

class Status implements ProcessorInterface
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
        if (null === $product) {
            $data['status'] = $this->config->getValue($this->configPath)
                ? ProductStatus::STATUS_ENABLED
                : ProductStatus::STATUS_DISABLED;
        }
    }
}
