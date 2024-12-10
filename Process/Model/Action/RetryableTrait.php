<?php
declare(strict_types=1);

namespace Mirakl\Process\Model\Action;

/**
 * @method mixed getData(string $key)
 * @method void  setData(string $key, mixed $value)
 */
trait RetryableTrait
{
    /**
     * @return int
     */
    public function getRetryCount(): int
    {
        return (int) $this->_getData('retry_count');
    }

    /**
     * @param int $retryCount
     * @return void
     */
    public function setRetryCount(int $retryCount): void
    {
        $this->setData('retry_count', $retryCount);
    }

    /**
     * @return int
     */
    public function getMaxRetry(): int
    {
        return (int) $this->_getData('max_retry');
    }

    /**
     * @param int $maxRetry
     * @return void
     */
    public function setMaxRetry(int $maxRetry): void
    {
        $this->setData('max_retry', $maxRetry);
    }
}