<?php

declare(strict_types=1);

namespace Mirakl\Process\Test\Integration\Model\Action;

use Mirakl\Process\Model\Action\AbstractAction;
use Mirakl\Process\Model\Action\RetryableInterface;
use Mirakl\Process\Model\Action\RetryableTrait;
use Mirakl\Process\Model\Exception\RetryLaterException;
use Mirakl\Process\Model\Process;

class RetryLaterActionStub extends AbstractAction implements RetryableInterface
{
    use RetryableTrait;

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Retry later action stub';
    }

    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        throw new RetryLaterException($process, __('Retry later please.'));
    }
}
