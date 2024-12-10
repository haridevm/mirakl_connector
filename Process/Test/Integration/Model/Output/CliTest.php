<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Output;

use Mirakl\Process\Model\Output\Cli;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Test\Integration\Model\Action\ActionStub;
use Mirakl\Process\Test\Integration\TestCase;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Output\Cli
 */
class CliTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function mockCliOutput(): void
    {
        $cliOutputMock = $this->getMockBuilder(Cli::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['display'])
            ->getMock();

        $this->objectManager->configure([
            'preferences' => [Cli::class => get_class($cliOutputMock)],
        ]);
    }

    /**
     * @covers ::display
     */
    public function testDisplay()
    {
        $this->expectOutputString('    This is a test' . PHP_EOL);

        $parent = $this->processModelFactory->create();
        $parent->setType('TESTS')
            ->setCode('TESTS');
        $this->processResourceFactory->create()->save($parent);

        $process = $this->processModelFactory->create();
        $process->setParentId($parent->getId())
            ->setStatus(Process::STATUS_PENDING)
            ->setHelper(ActionStub::class)
            ->setMethod('execute');
        $this->processResourceFactory->create()->save($process);

        $process->addOutput('cli');
        $process->output('This is a test');
    }
}
