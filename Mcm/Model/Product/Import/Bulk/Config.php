<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk;

use Mirakl\Mcm\Model\Product\Import\Bulk\Type;

class Config extends \Magento\ImportExport\Model\Import\Config
{
    /**
     * @inheritdoc
     */
    public function getEntityTypes($entity)
    {
        return [
            'simple' => [
                'name'  => 'simple',
                'model' => Type\Simple::class,
            ],
            'configurable' => [
                'name'  => 'configurable',
                'model' => Type\Configurable::class,
            ],
        ];
    }
}