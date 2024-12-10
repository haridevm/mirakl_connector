<?php

declare(strict_types=1);

namespace Mirakl\Mci\Model\Product\Import\Adapter;

interface AdapterInterface
{
    /**
     * @param string $shopId
     * @param array  $data
     * @return $this
     */
    public function import($shopId, array $data);
}
