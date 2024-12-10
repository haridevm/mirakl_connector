<?php
namespace Mirakl\Connector\Test\Integration\Model\Offer\Import;

use Mirakl\Connector\Model\Offer\AsyncImport\GetOffersExportFile as OffersImport;
use Mirakl\Connector\Model\ResourceModel\Offer\CollectionFactory;
use Mirakl\Core\Test\Integration\TestCase;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Process;

/**
 * @group connector
 * @group OF54
 * @group offers
 * @group import
 */
class ImportOffersAsyncTest extends TestCase
{
    /**
     * @dataProvider importOffersDataProvider
     *
     * @magentoDbIsolation enabled
     *
     * @param string $miraklFile
     * @param string $expectedRowsFile
     * @param array $expectedAdditionalInfo
     */
    public function testImportOffers($miraklFile, $expectedRowsFile, array $expectedAdditionalInfo = [])
    {
        /** @var Process $process */
        $process = $this->objectManager->create(Process::class);
        $process->setType('TEST OF54 OFFERS IMPORT')
            ->setName('Test of the OF54 offers import')
            ->setStatus(Process::STATUS_IDLE)
            ->setHelper(OffersImport::class)
            ->setMethod('execute');

        $processHelper = $this->objectManager->create(ProcessHelper::class);
        $file = $processHelper->saveFile($this->getFilePath($miraklFile), 'json');

        $process->setFile($file);
        $process->setQuiet(true);
        $process->run(true);

        /** @var \Mirakl\Connector\Model\ResourceModel\Offer\Collection $collection */
        $collection = $this->objectManager->create(CollectionFactory::class)->create();
        $this->assertCount(33, $collection);

        $offers = $collection->toArray()['items'];
        array_walk($offers, function (&$offer) use ($expectedAdditionalInfo) {
            if (isset($expectedAdditionalInfo[$offer['offer_id']])) {
                $offerAdditionalInfo = json_decode($offer['additional_info'], true);
                foreach ($expectedAdditionalInfo[$offer['offer_id']] as $key => $value) {
                    $this->assertArrayHasKey($key, $offerAdditionalInfo);
                    $this->assertEquals($value, $offerAdditionalInfo[$key]);
                }
            }
            unset($offer['additional_info']);
        });
        $expectedRows = $this->_getJsonFileContents($expectedRowsFile);
        $this->assertEquals($expectedRows, $offers);
    }

    /**
     * @return array
     */
    public function importOffersDataProvider()
    {
        return [
            ['OF54_sample_offers_input.json', 'expected_offers_table_rows.json', [
                2231 => [
                    'origin_price[channel=FR]' => 89,
                    'origin_price[channel=IT]' => 59,
                ],
                2239 => [
                    'fulfillment_center_code' => 'DEFAULT',
                ],
                2247 => [
                    'discount_price[channel=IT]' => 43,
                ],
            ]],
        ];
    }
}
