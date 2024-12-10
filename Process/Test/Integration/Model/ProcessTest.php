<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model;

use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Action\ActionInterface;
use Mirakl\Process\Model\CheckMiraklStatus;
use Mirakl\Process\Model\Exception\AlreadyRunningException;
use Mirakl\Process\Model\Execution;
use Mirakl\Process\Model\Execution\Validator\ConcurrencyValidator;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ResourceModel\Process\Collection as ProcessCollection;
use Mirakl\Process\Model\ResourceModel\Process\CollectionFactory;
use Mirakl\Process\Test\Integration\Model\Action\ActionStub;
use Mirakl\Process\Test\Integration\Model\Action\ClosureActionStub;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Process
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessTest extends TestCase
{
    /**
     * @covers ::__destruct
     */
    public function testDestruct()
    {
        $process = $this->createSampleProcess();
        $process->setStartedAt(microtime(true));

        $this->setOutputCallback(function ($output) use ($process) {
            ob_start();
            echo $output; // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput
            $process->__destruct();
            $this->assertTrue($process->isCompleted());
            $this->assertStringContainsString($output, $process->getOutput());
        });

        echo 'Hidden output'; // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput

        $this->assertTrue($process->isStarted());
        $this->assertFalse($process->isEnded());
    }

    /**
     * @covers ::run
     * @covers ::isProcessing
     * @covers ::isCompleted
     * @covers ::isPending
     * @covers ::output
     */
    public function testRunProcessWithParams()
    {
        $actionStub = new ClosureActionStub(function (Process $process, ...$params) {
            $process->output('This is a test');
            $this->assertTrue($process->isProcessing());
            $this->assertSame('foo', $params[0]);
            $this->assertSame(['bar'], $params[1]);

            return [];
        });

        // Create a sample process for test
        $process = $this->createSampleProcess(null, Process::STATUS_PENDING, $actionStub);

        // Ensure that process has been saved correctly in pending status and with params
        $this->processResourceFactory->create()->load($process, $process->getId());

        $this->assertNotEmpty($process->getId());
        $this->assertTrue($process->isPending());
        $this->assertNull($process->getParentId());
        $this->assertNotEmpty($process->getParams());

        // Run the process
        $process->run();

        // Process should be completed without any error
        $this->assertTrue($process->isCompleted());

        $this->assertGreaterThan(0, $process->getDuration());
        $this->assertNotEmpty($process->getOutput());
    }

    /**
     * @covers \Mirakl\Process\Model\ResourceModel\Process::_beforeSave
     * @covers \Mirakl\Process\Model\ResourceModel\Process::_beforeDelete
     */
    public function testDeleteParentProcessMustDeleteChildrenInCascade()
    {
        /**
         * Create sample processes with parent/child dependency for test:
         *
         * process #1
         *  |_ process #2
         *  |_ process #3
         *      |_ process #4
         *  |_ process #5
         */
        $process1 = $this->createSampleProcess();
        $process2 = $this->createSampleProcess($process1->getId());
        $process3 = $this->createSampleProcess($process1->getId());
        $process4 = $this->createSampleProcess($process3->getId());
        $process5 = $this->createSampleProcess($process1->getId());

        // Delete the main process should delete all children in cascade
        $this->processResourceFactory->create()->delete($process1);

        $this->assertNull($this->getProcessById($process1->getId())->getId());
        $this->assertNull($this->getProcessById($process2->getId())->getId());
        $this->assertNull($this->getProcessById($process3->getId())->getId());
        $this->assertNull($this->getProcessById($process4->getId())->getId());
        $this->assertNull($this->getProcessById($process5->getId())->getId());
    }

    /**
     * @covers ::start
     * @covers ::stop
     * @covers ::outputMemoryUsage
     */
    public function testChildrenIdAutomaticallyCreatedUnderCurrentProcess()
    {
        /**
         * Create sample processes created when parent process is running:
         *
         * process #1
         *  |_ process #2
         *    |_ process #3
         */
        $process1 = $this->createSampleProcess();
        $process1->start();
        $process2 = $this->createSampleProcess();
        $process2->start();
        $process3 = $this->createSampleProcess();
        $process3->start();
        $process3->stop();
        $process2->stop();
        $process1->stop();

        // Ensure that the three processes have been executed successfully
        $this->assertTrue($process1->isCompleted());
        $this->assertTrue($process2->isCompleted());
        $this->assertTrue($process3->isCompleted());

        // Ensure that process #2 and #3 are children of #1 and #2 respectively
        $this->assertEquals($process1->getId(), $process2->getParentId());
        $this->assertEquals($process2->getId(), $process3->getParentId());
    }

    /**
     * @coversNothing
     */
    public function testChildrenIdAutomaticallySetAndAssignedParentKept()
    {
        /**
         * Create sample parent/child processes created when parent process is running:
         *
         * process #1
         *  |_ process #2
         *  |_ process #3
         */
        $process1 = $this->createSampleProcess();
        $process1->start();
        $process2 = $this->createSampleProcess();
        $process2->start();
        $process3 = $this->createSampleProcess($process1->getId());
        $process3->stop();
        $process2->stop();
        $process1->stop();

        // Ensure that the three processes have been executed successfully
        $this->assertTrue($process1->isCompleted());
        $this->assertTrue($process2->isCompleted());
        $this->assertTrue($process3->isCompleted());

        // Ensure that processes #2 and #3 are children of #1
        $this->assertEquals($process1->getId(), $process2->getParentId());
        $this->assertEquals($process1->getId(), $process3->getParentId());
    }

    /**
     * @covers ::start
     */
    public function testProcessConcurrentExecutionBlocked()
    {
        // Create 3 process with the same name and different statuses to test concurrent execution
        // Process #1: STOPPED
        // Process #2: PROCESSING
        // Process #3: PENDING

        $process1 = $this->createSampleProcess();
        $process1->stop();

        $process2 = $this->createSampleProcess();
        $process2->setStatus(Process::STATUS_PROCESSING);
        $process2->output('Updated process status to processing', true);

        $this->expectException(AlreadyRunningException::class);
        $this->expectExceptionMessage('An identical process is already running.');

        $process3 = $this->createSampleProcess();
        $process3->start();
    }

    /**
     * @covers ::start
     */
    public function testProcessConcurrentExecutionAllowed()
    {
        $concurrencyValidatorMock = $this->getMockBuilder(ConcurrencyValidator::class)
            ->setConstructorArgs([
                'collectionFactory'  => $this->objectManager->create(CollectionFactory::class),
                'ignoreProcessCodes' => ['TESTS'],
            ])
            ->onlyMethods([])
            ->getMock();

        $this->objectManager->addSharedInstance($concurrencyValidatorMock, ConcurrencyValidator::class);

        $process1 = $this->createSampleProcess();
        $process1->setStatus(Process::STATUS_PROCESSING);
        $process1->output('Updated process status to processing', true);

        $process2 = $this->createSampleProcess();
        $process2->start();

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::addOutput
     * @covers ::getOutputs
     */
    public function testAddOutput()
    {
        $process = $this->processModelFactory->create();

        $process->addOutput('log');

        $this->assertArrayHasKey('log', $process->getOutputs());
    }

    /**
     * @covers ::addOutput
     */
    public function testAddOutputWithException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid output specified.');

        $process = $this->processModelFactory->create();

        $process->addOutput(new \stdClass());
    }

    /**
     * @covers ::cancel
     */
    public function testCancel()
    {
        $process = $this->createSampleProcess();
        $process->cancel();

        $this->assertTrue($process->isCancelled());
    }

    /**
     * @covers ::canCheckMiraklStatus
     */
    public function testCanCheckMiraklStatus()
    {
        $process = $this->processModelFactory->create();
        $process->setStatus(Process::STATUS_PENDING);
        $process->setMiraklStatus(Process::STATUS_PENDING);

        $this->assertTrue($process->canCheckMiraklStatus());

        $process->setMiraklStatus(Process::STATUS_COMPLETED);

        $this->assertFalse($process->canCheckMiraklStatus());
    }

    /**
     * @covers ::canRun
     */
    public function testCanRun()
    {
        $process = $this->processModelFactory->create();
        $process->setStatus(Process::STATUS_PENDING);
        $process->setHelper('foo');
        $process->setMethod('bar');

        $this->assertTrue($process->canRun());
    }

    /**
     * @covers ::canShowFile
     * @covers ::getFileSize
     * @covers ::getDownloadFileUrl
     */
    public function testCanShowFile()
    {
        $process = $this->processModelFactory->create();

        $this->assertFalse($process->getDownloadFileUrl());

        $process->setFile(__FILE__);

        $this->assertTrue($process->canShowFile());
        $this->assertStringContainsString('mirakl/process/downloadFile', $process->getDownloadFileUrl());
    }

    /**
     * @covers ::canStop
     */
    public function testCanStop()
    {
        $process = $this->processModelFactory->create();
        $process->setStatus(Process::STATUS_PROCESSING);

        $this->assertTrue($process->canStop());

        $process->setStatus(Process::STATUS_COMPLETED);

        $this->assertFalse($process->canStop());
    }

    /**
     * @covers ::checkMiraklStatus
     */
    public function testCheckMiraklStatus()
    {
        $checkMiraklStatusMock = $this->createMock(CheckMiraklStatus::class);
        $checkMiraklStatusMock->expects($this->once())
            ->method('execute')
            ->willReturnSelf();

        $process = $this->objectManager->create(Process::class, [
            'checkMiraklStatus' => $checkMiraklStatusMock,
        ]);

        $process->checkMiraklStatus();
    }

    /**
     * @covers ::getAction
     * @covers ::setAction
     */
    public function testGetAction()
    {
        $process = $this->processModelFactory->create();
        $process->setAction(new ActionStub());

        $this->assertInstanceOf(ActionInterface::class, $process->getAction());
    }

    /**
     * @covers ::getHelper
     */
    public function testGetHelper()
    {
        $process = $this->processModelFactory->create();
        $process->setHelper('foo');

        $this->assertSame('foo', $process->getHelper());
    }

    /**
     * @covers ::getHelperInstance
     */
    public function testGetHelperInstance()
    {
        $process = $this->processModelFactory->create();
        $process->setHelper(ProcessHelper::class);

        $this->assertInstanceOf(ProcessHelper::class, $process->getHelperInstance());
    }

    /**
     * @covers ::getMethod
     */
    public function testGetMethod()
    {
        $process = $this->processModelFactory->create();
        $process->setMethod('foo');

        $this->assertSame('foo', $process->getMethod());
    }

    /**
     * @covers ::getStartedAt
     * @covers ::setStartedAt
     * @covers ::isStarted
     */
    public function testGetStartedAt()
    {
        $now = microtime(true);

        $process = $this->createSampleProcess();

        $this->assertSame($process, $process->setStartedAt($now));
        $this->assertSame($now, $process->getStartedAt());
        $this->assertTrue($process->isStarted());
    }

    /**
     * @covers ::getStopped
     * @covers ::setStopped
     */
    public function testGetStoppedFlag()
    {
        $process = $this->createSampleProcess();

        $this->assertSame($process, $process->setStopped(false));
        $this->assertFalse($process->getStopped());
    }

    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $executionMock = $this->createMock(Execution::class);
        $executionMock->expects($this->once())
            ->method('execute')
            ->willReturn([]);

        /** @var Process $process */
        $process = $this->objectManager->create(Process::class, [
            'execution' => $executionMock,
        ]);

        $process->execute();
    }

    /**
     * @covers ::getChildrenCollection
     */
    public function testGetChildrenCollection()
    {
        $parent = $this->createSampleProcess();
        $child = $this->createSampleProcess($parent->getId());

        $children = $parent->getChildrenCollection();

        $this->assertInstanceOf(ProcessCollection::class, $children);
        $this->assertCount(1, $children);
        $this->assertCount(0, $child->getChildrenCollection());
    }

    /**
     * @covers ::generateHash
     */
    public function testGenerateHash()
    {
        $process = $this->createSampleProcess();

        $this->assertNotEmpty($process->getHash());
    }

    /**
     * @covers ::getDuration
     */
    public function testGetDuration()
    {
        $process1 = $this->createSampleProcess(null, Process::STATUS_IDLE);
        $process1->start();

        $this->assertGreaterThan(0, $process1->getDuration()->format('f'));

        $process2 = $this->createSampleProcess(null, Process::STATUS_COMPLETED);

        $createdAt = (new \DateTime())->format('Y-m-d H:i:s');
        $updatedAt = (new \DateTime())->add(new \DateInterval('PT1M'))->format('Y-m-d H:i:s');

        $process2->setCreatedAt($createdAt);
        $process2->setUpdatedAt($updatedAt);

        $this->assertGreaterThan(0, $process2->getDuration()->format('f'));
    }

    /**
     * @covers ::updateDuration
     */
    public function testUpdateDuration()
    {
        $process = $this->createSampleProcess();

        $process->updateDuration();
        $this->assertEmpty($process->getData('duration'));

        $process->setStatus(Process::STATUS_PROCESSING);
        $process->updateDuration();
        $this->assertGreaterThan(0, $process->getData('duration'));

        $process->unsDuration();
        $this->assertEmpty($process->getData('duration'));

        $process->start();
        $process->updateDuration();
        $this->assertGreaterThan(0, $process->getData('duration'));
    }

    /**
     * @covers ::error
     */
    public function testError()
    {
        $process = $this->createSampleProcess();
        $process->error(__('An error occurred'));

        $this->assertStringContainsString('<error>An error occurred</error>', $process->getOutput());
    }

    /**
     * @covers ::appendOutput
     */
    public function testAppendOutput()
    {
        $parent = $this->createSampleProcess();
        $child = $this->createSampleProcess($parent->getId());

        $child->appendOutput('Child output', true);

        // Reload parent and child processes
        $parent = $this->getProcessById($parent->getId());
        $child = $this->getProcessById($child->getId());

        $this->assertStringContainsString('Child output', $parent->getOutput());
        $this->assertStringContainsString('Child output', $child->getOutput());
    }

    /**
     * @covers ::appendOutput
     */
    public function testAppendOutputWithoutSave()
    {
        $parent = $this->createSampleProcess();
        $child = $this->createSampleProcess($parent->getId());

        $child->appendOutput('Child output');

        // Reload parent and child processes
        $parent = $this->getProcessById($parent->getId());
        $child = $this->getProcessById($child->getId());

        $this->assertNull($parent->getOutput());
        $this->assertNull($child->getOutput());
    }

    /**
     * @covers ::isParent
     * @covers ::isChild
     * @covers ::deleteChildren
     */
    public function testDeleteChildren()
    {
        $parent = $this->createSampleProcess();
        $child = $this->createSampleProcess($parent->getId());

        $this->assertTrue($parent->isParent());
        $this->assertFalse($child->isParent());

        $this->assertFalse($parent->isChild());
        $this->assertTrue($child->isChild());

        $parent->deleteChildren();

        // Reload parent and child processes
        $parent = $this->getProcessById($parent->getId());
        $child = $this->getProcessById($child->getId());

        $this->assertSame($parent->getId(), $parent->getId());
        $this->assertNull($child->getId());
    }

    /**
     * @covers ::isStatusPendingRetry
     * @covers ::isEnded
     */
    public function testIsStatusPendingRetry()
    {
        $process = $this->createSampleProcess(null, Process::STATUS_PENDING_RETRY);

        $this->assertTrue($process->isStatusPendingRetry());
        $this->assertFalse($process->isEnded());
    }

    /**
     * @covers ::idle
     * @covers ::isStatusIdle
     * @covers ::isEnded
     */
    public function testIsStatusIdle()
    {
        $process = $this->createSampleProcess();
        $process->idle();

        $this->assertTrue($process->isStatusIdle());
        $this->assertFalse($process->isEnded());
    }

    /**
     * @covers ::isStopped
     * @covers ::isEnded
     */
    public function testIsStatusStopped()
    {
        $process = $this->createSampleProcess();

        $this->assertFalse($process->isStopped());

        $process->stop(Process::STATUS_STOPPED);

        $this->assertTrue($process->isStopped());
        $this->assertTrue($process->isEnded());
    }

    /**
     * @covers ::hasProcessingChild
     */
    public function testHasProcessingChild()
    {
        // Test hasProcessingChild() returns false
        $parent = $this->createSampleProcess();
        $this->createSampleProcess($parent->getId());

        $this->assertTrue($parent->isParent());
        $this->assertFalse($parent->hasProcessingChild());

        // Test hasProcessingChild() returns true
        $parent = $this->createSampleProcess();
        $this->createSampleProcess($parent->getId(), Process::STATUS_PROCESSING);

        $this->assertTrue($parent->isParent());
        $this->assertTrue($parent->hasProcessingChild());
    }

    /**
     * @covers ::getParent
     */
    public function testGetParent()
    {
        $parent = $this->createSampleProcess();
        $child = $this->createSampleProcess();
        $child->setParentId($parent->getId());

        $this->assertSame($parent->getId(), $child->getParent()->getId());
    }

    /**
     * @covers ::setParent
     */
    public function testSetParent()
    {
        $parent = $this->createSampleProcess();
        $child = $this->createSampleProcess();
        $child->setParent($parent);

        $this->assertSame($parent->getId(), $child->getParentId());
        $this->assertSame($parent->getId(), $child->getParent()->getId());
    }

    /**
     * @covers ::getFileSizeFormatted
     * @covers ::getFileUrl
     * @covers \Mirakl\Process\Model\ResourceModel\Process::_beforeSave
     * @covers \Mirakl\Process\Model\ResourceModel\Process::_beforeDelete
     * @covers \Mirakl\Process\Model\ResourceModel\Process::prepareDataForUpdate
     */
    public function testGetFileSizeFormatted()
    {
        /** @var ProcessHelper $processHelper */
        $processHelper = $this->objectManager->create(ProcessHelper::class);
        $filePath = $processHelper->saveFile(__FILE__);

        $process = $this->createSampleProcess();

        $this->assertFalse($process->getFileSizeFormatted());
        $this->assertFalse($process->getFileUrl());

        $process->setFile($filePath);
        $process->setMiraklFile($filePath);

        $process->output('File saved', true);

        $this->assertNotFalse($process->getFileSizeFormatted());
        $this->assertNotFalse($process->getFileUrl());

        $this->assertNotFalse($process->getFileSizeFormatted(' ', true));
        $this->assertNotFalse($process->getFileUrl(true));

        $this->processResourceFactory->create()->delete($process);
        $this->assertFileDoesNotExist($filePath);
    }

    /**
     * @covers ::getOutputSize
     * @covers ::getOutputSizeFormatted
     */
    public function testGetOutputSize()
    {
        $process = $this->createSampleProcess();

        $this->assertFalse($process->getOutputSize());
        $this->assertFalse($process->getOutputSizeFormatted());

        $process->output('This is a test', true);

        $this->assertGreaterThan(0, $process->getOutputSize());
        $this->assertNotFalse($process->getOutputSizeFormatted());
    }

    /**
     * @covers ::getUrl
     */
    public function testGetUrl()
    {
        $process = $this->createSampleProcess();

        $this->assertStringContainsString('mirakl/process/view', $process->getUrl());
    }

    /**
     * @covers ::getStatusClass
     * @covers ::getClassForStatus
     */
    public function testGetStatusClass()
    {
        $process = $this->createSampleProcess();
        $this->assertSame('grid-severity-minor', $process->getStatusClass());

        $process->setStatus(Process::STATUS_PROCESSING);
        $this->assertSame('grid-severity-major', $process->getStatusClass());

        $process->setStatus(Process::STATUS_COMPLETED);
        $this->assertSame('grid-severity-notice', $process->getStatusClass());

        $process->setStatus(Process::STATUS_STOPPED);
        $this->assertSame('grid-severity-critical', $process->getStatusClass());
    }

    /**
     * @covers ::hr
     */
    public function testHr()
    {
        $process = $this->createSampleProcess();
        $process->hr();

        $this->assertStringContainsString('--------------------------------', $process->getOutput());
    }

    /**
     * @covers ::getDownloadOutputUrl
     */
    public function testGetDownloadOutputUrl()
    {
        $process = $this->createSampleProcess();

        $this->assertFalse($process->getDownloadOutputUrl());

        $process->output('This is a test', true);

        $this->assertStringContainsString('mirakl/process/downloadOutput', $process->getDownloadOutputUrl());
    }

    /**
     * @covers ::getParams
     */
    public function testGetParams()
    {
        $process = $this->createSampleProcess();

        $params = serialize(['foo' => 'bar']); // phpcs:ignore
        $process->setParams($params);

        $this->assertSame(['foo' => 'bar'], $process->getParams());
    }
}
