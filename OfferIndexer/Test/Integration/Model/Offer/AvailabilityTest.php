<?php

declare(strict_types=1);

namespace Mirakl\OfferIndexer\Test\Integration\Model\Offer;

use Magento\Store\Api\Data\StoreInterface;
use Mirakl\Connector\Model\Offer;
use Mirakl\OfferIndexer\Model\Offer\Availability;
use Mirakl\OfferIndexer\Model\Offer\AvailabilityInterface;
use Mirakl\OfferIndexer\Test\Integration\TestCase;

/**
 * @group offer_indexer
 * @group model
 * @coversDefaultClass \Mirakl\OfferIndexer\Model\Offer\Availability
 * @covers ::__construct
 */
class AvailabilityTest extends TestCase
{
    /**
     * @covers ::validate
     */
    public function testValidateWithoutValidators()
    {
        $offerMock = $this->createMock(Offer::class);
        $storeMock = $this->createMock(StoreInterface::class);

        $availability = $this->objectManager->create(Availability::class);

        $this->assertTrue($availability->validate($offerMock, $storeMock));
    }

    /**
     * @covers ::validate
     */
    public function testValidateReturnsFalse()
    {
        $offerMock = $this->createMock(Offer::class);
        $storeMock = $this->createMock(StoreInterface::class);

        $validatorMock = $this->createMock(AvailabilityInterface::class);
        $validatorMock->expects($this->once())
            ->method('validate')
            ->with($offerMock, $storeMock)
            ->willReturn(false);

        $availability = $this->objectManager->create(Availability::class, [
            'validators' => [$validatorMock],
        ]);

        $this->assertFalse($availability->validate($offerMock, $storeMock));
    }
}
