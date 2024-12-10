<?php

declare(strict_types=1);

namespace Mirakl\Process\Model;

use Mirakl\Process\Model\Exception\ChildProcessException;
use Mirakl\Process\Model\Exception\StopExecutionException;
use Mirakl\Process\Model\Execution\Validator;
use Mirakl\Process\Model\ResourceModel\Process as ProcessResource;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var Validator
     */
    private $runProcessValidator;

    /**
     * @var Validator
     */
    private $startProcessValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private array $catchErrors;

    /**
     * @param Execution\Executor     $executor
     * @param Execution\Failure      $failure
     * @param Execution\Stack        $stack
     * @param ProcessResourceFactory $processResourceFactory
     * @param Validator              $runProcessValidator
     * @param Validator              $startProcessValidator
     * @param LoggerInterface        $logger
     * @param array                  $catchErrors
     */
    public function __construct(
        Execution\Executor $executor,
        Execution\Failure $failure,
        Execution\Stack $stack,
        ProcessResourceFactory $processResourceFactory,
        Validator $runProcessValidator,
        Validator $startProcessValidator,
        LoggerInterface $logger,
        array $catchErrors = [E_ERROR, E_WARNING, E_USER_ERROR, E_USER_WARNING]
    ) {
        $this->executor = $executor;
        $this->failure = $failure;
        $this->stack = $stack;
        $this->processResource = $processResourceFactory->create();
        $this->runProcessValidator = $runProcessValidator;
        $this->startProcessValidator = $startProcessValidator;
        $this->logger = $logger;
        $this->catchErrors = $catchErrors;
    }

    /**
     * @param Process $process
     * @return void
     */
    private function initErrorHandler(Process $process): void
    {
        set_error_handler(function ($errNo, $errStr, $errFile, $errLine) use ($process) {
            if (in_array($errNo, $this->catchErrors)) {
                $process->error(__('%1 in %2 on line %3', $errStr, $errFile, $errLine));
            }
        });
    }

    /**
     * @param Process $process
     * @param bool    $force
     * @return mixed
     * @throws \Exception
     */
    public function run(Process $process, bool $force = false)
    {
        $process->setForceExecution($force);
        $this->runProcessValidator->validate($process);

        $this->start($process);
        $result = $this->execute($process);
        $this->stop($process);

        return $result;
    }

    /**
     * @param Process $process
     * @return void
     * @throws \Exception
     */
    public function start(Process $process): void
    {
        $this->startProcessValidator->validate($process);

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
     * @throws \Exception
     */
    public function execute(Process $process)
    {
        ob_start();

        $this->initErrorHandler($process);

        try {
            return $this->executor->execute($process);
        } catch (StopExecutionException $e) {
            $process->output($e->getMessage());
            $this->stop($process, $e->getStatus());
        } catch (ChildProcessException $e) {
            $this->fail($process, $e->getMessage());
        } catch (\TypeError $e) {
            $process->output($e->getMessage());
            $this->fail($process, $e->getMessage());
        } catch (\Exception $e) {
            $this->fail($process, $e->getMessage());
            $this->logger->critical($e->getMessage());
            throw $e;
        } finally {
            restore_error_handler();
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
     * @throws \Exception
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
            $process->error($message);
        }

        $this->stop($process, $status);

        $this->failure->propagate($process, $status);
    }
}
