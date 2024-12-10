<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Setup\Patch\Schema;

use Mirakl\OfferIndexer\Setup\Patch\Schema\CreateLegacyStockStatusTriggers;
use Mirakl\OfferIndexer\Test\Integration\TestCase;

/**
 * @group offer_indexer
 * @group setup
 * @coversDefaultClass \Mirakl\OfferIndexer\Setup\Patch\Schema\CreateLegacyStockStatusTriggers
 * @covers ::__construct
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class CreateLegacyStockStatusTriggersTest extends TestCase
{
    /**
     * @var CreateLegacyStockStatusTriggers
     */
    private $patch;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->patch = $this->objectManager->create(CreateLegacyStockStatusTriggers::class);
    }

    /**
     * @covers ::getAliases
     */
    public function testGetAliases()
    {
        $this->assertEmpty($this->patch->getAliases());
    }

    /**
     * @covers ::getDependencies
     */
    public function testGetDependencies()
    {
        $this->assertEmpty($this->patch->getDependencies());
    }

    /**
     * @dataProvider getTestApplyDataProvider
     * @covers ::apply
     * @covers ::createTriggers
     */
    public function testApply(array $triggers)
    {
        foreach ($triggers as $triggerName) {
            $this->resource->getConnection()->dropTrigger($triggerName);
        }

        $dbTriggers = $this->getDbTriggers();

        foreach ($triggers as $triggerName) {
            $this->assertArrayNotHasKey($triggerName, $dbTriggers);
        }

        $this->patch->apply();

        $dbTriggers = $this->getDbTriggers();

        $this->assertGreaterThan(0, count($dbTriggers));

        foreach ($triggers as $triggerName) {
            $this->assertArrayHasKey($triggerName, $dbTriggers);
        }
    }

    /**
     * @return array[]
     */
    public function getTestApplyDataProvider(): array
    {
        return [
            [
                [
                    'mirakl_stock_before_insert_0',
                    'mirakl_stock_before_update_0',
                    'mirakl_stock_before_insert_1',
                    'mirakl_stock_before_update_1',
                ],
            ],
        ];
    }
}
