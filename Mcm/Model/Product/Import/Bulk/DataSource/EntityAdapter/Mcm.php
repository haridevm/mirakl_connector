<?php
declare(strict_types=1);

namespace Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\EntityAdapter;

use Mirakl\Mcm\Helper\Data as McmHelper;
use Mirakl\Mcm\Model\Product\Import\Bulk\DataSource\DataSourceInterface;

class Mcm extends \Magento\CatalogImportExport\Model\Import\Product implements EntityAdapterInterface
{
    /**
     * @var string[]
     */
    private $staticFields = [
        McmHelper::ATTRIBUTE_IMAGES_STATUS,
        McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID,
        McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE,
    ];

    /**
     * @inheritdoc
     */
    public function setDataSource(DataSourceInterface $dataSource): void
    {
        $this->_dataSourceModel = $dataSource;
    }

    /**
     * @inheritdoc
     */
    protected function getExistingImages($bunch)
    {
        return []; // Not needed
    }

    /**
     * @inheritdoc
     */
    protected function _saveMediaGallery(array $mediaGalleryData)
    {
        return $this; // Not needed
    }

    /**
     * @inheritdoc
     */
    protected function processRowCategories($rowData)
    {
        return $rowData['categories'] ?? [];
    }

    /**
     * @inheritdoc
     */
    protected function _saveProductsData()
    {
        parent::_saveProductsData();

        $this->_saveMiraklStaticFields();

        return $this;
    }

    /**
     * Default method @see saveProductEntity() does not update custom static fields.
     * So we need to update the 'mirakl_*' fields manually.
     */
    protected function _saveMiraklStaticFields(): void
    {
        $oldSku = $this->getOldSku();
        $entityTable = $this->_resourceFactory->create()->getEntityTable();
        $entityLinkField = $this->getMetadataPool()
            ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $data = array_fill_keys($this->staticFields, []);
            foreach ($bunch as $rowData) {
                $sku = $rowData['sku'];
                foreach ($this->staticFields as $field) {
                    if (isset($rowData[$field]) && isset($oldSku[$sku])) {
                        $data[$field][] = [
                            $entityLinkField => $oldSku[$sku][$entityLinkField],
                            $field => $rowData[$field],
                        ];
                    }
                }
            }
            foreach ($data as $field => $update) {
                if (!empty($update)) {
                    $this->_connection->insertOnDuplicate($entityTable, $update, [$field]);
                }
            }
        }
    }
}