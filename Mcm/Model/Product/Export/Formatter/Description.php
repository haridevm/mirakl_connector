<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Export\Formatter;

use Mirakl\Connector\Model\Product\Filter;

class Description implements FormatterInterface
{
    /**
     * @var Filter\Description
     */
    private $descriptionFilter;

    /**
     * @param Filter\Description $descriptionFilter
     */
    public function __construct(Filter\Description $descriptionFilter)
    {
        $this->descriptionFilter = $descriptionFilter;
    }

    /**
     * @inheritdoc
     */
    public function format(array &$data): void
    {
        if (empty($data['description'])) {
            return;
        }

        $data['description'] = $this->descriptionFilter->filter($data['description']);
    }
}
