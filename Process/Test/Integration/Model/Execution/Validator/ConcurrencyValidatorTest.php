<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Execution\Validator;

use Mirakl\Process\Model\Exception\AlreadyRunningException;
use Mirakl\Process\Model\Execution\Validator\ConcurrencyValidator;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ResourceModel\Process\CollectionFactory;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Execution\Validator\ConcurrencyValidator
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ConcurrencyValidatorTest extends TestCase
{
    /**
     * @var ConcurrencyValidator
     */
    private $validator;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->validator = new ConcurrencyValidator(
            $this->objectManager->create(CollectionFactory::class),
            ['foo']
        );
    }

    /**
     * @covers ::validate
     */
    public function testValidateWithIgnoredCode()
    {
        $process = $this->createSampleProcess();
        $process->setCode('foo');

        $this->validator->validate($process);

        $this->assertTrue(true); // if no exception is thrown, test is ok
    }

    /**
     * @covers ::validate
     * @covers ::getRunningProcesses
     */
    public function testValidateWithException()
    {
        $this->expectException(AlreadyRunningException::class);

        $process1 = $this->createSampleProcess();
        $process1->setCode('bar');
        $process1->setStatus(Process::STATUS_PROCESSING);
        $process1->output('Process is running', true);

        $process2 = $this->createSampleProcess();
        $process2->setCode('bar');
        $process2->output('Process is pending', true);

        $this->validator->validate($process2);
    }
}
