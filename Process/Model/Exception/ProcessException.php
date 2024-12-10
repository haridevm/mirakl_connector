<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Mirakl\Process\Model\Process;

class ProcessException extends LocalizedException
{
    /**
     * @var Process
     */
    protected $process;

    /**
     * @param Process         $process
     * @param Phrase          $phrase
     * @param \Exception|null $cause
     * @param int             $code
     */
    public function __construct(Process $process, Phrase $phrase, \Exception $cause = null, int $code = 0)
    {
        parent::__construct($phrase, $cause, $code);
        $this->process = $process;
    }

    /**
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->process;
    }
}
