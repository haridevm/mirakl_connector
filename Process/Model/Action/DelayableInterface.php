<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Action;

interface DelayableInterface
{
    /**
     * @return int
     */
    public function getDefaultDelay(): int;

    /**
     * @param int $defaultDelay
     * @return void
     */
    public function setDefaultDelay(int $defaultDelay): void;

    /**
     * @return int
     */
    public function getMaxAttempts(): int;

    /**
     * @param int $maxAttempts
     * @return void
     */
    public function setMaxAttempts(int $maxAttempts): void;
}
