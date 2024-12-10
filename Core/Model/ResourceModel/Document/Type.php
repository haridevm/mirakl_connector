<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\ResourceModel\Document;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Type extends AbstractDb
{
    /**
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _construct()
    {
        // Table Name and Primary Key column
        $this->_init('mirakl_document_type', 'id');
    }
}
