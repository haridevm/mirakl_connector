<?php
namespace Mirakl\Connector\Test\Integration\Model\Offer\Import;

use Mirakl\Connector\Helper\Offer\Import as OffersImport;
use Mirakl\Connector\Model\ResourceModel\Offer\CollectionFactory;
use Mirakl\Connector\Observer\ImportShopsBeforeOffersObserver;
use Mirakl\Core\Test\Integration\TestCase;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Process;

/**
 * @group connector
 * @group OF51
 * @group offers
 * @group import
 */
class ImportOffersLegacyTest extends TestCase
{
    /**
     * @dataProvider importOffersDataProvider
     *
     * @param string $miraklFile
     * @param string $expectedRowsFile
     */
    public function testImportOffers($miraklFile, $expectedRowsFile)
    {
        $observerMock = $this->createMock(ImportShopsBeforeOffersObserver::class);
        $this->objectManager->configure([
            'preferences' => [ImportShopsBeforeOffersObserver::class => get_class($observerMock)],
        ]);

        /** @var Process $process */
        $process = $this->objectManager->create(Process::class);
        $process->setType('TEST OF51 OFFERS IMPORT')
            ->setName('Test of the OF51 offers import')
            ->setStatus(Process::STATUS_IDLE)
            ->setHelper(OffersImport::class)
            ->setMethod('run');

        $processHelper = $this->objectManager->create(ProcessHelper::class);
        $file = $processHelper->saveFile($this->getFilePath($miraklFile));

        $process->setFile($file);
        $process->setQuiet(true);
        $process->run(true);

        /** @var \Mirakl\Connector\Model\ResourceModel\Offer\Collection $collection */
        $collection = $this->objectManager->create(CollectionFactory::class)->create();
        $this->assertCount(33, $collection);

        $offers = $collection->toArray()['items'];
        array_walk($offers, function (&$offer) {
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
            ['OF51_sample_offers_input.csv', 'expected_offers_table_rows.json'],
        ];
    }
}
