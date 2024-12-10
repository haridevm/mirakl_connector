<?php

declare(strict_types=1);

namespace Mirakl\Process\Model\Action;

/**
 * @method mixed getData(string $key)
 * @method void  setData(string $key, mixed $value)
 */
trait DelayableTrait
{
    /**
     * @return int
     */
    public function getDefaultDelay(): int
    {
        return (int) $this->_getData('default_delay');
    }

    /**
     * @param int $defaultDelay
     * @return void
     */
    public function setDefaultDelay(int $defaultDelay): void
    {
        $this->setData('default_delay', $defaultDelay);
    }

    /**
     * @return int
     */
    public function getMaxAttempts(): int
    {
        return (int) $this->_getData('max_attempts');
    }

    /**
     * @param int $maxAttempts
     * @return void
     */
    public function setMaxAttempts(int $maxAttempts): void
    {
        $this->setData('max_attempts', $maxAttempts);
    }
}
