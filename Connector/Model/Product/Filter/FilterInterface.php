<?php
declare(strict_types=1);

namespace Mirakl\Connector\Model\Product\Filter;

interface FilterInterface
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function filter($value);
}
