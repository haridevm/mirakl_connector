<?php

declare(strict_types=1);

namespace Mirakl\Core\Model\ResourceModel\Metadata;

use Magento\Framework\App\ResourceConnection;

class MetadataProvider implements MetadataProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private string $tableName;

    /**
     * @var array
     */
    private array $defaults;

    /**
     * @param ResourceConnection $resourceConnection
     * @param string             $tableName
     * @param array              $defaults
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        string $tableName,
        array $defaults = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->tableName = $tableName;
        $this->defaults = $defaults;
    }

    /**
     * @inheritdoc
     */
    public function getFields(): array
    {
        $connection = $this->resourceConnection->getConnection();

        return $connection->describeTable($this->resourceConnection->getTableName($this->tableName));
    }

    /**
     * @inheritdoc
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }
}
