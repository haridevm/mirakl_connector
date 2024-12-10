<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Export\Formatter;

interface FormatterInterface
{
    /**
     * @param array $data
     * @return void
     */
    public function format(array &$data): void;
}
