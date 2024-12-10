<?php
declare(strict_types=1);

namespace Mirakl\Core\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateMiraklCoreProductAttributes implements DataPatchInterface
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

        $this->addProductAttributes($setup);

        $setup->endSetup();
    }

    /**
     * Create custom product attributes
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addProductAttributes(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /***
         * Create aattribute to stores offer's shop and  offer states
         */
        $eavSetup->addAttribute(
            Product::ENTITY,
            'mirakl_shop_ids',
            [
                'group'                     => 'Mirakl Marketplace',
                'type'                      => 'varchar',
                'backend'                   => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'label'                     => 'Shops',
                'input'                     => 'multiselect',
                'visible'                   => true,
                'required'                  => false,
                'user_defined'              => true,
                'searchable'                => true,
                'filterable'                => true,
                'comparable'                => false,
                'visible_on_front'          => false,
                'used_in_product_listing'   => true,
                'unique'                    => false,
                'apply_to'                  => 'simple',
                'note'                      => 'Selected Mirakl shops are associated with the product. This field is automatically filled.',
                'is_configurable'           => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY, 'mirakl_offer_state_ids', [
            'group'                     => 'Mirakl Marketplace',
            'type'                      => 'varchar',
            'backend'                   => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'label'                     => 'Offer Conditions',
            'input'                     => 'multiselect',
            'visible'                   => true,
            'required'                  => false,
            'user_defined'              => true,
            'searchable'                => true,
            'filterable'                => true,
            'comparable'                => false,
            'visible_on_front'          => false,
            'used_in_product_listing'   => true,
            'unique'                    => false,
            'apply_to'                  => 'simple',
            'note'                      => 'Selected Mirakl offer conditions are associated with the product. This field is automatically filled.',
            'is_configurable'           => false,
        ]);

        return $this;
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