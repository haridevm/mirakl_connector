<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class TestCase extends \Mirakl\Core\Test\Integration\TestCase
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->resource = $this->objectManager->get(ResourceConnection::class);
    }

    /**
     * @return array
     */
    protected function getDbTriggers(): array
    {
        $connection = $this->resource->getConnection();
        $dbName = $this->resource->getSchemaName(ResourceConnection::DEFAULT_CONNECTION);
        $sql = $connection->select()
            ->from(
                ['information_schema.TRIGGERS'],
                ['TRIGGER_NAME', 'ACTION_STATEMENT', 'EVENT_OBJECT_TABLE']
            )
            ->where('TRIGGER_SCHEMA = ?', $dbName);

        return $connection->fetchAssoc($sql);
    }
}
