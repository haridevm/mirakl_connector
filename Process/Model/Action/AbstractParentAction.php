<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Action;

use Mirakl\Process\Model\Exception\ChildMaxRetryException;
use Mirakl\Process\Model\Exception\ChildProcessException;
use Mirakl\Process\Model\Exception\RetryLaterException;
use Mirakl\Process\Model\Exception\RetryLaterHandlerInterface;
use Mirakl\Process\Model\Action\Execution\ChildProviderInterface;
use Mirakl\Process\Model\Exception\StopExecutionException;
use Mirakl\Process\Model\Execution\Executor;
use Mirakl\Process\Model\Process;

abstract class AbstractParentAction extends AbstractAction
{
    /**
     * @var ChildProviderInterface
     */
    protected $childProvider;

    /**
     * @var RetryLaterHandlerInterface
     */
    protected $retryLaterHandler;

    /**
     * @var ActionListInterface
     */
    protected $actionList;

    /**
     * @var Executor
     */
    protected $executor;

    /**
     * @param ChildProviderInterface     $childProvider
     * @param RetryLaterHandlerInterface $retryLaterHandler
     * @param ActionListInterface        $actionList
     * @param Executor                   $executor
     * @param array                      $data
     */
    public function __construct(
        ChildProviderInterface $childProvider,
        RetryLaterHandlerInterface $retryLaterHandler,
        ActionListInterface $actionList,
        Executor $executor,
        array $data = []
    ) {
        parent::__construct($data);
        $this->childProvider = $childProvider;
        $this->retryLaterHandler = $retryLaterHandler;
        $this->actionList = $actionList;
        $this->executor = $executor;
    }

    /**
     * @inheritdoc
     */
    public function execute(Process $process, ...$params): array
    {
        $result = [];

        foreach ($this->actionList->get($params) as $action) {
            /** @var \Mirakl\Process\Model\Action\ActionInterface $action */
            if ($this->actionList->areParamsChainable()) {
                // Add previous child result to current child params
                $action->addParams($result);
            }

            if (!$childProcess = $this->childProvider->get($process, $action)) {
                continue; // continue to next child
            }

            $process->output(__('<info>Executing child process #%1 "%2" ...</info>', $childProcess->getId(), $action->getName()));

            try {
                $childProcess->start();
                $result = $this->executor->execute($childProcess);
                $childProcess->stop();
            } catch (RetryLaterException $e) {
                $this->handleRetryLater($e);
                break; // Stop children execution
            } catch (StopExecutionException $e) {
                $childProcess->output($e->getMessage());
                $childProcess->stop($e->getStatus());
                break; // Stop children execution
            } catch (ChildProcessException|\Exception $e) {
                $childProcess->fail($e->getMessage());
                break; // Stop children execution
            }
        }

        return $result;
    }

    /**
     * @param RetryLaterException $e
     * @return void
     */
    private function handleRetryLater(RetryLaterException $e): void
    {
        try {
            $this->retryLaterHandler->handle($e);
        } catch (ChildMaxRetryException $e) {
            $e->getProcess()->cancel($e->getMessage());
        }
    }
}