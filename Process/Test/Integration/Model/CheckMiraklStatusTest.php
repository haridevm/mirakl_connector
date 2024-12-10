<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Phrase;
use Mirakl\Api\Helper\SynchroResultInterface;
use Mirakl\Core\Domain\ProcessTrackingStatus;
use Mirakl\MMP\FrontOperator\Domain\Synchro\AbstractSynchroResult;
use Mirakl\Process\Model\CheckMiraklStatus;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\CheckMiraklStatus
 * @covers ::__construct
 */
class CheckMiraklStatusTest extends TestCase
{
    /**
     * @var CheckMiraklStatus
     */
    private $checkMiraklStatus;

    /**
     * @var EventManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->eventManagerMock = $this->createMock(EventManagerInterface::class);
        $this->checkMiraklStatus = $this->objectManager->create(CheckMiraklStatus::class, [
            'eventManager' => $this->eventManagerMock,
        ]);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithException()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['output'])
            ->addMethods(['getSynchroId', 'setMiraklStatus'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('getSynchroId')
            ->willThrowException(new \Exception('Test exception'));

        $processMock->expects($this->exactly(3))
            ->method('output')
            ->with(
                $this->logicalOr(
                    $this->equalTo('Checking Mirakl report status...'),
                    $this->equalTo('Check report in Mirakl failed: Test exception'),
                    $this->equalTo('Done!'),
                ),
            );

        $processMock->expects($this->once())
            ->method('setMiraklStatus')
            ->with(Process::STATUS_ERROR);

        $this->checkMiraklStatus->execute($processMock);
    }

    /**
     * @covers ::execute
     * @covers ::check
     */
    public function testExecuteWithEmptySynchroId()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['output'])
            ->addMethods(['getSynchroId'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('getSynchroId')
            ->willReturn(null);

        $processMock->expects($this->exactly(3))
            ->method('output')
            ->with(
                $this->logicalOr(
                    $this->equalTo('Checking Mirakl report status...'),
                    $this->equalTo('No synchro id found for current process'),
                    $this->equalTo('Done!'),
                ),
            );

        $this->checkMiraklStatus->execute($processMock);
    }

    /**
     * @covers ::execute
     * @covers ::check
     */
    public function testExecuteWithBadHelper()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['output', 'getHelperInstance'])
            ->addMethods(['getSynchroId', 'setMiraklStatus'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('getSynchroId')
            ->willReturn(123);

        $processMock->expects($this->once())
            ->method('setMiraklStatus')
            ->with(Process::STATUS_PROCESSING);

        $processMock->expects($this->once())
            ->method('getHelperInstance')
            ->willReturn(new \stdClass());

        $processMock->expects($this->exactly(4))
            ->method('output')
            ->with(
                $this->logicalOr(
                    $this->equalTo('Checking Mirakl report status...'),
                    $this->equalTo('API Synchro Id: #123'),
                    $this->equalTo('Helper does not implement SynchroResultInterface'),
                    $this->equalTo('Done!'),
                ),
            );

        $this->checkMiraklStatus->execute($processMock);
    }

    /**
     * @covers ::execute
     * @covers ::check
     */
    public function testExecuteWithSynchroResultPending()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['output', 'getHelperInstance'])
            ->addMethods(['getSynchroId', 'setMiraklStatus'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('getSynchroId')
            ->willReturn(123);

        $processMock->expects($this->exactly(2))
            ->method('setMiraklStatus')
            ->with(
                $this->logicalOr(
                    $this->equalTo(Process::STATUS_PROCESSING),
                    $this->equalTo(Process::STATUS_PENDING),
                )
            );

        $synchroResultMock = $this->getMockBuilder(AbstractSynchroResult::class)
            ->disableOriginalConstructor()
            ->addMethods(['getStatus'])
            ->getMock();

        $synchroResultMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(ProcessTrackingStatus::RUNNING);

        $helperMock = $this->createMock(SynchroResultInterface::class);
        $helperMock->expects($this->once())
            ->method('getSynchroResult')
            ->willReturn($synchroResultMock);

        $processMock->expects($this->once())
            ->method('getHelperInstance')
            ->willReturn($helperMock);

        $processMock->expects($this->exactly(4))
            ->method('output')
            ->with(
                $this->logicalOr(
                    $this->equalTo('Checking Mirakl report status...'),
                    $this->equalTo('API Synchro Id: #123'),
                    $this->equalTo('API call is not finished ... try again later'),
                    $this->equalTo('Done!'),
                ),
            );

        $this->checkMiraklStatus->execute($processMock);
    }

    /**
     * @covers ::execute
     * @covers ::check
     */
    public function testExecuteWithSynchroResultHasReport()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['output', 'getHelperInstance'])
            ->addMethods(['getSynchroId', 'setMiraklStatus'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('getSynchroId')
            ->willReturn(123);

        $processMock->expects($this->exactly(2))
            ->method('setMiraklStatus')
            ->with(
                $this->logicalOr(
                    $this->equalTo(Process::STATUS_PROCESSING),
                    $this->equalTo(Process::STATUS_COMPLETED),
                )
            );

        $synchroResultMock = $this->getMockBuilder(AbstractSynchroResult::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->addMethods(['getStatus'])
            ->getMock();

        $synchroResultMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(ProcessTrackingStatus::COMPLETE);

        $synchroResultMock->expects($this->once())
            ->method('getData')
            ->with('has_report')
            ->willReturn(true);

        $helperMock = $this->createMock(SynchroResultInterface::class);
        $helperMock->expects($this->once())
            ->method('getSynchroResult')
            ->willReturn($synchroResultMock);

        $helperMock->expects($this->once())
            ->method('getErrorReport')
            ->with(123)
            ->willReturn(new \SplTempFileObject());

        $processMock->expects($this->once())
            ->method('getHelperInstance')
            ->willReturn($helperMock);

        $processMock->expects($this->exactly(4))
            ->method('output')
            ->with(
                $this->logicalOr(
                    $this->equalTo('Checking Mirakl report status...'),
                    $this->equalTo('API Synchro Id: #123'),
                    $this->equalTo('Status COMPLETED'),
                    $this->equalTo('Done!'),
                ),
            );

        $this->checkMiraklStatus->execute($processMock);
    }

    /**
     * @covers ::execute
     * @covers ::check
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testExecuteWithSynchroResultHasReportWithError()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['output', 'getHelperInstance'])
            ->addMethods(['getSynchroId', 'setMiraklStatus'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('getSynchroId')
            ->willReturn(123);

        $processMock->expects($this->exactly(2))
            ->method('setMiraklStatus')
            ->with(
                $this->logicalOr(
                    $this->equalTo(Process::STATUS_PROCESSING),
                    $this->equalTo(Process::STATUS_ERROR),
                )
            );

        $synchroResultMock = $this->getMockBuilder(AbstractSynchroResult::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->addMethods(['getStatus'])
            ->getMock();

        $synchroResultMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(ProcessTrackingStatus::COMPLETE);

        $synchroResultMock->expects($this->once())
            ->method('getData')
            ->with('has_report')
            ->willReturn(true);

        $helperMock = $this->createMock(SynchroResultInterface::class);
        $helperMock->expects($this->once())
            ->method('getSynchroResult')
            ->willReturn($synchroResultMock);

        $helperMock->expects($this->once())
            ->method('getErrorReport')
            ->with(123)
            ->willReturn(new \SplTempFileObject());

        $processMock->expects($this->once())
            ->method('getHelperInstance')
            ->willReturn($helperMock);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('mirakl_api_get_synchronization_report')
            ->willReturnCallback(function (string $eventName, array $data = []) {
                /** @var DataObject $hasError */
                $hasError = $data['has_error'];
                $hasError->setData('error', true);
            });

        $processMock->expects($this->exactly(4))
            ->method('output')
            ->with(
                $this->logicalOr(
                    $this->equalTo('Checking Mirakl report status...'),
                    $this->equalTo('API Synchro Id: #123'),
                    $this->equalTo('Status ERROR'),
                    $this->equalTo('Done!'),
                ),
            );

        $this->checkMiraklStatus->execute($processMock);
    }

    /**
     * @covers ::execute
     * @covers ::check
     */
    public function testExecuteWithSynchroErrorReport()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['output', 'getHelperInstance'])
            ->addMethods(['getSynchroId', 'setMiraklStatus'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('getSynchroId')
            ->willReturn(123);

        $processMock->expects($this->exactly(2))
            ->method('setMiraklStatus')
            ->with(
                $this->logicalOr(
                    $this->equalTo(Process::STATUS_PROCESSING),
                    $this->equalTo(Process::STATUS_ERROR),
                )
            );

        $synchroResultMock = $this->getMockBuilder(AbstractSynchroResult::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->addMethods(['getStatus', 'getErrorReport'])
            ->getMock();

        $synchroResultMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(ProcessTrackingStatus::COMPLETE);

        $synchroResultMock->expects($this->once())
            ->method('getData')
            ->with('has_report')
            ->willReturn(false);

        $synchroResultMock->expects($this->once())
            ->method('getErrorReport')
            ->willReturn(true);

        $helperMock = $this->createMock(SynchroResultInterface::class);
        $helperMock->expects($this->once())
            ->method('getSynchroResult')
            ->willReturn($synchroResultMock);

        $helperMock->expects($this->once())
            ->method('getErrorReport')
            ->with(123)
            ->willReturn(new \SplTempFileObject());

        $processMock->expects($this->once())
            ->method('getHelperInstance')
            ->willReturn($helperMock);

        $processMock->expects($this->exactly(5))
            ->method('output')
            ->with(
                $this->logicalOr(
                    $this->equalTo('Checking Mirakl report status...'),
                    $this->equalTo('API Synchro Id: #123'),
                    $this->equalTo('Status ERROR'),
                    $this->equalTo('Done!'),
                    $this->callback(function ($arg) {
                        return $arg instanceof Phrase
                            && str_starts_with($arg->getText(), 'Error file has been saved as');
                    }),
                ),
            );

        $this->checkMiraklStatus->execute($processMock);
    }

    /**
     * @covers ::execute
     * @covers ::check
     */
    public function testExecuteWithSynchroResultSuccess()
    {
        $processMock = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['output', 'getHelperInstance'])
            ->addMethods(['getSynchroId', 'setMiraklStatus'])
            ->getMock();

        $processMock->expects($this->once())
            ->method('getSynchroId')
            ->willReturn(123);

        $processMock->expects($this->exactly(2))
            ->method('setMiraklStatus')
            ->with(
                $this->logicalOr(
                    $this->equalTo(Process::STATUS_PROCESSING),
                    $this->equalTo(Process::STATUS_COMPLETED),
                )
            );

        $synchroResultMock = $this->getMockBuilder(AbstractSynchroResult::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->addMethods(['getStatus', 'getErrorReport'])
            ->getMock();

        $synchroResultMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(ProcessTrackingStatus::COMPLETE);

        $synchroResultMock->expects($this->once())
            ->method('getData')
            ->with('has_report')
            ->willReturn(false);

        $synchroResultMock->expects($this->once())
            ->method('getErrorReport')
            ->willReturn(false);

        $helperMock = $this->createMock(SynchroResultInterface::class);
        $helperMock->expects($this->once())
            ->method('getSynchroResult')
            ->willReturn($synchroResultMock);

        $processMock->expects($this->once())
            ->method('getHelperInstance')
            ->willReturn($helperMock);

        $processMock->expects($this->exactly(4))
            ->method('output')
            ->with(
                $this->logicalOr(
                    $this->equalTo('Checking Mirakl report status...'),
                    $this->equalTo('API Synchro Id: #123'),
                    $this->equalTo('Status SUCCESS'),
                    $this->equalTo('Done!'),
                ),
            );

        $this->checkMiraklStatus->execute($processMock);
    }
}
