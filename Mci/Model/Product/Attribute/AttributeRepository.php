<?php
declare(strict_types=1);

namespace Mirakl\Mci\Model\Product\Attribute;

use Magento\Framework\App\ResourceConnection;

class AttributeRepository
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param int $setId
     * @return array
     */
    public function getBySetId($setId): array
    {
        $this->load();

        return $this->attributes[$setId] ?? [];
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        $this->load();

        return $this->attributes;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * @return void
     */
    private function load()
    {
        if (null !== $this->attributes) {
            return;
        }

        $select = $this->getConnection()->select()
            ->from(['eea' => 'eav_entity_attribute'], ['attribute_set_id'])
            ->join(['eet' => 'eav_entity_type'], 'eet.entity_type_id = eea.entity_type_id', [])
            ->join(['ea' => 'eav_attribute'], 'ea.attribute_id = eea.attribute_id', ['attribute_code'])
            ->where('eet.entity_type_code = ?', 'catalog_product');

        $this->attributes = [];

        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $setId = $row['attribute_set_id'];
            if (!isset($this->attributes[$setId])) {
                $this->attributes[$setId] = [];
            }
            $this->attributes[$setId][] = $row['attribute_code'];
        }
    }
}