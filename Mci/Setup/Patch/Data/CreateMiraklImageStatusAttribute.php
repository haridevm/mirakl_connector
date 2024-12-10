<?php

declare(strict_types=1);

namespace Mirakl\Mci\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Mirakl\Mci\Helper\Data as MciHelper;
use Mirakl\Mci\Helper\Product\Image as ImageHelper;

class CreateMiraklImageStatusAttribute implements DataPatchInterface
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
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @param ModuleDataSetupInterface $setup
     * @param EavSetupFactory          $eavSetupFactory
     * @param ImageHelper              $imageHelper
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetupFactory,
        ImageHelper $imageHelper
    ) {
        $this->setup = $setup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @inheritdoc
     */
    public function apply(): void
    {
        $setup = $this->setup;
        $setup->startSetup();

        $this->addMiraklImagesStatusAttribute($setup);
        $this->updateMiraklImagesToProcess();

        $setup->endSetup();
    }

    /**
     * Creates a new 'mirakl_images_status' attribute to handle processed and unprocessed images.
     * It is created as a static attribute on the 'catalog_product_entity' table in order to benefit from the index.
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function addMiraklImagesStatusAttribute(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            MciHelper::ATTRIBUTE_IMAGES_STATUS,
            [
                'type'                    => 'static',
                'input'                   => 'text',
                'required'                => false,
                'visible'                 => false,
                'used_in_product_listing' => false,
            ]
        );

        $tableProduct = $setup->getTable('catalog_product_entity');

        $setup->getConnection()->update(
            $tableProduct,
            [MciHelper::ATTRIBUTE_IMAGES_STATUS => ImageHelper::IMAGES_IMPORT_STATUS_PROCESSED]
        );
    }

    /**
     * Marks current unprocessed images to PENDING on the new 'mirakl_images_status' attribute.
     */
    private function updateMiraklImagesToProcess()
    {
        $collection = $this->imageHelper->getProductsToProcessByQueryParam();

        $this->imageHelper->markProductsImagesAsPending($collection);
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
