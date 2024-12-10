<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Processor;

use Mirakl\Mcm\Helper\Config;

class TaxClass implements ProcessorInterface
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
            $taxClassId = (int) $this->config->getValue($this->configPath);
            $data['tax_class_id'] = $taxClassId ?: '';
        }
    }
}
