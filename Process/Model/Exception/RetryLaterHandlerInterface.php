<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Exception;

interface RetryLaterHandlerInterface
{
    /**
     * @param ProcessException $e
     * @return void
     * @throws ChildMaxRetryException
     */
    public function handle(ProcessException $e): void;
}
