<?php
declare(strict_types=1);

namespace Mirakl\OfferIndexer\DB\Trigger\Creator;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

abstract class AbstractTriggerCreator implements TriggerCreatorInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return AdapterInterface
     */
    protected function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * @param string $table
     * @return string
     */
    protected function getTable(string $table): string
    {
        return $this->resourceConnection->getTableName($table);
    }

    /**
     * @param string $tableName
     * @return string
     */
    protected function quoteTable(string $tableName): string
    {
        return $this->getConnection()->quoteIdentifier($this->getTable($tableName));
    }
}
