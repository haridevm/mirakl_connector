<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mirakl\Mcm\Helper\Data as McmHelper;

class CreateMiraklMcmAttributes implements DataPatchInterface
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
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply(): void
    {
        $setup = $this->setup;
        $setup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID,
            [
                'group'                   => 'Mirakl Marketplace',
                'type'                    => 'varchar',
                'backend'                 => '',
                'frontend'                => '',
                'label'                   => 'MCM Product Id',
                // phpcs:ignore
                'note'                    => 'Contains Mirakl product id created by MCM. This field is automatically filled.',
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
                'used_in_product_listing' => true,
                'mirakl_is_exportable'    => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            McmHelper::ATTRIBUTE_MIRAKL_IS_OPERATOR_MASTER,
            [
                'group'                   => 'Mirakl Marketplace',
                'type'                    => 'int',
                'backend'                 => '',
                'frontend'                => '',
                'label'                   => 'MCM Is Operator Master',
                // phpcs:ignore
                'note'                    => 'Is the operator master of this product? This field is automatically filled.',
                'input'                   => 'select',
                'class'                   => '',
                'source'                  => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
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
                'default'                 => 1,
                'used_in_product_listing' => true,
                'mirakl_is_exportable'    => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE,
            [
                'group'                   => 'Mirakl Marketplace',
                'type'                    => 'text',
                'backend'                 => '',
                'frontend'                => '',
                'label'                   => 'MCM Variant Group Code',
                // phpcs:ignore
                'note'                    => 'Contains variant group code from MCM import. This field is automatically filled.',
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
                'mirakl_is_exportable'    => false,
            ]
        );

        $setup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
