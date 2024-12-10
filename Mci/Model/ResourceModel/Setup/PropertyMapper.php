<?php

declare(strict_types=1);

namespace Mirakl\Mci\Model\ResourceModel\Setup;

use Magento\Eav\Model\Entity\Setup\PropertyMapperAbstract;

/**
 * Attribute property mapper for Mirakl fields
 */
class PropertyMapper extends PropertyMapperAbstract
{
    /**
     * Fields and default values taken from Mci/etc/db_schema.xml
     *
     * @inheritdoc
     */
    public function map(array $input, $entityTypeId): array
    {
        return [
            'mirakl_is_variant'     => $this->_getValue($input, 'mirakl_is_variant', 0),
            'mirakl_is_exportable'  => $this->_getValue($input, 'mirakl_is_exportable', 1),
            'mirakl_is_localizable' => $this->_getValue($input, 'mirakl_is_localizable', 0)
        ];
    }
}
