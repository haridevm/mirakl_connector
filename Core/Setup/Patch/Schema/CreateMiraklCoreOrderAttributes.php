<?php

declare(strict_types=1);

namespace Mirakl\Core\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

class CreateMiraklCoreOrderAttributes implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @param ModuleDataSetupInterface $setup
     * @param QuoteSetupFactory        $quoteSetupFactory
     * @param SalesSetupFactory        $salesSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->setup = $setup;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply(): void
    {
        $setup = $this->setup;
        $setup->startSetup();

        $this->addQuoteAttributes($setup);
        $this->addSalesAttributes($setup);

        $setup->endSetup();
    }

    /**
     * Create quote additional attributes
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addQuoteAttributes(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);

        $attributes = [
            'quote' => [
                'mirakl_shipping_zone'       => ['type' => Table::TYPE_TEXT, 'size' => 255],
                'mirakl_base_shipping_fee'   => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_fee'        => ['type' => Table::TYPE_DECIMAL],
            ],
            'quote_item' => [
                'mirakl_offer_id'            => ['type' => Table::TYPE_INTEGER],
                'mirakl_shop_id'             => ['type' => Table::TYPE_INTEGER],
                'mirakl_shop_name'           => ['type' => Table::TYPE_TEXT, 'size' => 255],
                'mirakl_leadtime_to_ship'    => ['type' => Table::TYPE_INTEGER],
                'mirakl_shipping_type'       => ['type' => Table::TYPE_TEXT, 'size' => 255],
                'mirakl_shipping_type_label' => ['type' => Table::TYPE_TEXT, 'size' => 255],
                'mirakl_base_shipping_fee'   => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_fee'        => ['type' => Table::TYPE_DECIMAL],
            ],
        ];

        foreach ($attributes as $entityTypeId => $attrParams) {
            foreach ($attrParams as $code => $params) {
                $params['visible'] = false;
                $quoteSetup->addAttribute($entityTypeId, $code, $params);
            }
        }

        return $this;
    }

    /**
     * Create sales additional attributes
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addSalesAttributes(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        $attributes = [
            'order' => [
                'mirakl_shipping_zone'       => ['type' => Table::TYPE_TEXT, 'size' => 255],
                'mirakl_base_shipping_fee'   => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_fee'        => ['type' => Table::TYPE_DECIMAL],
                'mirakl_sent'                => ['type' => Table::TYPE_BOOLEAN, 'default' => 0],
            ],
            'order_item' => [
                'mirakl_offer_id'            => ['type' => Table::TYPE_INTEGER],
                'mirakl_shop_id'             => ['type' => Table::TYPE_INTEGER],
                'mirakl_shop_name'           => ['type' => Table::TYPE_TEXT, 'size' => 255],
                'mirakl_leadtime_to_ship'    => ['type' => Table::TYPE_INTEGER],
                'mirakl_shipping_type'       => ['type' => Table::TYPE_TEXT, 'size' => 255],
                'mirakl_shipping_type_label' => ['type' => Table::TYPE_TEXT, 'size' => 255],
                'mirakl_base_shipping_fee'   => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_fee'        => ['type' => Table::TYPE_DECIMAL],
            ],
        ];

        foreach ($attributes as $entityTypeId => $attrParams) {
            foreach ($attrParams as $code => $params) {
                $params['visible'] = false;
                $salesSetup->addAttribute($entityTypeId, $code, $params);
            }
        }

        return $this;
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
