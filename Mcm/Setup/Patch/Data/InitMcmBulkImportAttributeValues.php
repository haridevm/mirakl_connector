<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mirakl\Mci\Setup\Patch\Data\CreateMiraklMciAttributes;
use Mirakl\Mcm\Helper\Data as McmHelper;

class InitMcmBulkImportAttributeValues implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param ModuleDataSetupInterface $setup
     * @param EavSetupFactory          $eavSetupFactory
     * @param MetadataPool             $metadataPool
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetupFactory,
        MetadataPool $metadataPool
    ) {
        $this->setup = $setup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritdoc
     */
    public function apply(): void
    {
        $setup = $this->setup;
        $setup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $connection = $setup->getConnection();
        $tableProduct = $setup->getTable('catalog_product_entity');
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        // Attributes to move from EAV table to main table
        $attributes = [
            McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID,
            McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE
        ];

        foreach ($attributes as $attrCode) {
            $attrId = $eavSetup->getAttributeId(Product::ENTITY, $attrCode);

            $select = $connection->select();
            $select->join(
                ['cpev' => $setup->getTable('catalog_product_entity_varchar')],
                "cpe.$linkField = cpev.$linkField",
                []
            );
            $select->where('cpev.attribute_id = ?', $attrId);
            $select->where('cpev.store_id = 0');
            $select->columns([$attrCode => 'cpev.value']);

            // Move values from EAV table to main table
            $query = $connection->updateFromSelect($select, ['cpe' => $tableProduct]);
            $connection->query($query);

            // Delete obsolete values from EAV table
            $query = $select->deleteFromSelect('cpev');
            $connection->query($query);
        }

        $eavSetup->updateAttribute(
            Product::ENTITY,
            McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID,
            'backend_type',
            'static'
        );

        $eavSetup->updateAttribute(
            Product::ENTITY,
            McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE,
            'backend_type',
            'static'
        );

        $setup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
            CreateMiraklMciAttributes::class,
            UpdateMiraklMcmAttributes::class,
            UpdateVariantGroupCodeAttribute::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
