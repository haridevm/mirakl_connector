<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Action;

interface RetryableInterface
{
    /**
     * @return int
     */
    public function getRetryCount(): int;

    /**
     * @param int $retryCount
     * @return void
     */
    public function setRetryCount(int $retryCount): void;

    /**
     * @return int
     */
    public function getMaxRetry(): int;

    /**
     * @param int $maxRetry
     * @return void
     */
    public function setMaxRetry(int $maxRetry): void;
}
