<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mirakl\Mci\Setup\Patch\Data\CreateMiraklMciAttributes;
use Mirakl\Mcm\Helper\Data as McmHelper;

class UpdateMiraklMcmAttributes implements DataPatchInterface
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
     */
    public function apply(): void
    {
        $setup = $this->setup;
        $setup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->updateAttribute(
            Product::ENTITY,
            McmHelper::ATTRIBUTE_MIRAKL_PRODUCT_ID,
            'backend_type',
            'varchar'
        );

        $eavSetup->updateAttribute(
            Product::ENTITY,
            McmHelper::ATTRIBUTE_MIRAKL_VARIANT_GROUP_CODE,
            'backend_type',
            'varchar'
        );

        $setup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
            CreateMiraklMciAttributes::class
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
