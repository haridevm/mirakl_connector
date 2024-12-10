<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Adapter;

interface AdapterInterface
{
    /**
     * Executed BEFORE all the products are imported
     *
     * @return void
     */
    public function before();

    /**
     * Imports a single product
     *
     * @param array $data Data coming from Mirakl
     * @return string SKU of the product
     */
    public function import(array $data);

    /**
     * Executed AFTER all the products are imported
     *
     * @return void
     */
    public function after();
}
