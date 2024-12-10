<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Exception;

use Magento\Framework\Phrase;
use Mirakl\Process\Model\Process;

class StopExecutionException extends ChildProcessException
{
    /**
     * @var string
     */
    private string $status;

    /**
     * @param Process         $process
     * @param Phrase          $phrase
     * @param string          $status
     * @param \Exception|null $cause
     * @param int             $code
     */
    public function __construct(
        Process $process,
        Phrase $phrase,
        string $status = Process::STATUS_STOPPED,
        \Exception $cause = null,
        int $code = 0
    ) {
        parent::__construct($process, $phrase, $cause, $code);
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}