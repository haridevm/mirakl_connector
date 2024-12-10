<?php

declare(strict_types=1);

namespace Mirakl\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateMiraklCatalogAttributes implements DataPatchInterface
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
        $this->setup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            'mirakl_sync',
            [
                'group'                   => 'Mirakl Marketplace',
                'type'                    => 'int',
                'backend'                 => '',
                'frontend'                => '',
                'label'                   => 'Mirakl Synchronization',
                // phpcs:ignore
                'note'                    => 'If enabled, product will be synchronized on the Mirakl platform automatically.',
                'input'                   => 'boolean',
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
                'default'                 => 0,
                'used_in_product_listing' => true,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'mirakl_category_id',
            [
                'group'                   => 'Mirakl Marketplace',
                'type'                    => 'int',
                'backend'                 => '',
                'frontend'                => '',
                'label'                   => 'Mirakl Category',
                'note'                    => 'This is the category associated with the product. '
                    . 'This category will be sent during synchronization with Mirakl platform.',
                'input'                   => 'select',
                'class'                   => '',
                'source'                  => \Mirakl\Catalog\Eav\Model\Product\Attribute\Source\Category::class,
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
            'mirakl_authorized_shop_ids',
            [
                'group'                   => 'Mirakl Marketplace',
                'type'                    => 'text',
                'backend'                 => \Mirakl\Catalog\Model\Product\Attribute\Backend\Shop\Authorized::class,
                'frontend'                => '',
                'label'                   => 'Mirakl Authorized Shops',
                // phpcs:ignore
                'note'                    => 'Only selected shops will be allowed to add offers on the product. Leave empty to authorize all shops.',
                'input'                   => 'multiselect',
                'class'                   => '',
                'source'                  => \Mirakl\Connector\Eav\Model\Entity\Attribute\Source\Shop::class,
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
            Category::ENTITY,
            'mirakl_sync',
            [
                'group'            => 'Mirakl Marketplace',
                'type'             => 'int',
                'backend'          => '',
                'frontend'         => '',
                'label'            => 'Mirakl Synchronization',
                // phpcs:ignore
                'note'             => 'If enabled, this category will be automatically synchronized on the Mirakl platform.',
                'input'            => 'boolean',
                'input_renderer'   => '',
                'class'            => '',
                'source'           => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'global'           => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => true,
                'default'          => 0,
                'visible_on_front' => false,
                'unique'           => false,
            ]
        );

        $this->setup->endSetup();
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
