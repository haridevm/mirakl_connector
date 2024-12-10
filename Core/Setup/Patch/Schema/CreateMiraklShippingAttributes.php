<?php

declare(strict_types=1);

namespace Mirakl\Core\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

class CreateMiraklShippingAttributes implements SchemaPatchInterface
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

        $this->addQuotePricesExclTaxFields($setup);
        $this->addSalesPricesExclTaxFields($setup);

        // Add Mirakl custom taxes fields for Mirakl Tax Calculator feature
        $this->addMiraklCustomTaxesFields($setup);

        // Add column 'mirakl_is_shipping_incl_tax' on quote and order entities
        $attributes = [
            'quote' => [
                'mirakl_is_shipping_incl_tax' => [
                    'type' => Table::TYPE_SMALLINT, 'default' => 1
                ]
            ]
        ];
        $this->addQuoteAttributes($setup, $attributes);

        $attributes = [
            'order' => [
                'mirakl_is_shipping_incl_tax' => [
                    'type' => Table::TYPE_SMALLINT,
                    'default' => 1
                ]
            ]
        ];
        $this->addOrderAttributes($setup, $attributes);

        $this->addQuoteShippingPricesTaxFields($setup);
        $this->addSalesShippingPricesTaxFields($setup);
        $this->upgradeMiraklShippingFields($setup);

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addMiraklCustomTaxesFields(ModuleDataSetupInterface $setup)
    {
        $attributes = [
            'quote' => [
                'mirakl_base_custom_shipping_tax_amount' => ['type' => Table::TYPE_DECIMAL],
                'mirakl_custom_shipping_tax_amount'      => ['type' => Table::TYPE_DECIMAL],
            ],
            'quote_item' => [
                'mirakl_custom_tax_applied'              => ['type' => Table::TYPE_TEXT],
                'mirakl_base_custom_shipping_tax_amount' => ['type' => Table::TYPE_DECIMAL],
                'mirakl_custom_shipping_tax_amount'      => ['type' => Table::TYPE_DECIMAL],
            ],
        ];

        $this->addQuoteAttributes($setup, $attributes);

        $attributes = [
            'order' => [
                'mirakl_base_custom_shipping_tax_amount' => ['type' => Table::TYPE_DECIMAL],
                'mirakl_custom_shipping_tax_amount'      => ['type' => Table::TYPE_DECIMAL],
            ],
            'order_item' => [
                'mirakl_custom_tax_applied'              => ['type' => Table::TYPE_TEXT],
                'mirakl_base_custom_shipping_tax_amount' => ['type' => Table::TYPE_DECIMAL],
                'mirakl_custom_shipping_tax_amount'      => ['type' => Table::TYPE_DECIMAL],
            ],
        ];

        $this->addOrderAttributes($setup, $attributes);

        return $this;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param array                    $attributes
     * @return $this
     */
    private function addQuoteAttributes(ModuleDataSetupInterface $setup, array $attributes)
    {
        $quoteSetup = $this->getQuoteSetup($setup);

        foreach ($attributes as $entityTypeId => $attrParams) {
            foreach ($attrParams as $code => $params) {
                $params['visible'] = false;
                $quoteSetup->addAttribute($entityTypeId, $code, $params);
            }
        }

        return $this;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param array                    $attributes
     * @return $this
     */
    private function addOrderAttributes(ModuleDataSetupInterface $setup, array $attributes)
    {
        $salesSetup = $this->getSalesSetup($setup);

        foreach ($attributes as $entityTypeId => $attrParams) {
            foreach ($attrParams as $code => $params) {
                $params['visible'] = false;
                $salesSetup->addAttribute($entityTypeId, $code, $params);
            }
        }

        return $this;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addQuotePricesExclTaxFields(ModuleDataSetupInterface $setup)
    {
        $attributes = [
            'quote' => [
                'mirakl_is_offer_incl_tax'        => ['type' => Table::TYPE_SMALLINT, 'default' => 1],
                'mirakl_base_shipping_tax_amount' => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_tax_amount'      => ['type' => Table::TYPE_DECIMAL],
            ],
            'quote_item' => [
                'mirakl_shipping_tax_percent'     => ['type' => Table::TYPE_DECIMAL],
                'mirakl_base_shipping_tax_amount' => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_tax_amount'      => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_tax_applied'     => ['type' => Table::TYPE_TEXT],
            ],
        ];

        return $this->addQuoteAttributes($setup, $attributes);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addQuoteShippingPricesTaxFields(ModuleDataSetupInterface $setup)
    {
        $attributes = [
            'quote' => [
                'mirakl_base_shipping_excl_tax' => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_shipping_fee'
                ],
                'mirakl_shipping_excl_tax'      => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_base_shipping_excl_tax'
                ],
                'mirakl_base_shipping_incl_tax' => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_shipping_excl_tax'
                ],
                'mirakl_shipping_incl_tax'      => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_base_shipping_incl_tax'
                ],
            ],
            'quote_item' => [
                'mirakl_base_shipping_excl_tax' => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_shipping_fee'
                ],
                'mirakl_shipping_excl_tax'      => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_base_shipping_excl_tax'
                ],
                'mirakl_base_shipping_incl_tax' => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_shipping_excl_tax'
                ],
                'mirakl_shipping_incl_tax'      => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_base_shipping_incl_tax'
                ],
            ],
        ];

        return $this->addQuoteAttributes($setup, $attributes);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addSalesPricesExclTaxFields(ModuleDataSetupInterface $setup)
    {
        $attributes = [
            'order' => [
                'mirakl_is_offer_incl_tax'        => ['type' => Table::TYPE_SMALLINT, 'default' => 1],
                'mirakl_base_shipping_tax_amount' => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_tax_amount'      => ['type' => Table::TYPE_DECIMAL],
            ],
            'order_item' => [
                'mirakl_shipping_tax_percent'     => ['type' => Table::TYPE_DECIMAL],
                'mirakl_base_shipping_tax_amount' => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_tax_amount'      => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_tax_applied'     => ['type' => Table::TYPE_TEXT],
            ],
        ];

        return $this->addOrderAttributes($setup, $attributes);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addSalesShippingPricesTaxFields(ModuleDataSetupInterface $setup)
    {
        $attributes = [
            'order' => [
                'mirakl_base_shipping_excl_tax'     => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_shipping_fee'
                ],
                'mirakl_shipping_excl_tax'          => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_base_shipping_excl_tax'
                ],
                'mirakl_base_shipping_incl_tax'     => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_shipping_excl_tax'
                ],
                'mirakl_shipping_incl_tax'          => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_base_shipping_incl_tax'
                ],
                'mirakl_base_shipping_refunded'     => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_custom_shipping_tax_amount'
                ],
                'mirakl_shipping_refunded'          => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_base_shipping_refunded'
                ],
                'mirakl_base_shipping_tax_refunded' => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_shipping_refunded'
                ],
                'mirakl_shipping_tax_refunded'      => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_base_shipping_tax_refunded'
                ],
            ],
            'order_item' => [
                'mirakl_base_shipping_excl_tax' => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_shipping_fee'
                ],
                'mirakl_shipping_excl_tax'      => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_base_shipping_excl_tax'
                ],
                'mirakl_base_shipping_incl_tax' => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_shipping_excl_tax'
                ],
                'mirakl_shipping_incl_tax'      => [
                    'type'  => Table::TYPE_DECIMAL,
                    'after' => 'mirakl_base_shipping_incl_tax'
                ],
            ],
            'invoice' => [
                'mirakl_base_shipping_excl_tax'   => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_excl_tax'        => ['type' => Table::TYPE_DECIMAL],
                'mirakl_base_shipping_incl_tax'   => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_incl_tax'        => ['type' => Table::TYPE_DECIMAL],
                'mirakl_base_shipping_tax_amount' => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_tax_amount'      => ['type' => Table::TYPE_DECIMAL],
            ],
            'invoice_item' => [
                'mirakl_base_shipping_excl_tax'   => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_excl_tax'        => ['type' => Table::TYPE_DECIMAL],
                'mirakl_base_shipping_incl_tax'   => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_incl_tax'        => ['type' => Table::TYPE_DECIMAL],
                'mirakl_base_shipping_tax_amount' => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_tax_amount'      => ['type' => Table::TYPE_DECIMAL],
            ],
            'creditmemo' => [
                'mirakl_base_shipping_excl_tax' => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_excl_tax'      => ['type' => Table::TYPE_DECIMAL],
                'mirakl_base_shipping_incl_tax' => ['type' => Table::TYPE_DECIMAL],
                'mirakl_shipping_incl_tax'      => ['type' => Table::TYPE_DECIMAL],
            ],
        ];

        return $this->addOrderAttributes($setup, $attributes);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function upgradeMiraklShippingFields(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();

        // Update sales_order fields
        $updateSalesOrderSql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $setup->getTable('sales_order'),
            implode(', ', [
                'mirakl_base_shipping_excl_tax = ' . $connection->getCheckSql(
                    'mirakl_is_shipping_incl_tax = 0',
                    'mirakl_base_shipping_fee',
                    'mirakl_base_shipping_fee - IFNULL(mirakl_base_shipping_tax_amount, 0) '
                        . '- IFNULL(mirakl_base_custom_shipping_tax_amount, 0)'
                ),
                'mirakl_shipping_excl_tax = ' . $connection->getCheckSql(
                    'mirakl_is_shipping_incl_tax = 0',
                    'mirakl_shipping_fee',
                    'mirakl_shipping_fee - IFNULL(mirakl_shipping_tax_amount, 0) '
                        . '- IFNULL(mirakl_custom_shipping_tax_amount, 0)'
                ),
                'mirakl_base_shipping_incl_tax = ' . $connection->getCheckSql(
                    'mirakl_is_shipping_incl_tax = 1',
                    'mirakl_base_shipping_fee',
                    'mirakl_base_shipping_fee + IFNULL(mirakl_base_shipping_tax_amount, 0) '
                        . '+ IFNULL(mirakl_base_custom_shipping_tax_amount, 0)'
                ),
                'mirakl_shipping_incl_tax = ' . $connection->getCheckSql(
                    'mirakl_is_shipping_incl_tax = 1',
                    'mirakl_shipping_fee',
                    'mirakl_shipping_fee + IFNULL(mirakl_shipping_tax_amount, 0) '
                        . '+ IFNULL(mirakl_custom_shipping_tax_amount, 0)'
                ),
            ]),
            'mirakl_shipping_fee IS NOT NULL AND mirakl_shipping_excl_tax IS NULL'
        );
        $connection->query($updateSalesOrderSql);

        // Update sales_order_item fields
        $updateSalesOrderItemSql = sprintf(
            'UPDATE %s AS items INNER JOIN %s AS orders ON items.order_id = orders.entity_id SET %s WHERE %s',
            $setup->getTable('sales_order_item'),
            $setup->getTable('sales_order'),
            implode(', ', [
                'items.mirakl_base_shipping_excl_tax = ' . $connection->getCheckSql(
                    'orders.mirakl_is_shipping_incl_tax = 0',
                    'items.mirakl_base_shipping_fee',
                    'items.mirakl_base_shipping_fee - IFNULL(items.mirakl_base_shipping_tax_amount, 0) '
                        . '- IFNULL(items.mirakl_base_custom_shipping_tax_amount, 0)'
                ),
                'items.mirakl_shipping_excl_tax = ' . $connection->getCheckSql(
                    'orders.mirakl_is_shipping_incl_tax = 0',
                    'items.mirakl_shipping_fee',
                    'items.mirakl_shipping_fee - IFNULL(items.mirakl_shipping_tax_amount, 0) '
                        . '- IFNULL(items.mirakl_custom_shipping_tax_amount, 0)'
                ),
                'items.mirakl_base_shipping_incl_tax = ' . $connection->getCheckSql(
                    'orders.mirakl_is_shipping_incl_tax = 1',
                    'items.mirakl_base_shipping_fee',
                    'items.mirakl_base_shipping_fee + IFNULL(items.mirakl_base_shipping_tax_amount, 0) '
                        . '+ IFNULL(items.mirakl_base_custom_shipping_tax_amount, 0)'
                ),
                'items.mirakl_shipping_incl_tax = ' . $connection->getCheckSql(
                    'orders.mirakl_is_shipping_incl_tax = 1',
                    'items.mirakl_shipping_fee',
                    'items.mirakl_shipping_fee + IFNULL(items.mirakl_shipping_tax_amount, 0) '
                        . '+ IFNULL(items.mirakl_custom_shipping_tax_amount, 0)'
                ),
            ]),
            'items.mirakl_shipping_fee IS NOT NULL AND items.mirakl_shipping_excl_tax IS NULL'
        );

        $connection->query($updateSalesOrderItemSql);

        // Fix old order amounts according to the new created fields
        $fixOldOrdersAmounts = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $setup->getTable('sales_order'),
            implode(', ', [
                'base_shipping_amount = base_shipping_amount - mirakl_base_shipping_excl_tax',
                'base_tax_amount = base_tax_amount - mirakl_base_shipping_tax_amount '
                    . '- mirakl_base_custom_shipping_tax_amount',
                'shipping_amount = shipping_amount - mirakl_shipping_excl_tax',
                'tax_amount = tax_amount - mirakl_shipping_tax_amount - mirakl_custom_shipping_tax_amount',
                'base_shipping_incl_tax = base_shipping_incl_tax - mirakl_base_shipping_incl_tax',
                'shipping_incl_tax = shipping_incl_tax - mirakl_shipping_incl_tax',
            ]),
            'mirakl_base_shipping_excl_tax > 0 AND base_grand_total - base_subtotal '
                . '- base_shipping_amount - base_tax_amount = 0'
        );

        $connection->query($fixOldOrdersAmounts);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return \Magento\Quote\Setup\QuoteSetup
     */
    private function getQuoteSetup(ModuleDataSetupInterface $setup)
    {
        return $this->quoteSetupFactory->create(['setup' => $setup]);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return \Magento\Sales\Setup\SalesSetup
     */
    private function getSalesSetup(ModuleDataSetupInterface $setup)
    {
        return $this->salesSetupFactory->create(['setup' => $setup]);
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
            CreateMiraklCoreOrderAttributes::class
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
