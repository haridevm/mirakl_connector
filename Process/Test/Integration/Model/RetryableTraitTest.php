<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model;

use Mirakl\Core\Test\Integration\TestCase;
use Mirakl\Process\Test\Integration\Model\Action\RetryableActionStub;

/**
 * @group process
 * @group model
 * @coversDefaultClass \Mirakl\Process\Model\Action\RetryableTrait
 */
class RetryableTraitTest extends TestCase
{
    /**
     * @var RetryableActionStub
     */
    private $retryable;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $data = [
            'retry_count' => 2,
            'max_retry' => 5,
        ];

        $this->retryable = new RetryableActionStub($data);
    }

    /**
     * @covers ::getRetryCount
     */
    public function testGetRetryCount()
    {
        $this->assertSame(2, $this->retryable->getRetryCount());
    }

    /**
     * @covers ::setRetryCount
     */
    public function testSetRetryCount()
    {
        $this->retryable->setRetryCount(3);
        $this->assertSame(3, $this->retryable->getRetryCount());
    }

    /**
     * @covers ::getMaxRetry
     */
    public function testGetMaxRetry()
    {
        $this->assertSame(5, $this->retryable->getMaxRetry());
    }

    /**
     * @covers ::setMaxRetry
     */
    public function testSetMaxRetry()
    {
        $this->retryable->setMaxRetry(10);
        $this->assertSame(10, $this->retryable->getMaxRetry());
    }
}
