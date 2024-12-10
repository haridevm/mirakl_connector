<?php
declare(strict_types=1);

namespace Mirakl\Process\Model;

use Mirakl\Process\Model\Exception\AlreadyStartedException;
use Mirakl\Process\Model\Exception\CannotRunException;
use Mirakl\Process\Model\Exception\ChildProcessException;
use Mirakl\Process\Model\Exception\StopExecutionException;
use Mirakl\Process\Model\ResourceModel\Process as ProcessResource;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;
use Psr\Log\LoggerInterface;

class Execution
{
    /**
     * @var Execution\Executor
     */
    private $executor;

    /**
     * @var Execution\Failure
     */
    private $failure;

    /**
     * @var Execution\Stack
     */
    private $stack;

    /**
     * @var ProcessResource
     */
    private $processResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Execution\Executor     $executor
     * @param Execution\Failure      $failure
     * @param Execution\Stack        $stack
     * @param ProcessResourceFactory $processResourceFactory
     * @param LoggerInterface        $logger
     */
    public function __construct(
        Execution\Executor $executor,
        Execution\Failure $failure,
        Execution\Stack $stack,
        ProcessResourceFactory $processResourceFactory,
        LoggerInterface $logger
    ) {
        $this->executor = $executor;
        $this->failure = $failure;
        $this->stack = $stack;
        $this->processResource = $processResourceFactory->create();
        $this->logger = $logger;
    }

    /**
     * @param Process $process
     * @return void
     */
    private function init(Process $process): void
    {
        register_shutdown_function(function () use ($process) {
            if (!$process->isStopped()) {
                $error = error_get_last();
                if (!empty($error) && $error['type'] != E_NOTICE) {
                    $message = sprintf('%s in %s on line %d', $error['message'], $error['file'], $error['line']);
                    $this->fail($process, $message);
                }
            }
        });
    }

    /**
     * @param Process $process
     * @param bool    $force
     * @return mixed
     * @throws CannotRunException
     */
    public function run(Process $process, bool $force = false)
    {
        if (!$process->isPending() && !$force) {
            throw new CannotRunException($process, __('Cannot run a process that is not in pending status.'));
        }

        $this->start($process);
        $result = $this->execute($process);
        $this->stop($process);

        return $result;
    }

    /**
     * @param Process $process
     * @return void
     */
    public function start(Process $process): void
    {
        if ($process->isStarted()) {
            throw new AlreadyStartedException($process, __('Cannot start a process that is already started'));
        }

        $this->init($process);
        $process->setStartedAt(microtime(true));

        if ($process->isEnded()) {
            // Remove children if process was already executed, it will create new ones.
            $process->deleteChildren();

            // Reset some data for fresh output and process execution time
            $process->setCreatedAt(time())
                ->setOutput(null)
                ->setDuration(null);
        }

        if (PHP_SAPI == 'cli') {
            $process->addOutput('cli');
        }

        $this->processResource->save($process);
        $this->stack->add($process);
    }

    /**
     * @param Process $process
     * @return mixed
     */
    public function execute(Process $process)
    {
        ob_start();

        try {
            return $this->executor->execute($process);
        } catch (StopExecutionException $e) {
            $process->output($e->getMessage());
            $process->stop($e->getStatus());
        } catch (ChildProcessException $e) {
            $process->fail($e->getMessage());
        } catch (\Exception $e) {
            $this->fail($process, $e->getMessage());
            $this->logger->critical($e->getMessage());
            throw $e;
        } finally {
            if ($output = ob_get_flush()) {
                $process->output($output);
            }
        }

        return [];
    }

    /**
     * @param Process $process
     * @param string  $status
     * @return void
     */
    public function stop(Process $process, string $status = Process::STATUS_COMPLETED): void
    {
        if ($process->getStopped()) {
            return;
        }

        $process->updateDuration();
        $process->outputMemoryUsage();

        foreach ($process->getOutputs() as $output) {
            $output->close();
        }

        $process->setStopped();
        $process->setStatus($status);

        $this->processResource->save($process);
        $this->stack->remove($process);
    }

    /**
     * @param Process     $process
     * @param string|null $message
     * @return void
     */
    public function cancel(Process $process, ?string $message = null): void
    {
        $this->fail($process, $message, Process::STATUS_CANCELLED);
    }

    /**
     * @param Process     $process
     * @param string|null $message
     * @param string      $status
     * @return void
     */
    public function fail(Process $process, ?string $message = null, string $status = Process::STATUS_ERROR): void
    {
        if ($message) {
            $process->output('<error>' . $message . '</error>');
        }

        $this->stop($process, $status);

        $this->failure->propagate($process, $status);
    }
}