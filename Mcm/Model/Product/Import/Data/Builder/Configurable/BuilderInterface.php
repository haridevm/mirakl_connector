<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Data\Builder\Configurable;

interface BuilderInterface
{
    /**
     * Builds the configurable product for the specified variant group code and children
     *
     * @param string $vgc
     * @param array  $children
     * @return array
     */
    public function build(string $vgc, array $children): array;
}
