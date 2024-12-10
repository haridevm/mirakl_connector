<?php
declare(strict_types=1);

namespace Mirakl\Mci\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mirakl\Mci\Helper\Data as MciHelper;

class CreateMiraklMciAttributes implements DataPatchInterface
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
     * @param ModuleDataSetupInterface $setup
     * @param EavSetupFactory          $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->setup = $setup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(): void
    {
        $setup = $this->setup;
        $setup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            Category::ENTITY,
            MciHelper::ATTRIBUTE_ATTR_SET,
            [
                'group'            => 'Mirakl Marketplace',
                'type'             => 'int',
                'backend'          => '',
                'frontend'         => '',
                'label'            => 'MCI Attribute Set',
                'note'             => 'Associate an attribute set to this category. It will be used to synchronize Mirakl product attributes with MCI.',
                'input'            => 'select',
                'input_renderer'   => '',
                'class'            => '',
                'source'           => 'Mirakl\Mci\Eav\Model\Entity\Attribute\Source\AttributeSet',
                'global'           => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => true,
                'default'          => 0,
                'visible_on_front' => false,
                'unique'           => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            MciHelper::ATTRIBUTE_SHOPS_SKUS,
            [
                'group'                   => 'Mirakl Marketplace',
                'type'                    => 'text',
                'backend'                 => '',
                'frontend'                => '',
                'label'                   => 'MCI Shops SKUs',
                'note'                    => 'Contains all Mirakl shops SKUs as shop_id1|sku1,shop_id2|sku2... This field is automatically filled.',
                'input'                   => 'text',
                'class'                   => '',
                'source'                  => '',
                'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'                 => true,
                'required'                => false,
                'user_defined'            => true,
                'searchable'              => false,
                'filterable'              => false,
                'comparable'              => false,
                'visible_on_front'        => false,
                'unique'                  => false,
                'apply_to'                => 'simple',
                'is_configurable'         => false,
                'used_in_product_listing' => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            MciHelper::ATTRIBUTE_VARIANT_GROUP_CODES,
            [
                'group'                   => 'Mirakl Marketplace',
                'type'                    => 'text',
                'backend'                 => '',
                'frontend'                => '',
                'label'                   => 'MCI Variant Group Codes',
                'note'                    => 'Contains all Mirakl shops variant group codes as shop_id1|variant_id1,shop_id2|variant_id2... This field is automatically filled.',
                'input'                   => 'text',
                'class'                   => '',
                'source'                  => '',
                'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'                 => true,
                'required'                => false,
                'user_defined'            => true,
                'searchable'              => false,
                'filterable'              => false,
                'comparable'              => false,
                'visible_on_front'        => false,
                'unique'                  => false,
                'apply_to'                => 'configurable',
                'is_configurable'         => false,
                'used_in_product_listing' => false,
            ]
        );

        // Disable Mirakl Exportable status for system attribute because they can not be modified in Magento (bug)
        $select = $setup->getConnection()
            ->select()
            ->from($setup->getTable('eav_attribute'))
            ->columns('attribute_id')
            ->where('frontend_input = ?', 'select')
            ->where('is_user_defined = ?', 0);

        $attributeIds = $setup->getConnection()->fetchCol($select);

        if (!empty($attributeIds)) {
            $setup->getConnection()->update(
                $setup->getTable('catalog_eav_attribute'),
                ['mirakl_is_exportable' => 0],
                ['attribute_id IN (?)' => $attributeIds]
            );
        }

        $setup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}