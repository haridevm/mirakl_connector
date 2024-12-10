<?php
declare(strict_types=1);

namespace Mirakl\Process\Test\Integration;

use Magento\Framework\ObjectManager\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;
use Mirakl\Process\Model\Output\Factory as OutputFactory;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ProcessFactory as ProcessModelFactory;
use Mirakl\Process\Model\ResourceModel\Process\Collection as ProcessCollection;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;
use Mirakl\Process\Model\ResourceModel\Process\CollectionFactory as ProcessCollectionFactory;

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
     * @param   int|null    $parentId
     * @param   string|null $code
     * @return  Process
     */
    protected function createSampleProcess($parentId = null, $code = null): Process
    {
        $process = $this->processModelFactory->create([
            'objectManager' => $this->objectManagerMock,
            'outputFactory' => $this->outputFactoryMock,
        ]);
        $process->setType('TESTS')
            ->setCode($code)
            ->setStatus(Process::STATUS_PENDING)
            ->setName('Sample process for integration tests')
            ->setHelper('Mirakl\Process\Helper\Data')
            ->setMethod('run')
            ->setParams(['foo', ['bar']])
            ->setParentId($parentId);

        $this->processResourceFactory->create()->save($process);

        return $process;
    }

    /**
     * @param   int $processId
     * @return  Process
     */
    protected function getProcessById($processId): Process
    {
        $process = $this->processModelFactory->create();
        $this->processResourceFactory->create()->load($process, $processId);

        return $process;
    }

    /**
     * @param   string  $field
     * @param   mixed   $value
     * @return  ProcessCollection
     */
    protected function findProcess($field, $value): ProcessCollection
    {
        $collection = $this->processCollectionFactory->create();
        $collection->addFieldToFilter($field, $value);

        return $collection;
    }

    /**
     * @return  void
     */
    protected function mockCliOutput(): void
    {
        $outputFactory = $this->objectManager->create(OutputFactory::class);

        $callback = function ($type, $process) use ($outputFactory) {
            if ($type == 'cli') {
                $type = 'nullOutput';
            }

            return $outputFactory->create($type, $process);
        };

        $this->outputFactoryMock
            ->method('create')
            ->willReturnCallback($callback);
    }
}
