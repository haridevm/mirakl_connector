<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration;

use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;
use Mirakl\Process\Model\Action\AbstractParentAction;
use Mirakl\Process\Model\Action\ActionListInterface;
use Mirakl\Process\Model\Action\Execution\ChildProviderInterface;
use Mirakl\Process\Model\Exception\RetryLaterHandlerInterface;
use Mirakl\Process\Model\Execution\Executor;
use Mirakl\Process\Model\Output\Cli;
use Mirakl\Process\Model\Output\Factory as OutputFactory;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ProcessFactory as ProcessModelFactory;
use Mirakl\Process\Model\ResourceModel\Process\Collection as ProcessCollection;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;
use Mirakl\Process\Model\ResourceModel\Process\CollectionFactory as ProcessCollectionFactory;
use Mirakl\Process\Test\Integration\Model\Action\ActionStub;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class TestCase extends \Mirakl\Core\Test\TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ProcessModelFactory
     */
    protected $processModelFactory;

    /**
     * @var ProcessResourceFactory
     */
    protected $processResourceFactory;

    /**
     * @var ProcessCollectionFactory
     */
    protected $processCollectionFactory;

    /**
     * @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var OutputFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $outputFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->processModelFactory = $this->objectManager->create(ProcessModelFactory::class);
        $this->processResourceFactory = $this->objectManager->create(ProcessResourceFactory::class);
        $this->processCollectionFactory = $this->objectManager->create(ProcessCollectionFactory::class);
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->outputFactoryMock = $this->createMock(OutputFactory::class);

        $this->mockCliOutput();
    }

    /**
     * @param int|null $parentId
     * @param string   $status
     * @param mixed    $actionStub
     * @return Process
     */
    protected function createSampleProcess(
        $parentId = null,
        $status = Process::STATUS_PENDING,
        $actionStub = null
    ): Process {
        $objectManagerMock = $this->createMock(ObjectManager::class);

        $process = $this->processModelFactory->create([
            'objectManager' => $objectManagerMock,
            'outputFactory' => $this->outputFactoryMock,
        ]);

        if (null === $actionStub) {
            $actionStub = new ActionStub();
        }

        $objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturn($actionStub);

        $process->setType('TESTS')
            ->setCode('TESTS')
            ->setName($actionStub->getName())
            ->setStatus($status)
            ->setHelper(get_class($actionStub))
            ->setMethod('execute')
            ->setParams(['foo', ['bar']])
            ->setParentId($parentId);

        $this->processResourceFactory->create()->save($process);

        return $process;
    }

    /**
     * @param int $processId
     * @return Process
     */
    protected function getProcessById($processId): Process
    {
        $process = $this->processModelFactory->create();
        $this->processResourceFactory->create()->load($process, $processId);

        return $process;
    }

    /**
     * @param string $field
     * @param mixed  $value
     * @return ProcessCollection
     */
    protected function findProcess($field, $value): ProcessCollection
    {
        $collection = $this->processCollectionFactory->create();
        $collection->addFieldToFilter($field, $value);

        return $collection;
    }

    /**
     * @return void
     */
    protected function mockCliOutput(): void
    {
        $cliOutputMock = $this->createMock(Cli::class);

        $this->objectManager->configure([
            'preferences' => [Cli::class => get_class($cliOutputMock)],
        ]);
    }

    /**
     * @param array $actionList
     * @return AbstractParentAction
     */
    protected function createParentActionMock(array $actionList = []): AbstractParentAction
    {
        return $this->getMockBuilder(AbstractParentAction::class)
            ->setConstructorArgs([
                'childProvider'     => $this->objectManager->create(ChildProviderInterface::class),
                'retryLaterHandler' => $this->objectManager->create(RetryLaterHandlerInterface::class),
                'executor'          => $this->objectManager->create(Executor::class),
                'actionList'        => $this->objectManager->create(ActionListInterface::class, [
                    'actions' => $actionList,
                ]),
            ])
            ->getMockForAbstractClass();
    }
}
