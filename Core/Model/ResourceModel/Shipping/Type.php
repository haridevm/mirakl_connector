<?php
namespace Mirakl\Core\Model\ResourceModel\Shipping;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Serialize\Serializer\Json;

class Type extends AbstractDb
{
    /**
     * @var Json
     */
    private $json;

    /**
     * Initialize main table
     *
     * @return  void
     */
    protected function _construct()
    {
        $this->_init('mirakl_shipping_type', 'id');
    }

    /**
     * @param Context     $context
     * @param Json        $json
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        Json $json,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $connectionName
        );
        $this->json = $json;
    }

    /**
     * Save/update shipping methods in Magento database
     *
     * @param array $shippingTypes
     */
    public function updateShippingTypes(array $shippingTypes)
    {
        $connection = $this->getConnection();
        $shippingTypeCodes = array_keys($shippingTypes);
        $table = $this->getTable('mirakl_shipping_type');
        // Delete removed/disabled shipping method if there are any
        $connection->delete($table, ['code NOT IN (?)' => $shippingTypeCodes]);
        // We serialize labels and description by locale
        foreach ($shippingTypes as &$shippingType) {
            $shippingType['label'] = $this->json->serialize($shippingType['label']);
            $shippingType['description'] = $this->json->serialize($shippingType['description']);
        }
        // Insert/update shipping methods
        $connection->insertOnDuplicate($table, array_values($shippingTypes));
    }
}
