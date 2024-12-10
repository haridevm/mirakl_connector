<?php

declare(strict_types=1);

namespace Mirakl\Process\Helper;

class Config extends \Mirakl\Core\Helper\Config
{
    public const XML_PATH_AUTO_ASYNC_EXECUTION        = 'mirakl_process/general/auto_async_execution';
    public const XML_PATH_SHOW_FILE_MAX_SIZE          = 'mirakl_process/general/show_file_max_size';
    public const XML_PATH_PROCESS_LONG_TIMEOUT_DELAY  = 'mirakl_process/general/timeout_delay';
    public const XML_PATH_PROCESS_SHORT_TIMEOUT_DELAY = 'mirakl_process/general/short_timeout_delay';
    public const XML_PATH_PROCESS_HISTORY_KEEP_DAYS   = 'mirakl_process/history/clear_keep_days';

    /**
     * Returns allowed max file size (in MB) for process files that can be viewed directly in browser
     *
     * @return int
     */
    public function getShowFileMaxSize()
    {
        return intval($this->getValue(self::XML_PATH_SHOW_FILE_MAX_SIZE));
    }

    /**
     * Returns delay in minutes after which a process has to be automatically cancelled (blank = no timeout).
     *
     * Concerns long processes (CM51, CM21,...)
     *
     * @see /Mirakl/Process/etc/di.xml
     *
     * @return int
     */
    public function getLongTimeoutDelay()
    {
        return abs(intval($this->getValue(self::XML_PATH_PROCESS_LONG_TIMEOUT_DELAY)));
    }

    /**
     * Returns delay in minutes after which a process has to be automatically cancelled (blank = no timeout).
     *
     * Concerns short processes (H01, VL01,...) and used by default for processes with undefined code
     *
     * @return int
     */
    public function getShortTimeoutDelay()
    {
        return abs(intval($this->getValue(self::XML_PATH_PROCESS_SHORT_TIMEOUT_DELAY)));
    }

    /**
     * Returns true if processes can be executed automatically
     * through an AJAX request in Magento admin, false otherwise.
     *
     * @return bool
     */
    public function isAutoAsyncExecution()
    {
        return $this->getFlag(self::XML_PATH_AUTO_ASYNC_EXECUTION);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getProcessClearHistoryBeforeDate()
    {
        $cleanBeforeInDays = $this->getValue(self::XML_PATH_PROCESS_HISTORY_KEEP_DAYS);
        $datetime = new \DateTime('now');
        $dateInterval = 'P' . $cleanBeforeInDays . 'D';
        $datetime->sub(new \DateInterval($dateInterval));

        return $datetime->format('Y-m-d H:i:s');
    }
}
