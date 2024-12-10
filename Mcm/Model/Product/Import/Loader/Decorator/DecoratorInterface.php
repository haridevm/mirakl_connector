<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Loader\Decorator;

interface DecoratorInterface
{
    /**
     * @param array $rows
     */
    public function decorate(array &$rows): void;
}