<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\Formatter\FormatterInterface;

class Formatter
{
    /**
     * @var FormatterInterface[]
     */
    private $formatters;

    /**
     * @param FormatterInterface[] $formatters
     */
    public function __construct(array $formatters = [])
    {
        $this->formatters = $formatters;
    }

    /**
     * @param array               $data
     * @param StoreInterface|null $store
     * @return array
     */
    public function format(array $data, ?StoreInterface $store = null): array
    {
        foreach ($this->formatters as $formatter) {
            $formatter->format($data, $store);
        }

        return $data;
    }
}
