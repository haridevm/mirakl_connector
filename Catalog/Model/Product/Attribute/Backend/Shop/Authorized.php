<?php

declare(strict_types=1);

namespace Mirakl\Catalog\Model\Product\Attribute\Backend\Shop;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

class Authorized extends AbstractBackend
{
    /**
     * @inheritdoc
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'mirakl_authorized_shop_ids') {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = [];
            }
            $object->setData($attributeCode, implode(',', $data));
        }

        if ($object->getData($attributeCode) === null) {
            $object->setData($attributeCode, false);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'mirakl_authorized_shop_ids') {
            $data = $object->getData($attributeCode);
            if ($data && is_string($data)) {
                $object->setData($attributeCode, explode(',', $data));
            }
        }

        return $this;
    }
}
