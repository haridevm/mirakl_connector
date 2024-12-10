<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Exception;

use Mirakl\Process\Model\Action\RetryableInterface;
use Mirakl\Process\Model\ChildProcessFactoryInterface;
use Mirakl\Process\Model\Process;

class RetryLaterHandler implements RetryLaterHandlerInterface
{
    /**
     * @var ChildProcessFactoryInterface
     */
    private $childProcessFactory;

    /**
     * @param ChildProcessFactoryInterface $childProcessFactory
     */
    public function __construct(ChildProcessFactoryInterface $childProcessFactory)
    {
        $this->childProcessFactory = $childProcessFactory;
    }

    /**
     * @inheritdoc
     */
    public function handle(ProcessException $e): void
    {
        // Stop execution of current child process
        $process = $e->getProcess();

        $process->output(__('<error>ERROR: %1</error>', $e->getMessage()));
        $process->stop(Process::STATUS_STOPPED);

        $childAction = $process->getAction();

        if (!$childAction instanceof RetryableInterface) {
            return;
        }

        if ($childAction->getRetryCount() >= $childAction->getMaxRetry()) {
            throw new ChildMaxRetryException($process, __(
                'Child process #%1 has reached the max allowed retry count of %2',
                $process->getId(),
                $childAction->getMaxRetry()
            ));
        }

        if ($parent = $process->getParent()) {
            // Create a new child process to be executed later
            $childAction->setRetryCount($childAction->getRetryCount() + 1);
            $this->childProcessFactory->create($parent, $childAction);

            // Stop parent execution too but set status to 'pending retry' so it can be retried later
            $parent->output(__('<error>ERROR: %1</error>', $e->getMessage()));

            while ($parent) {
                // Set all parent processes to 'pending retry'
                $parent->stop(Process::STATUS_PENDING_RETRY);
                $parent = $parent->getParent();
            }
        }
    }
}
