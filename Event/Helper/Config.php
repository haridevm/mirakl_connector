<?php

declare(strict_types=1);

namespace Mirakl\Event\Helper;

class Config extends \Mirakl\Core\Helper\Config
{
    public const XML_PATH_EVENT_ASYNC_ACTIVE      = 'mirakl_event/general/event_async_active';
    public const XML_PATH_EVENT_HISTORY_KEEP_DAYS = 'mirakl_event/history/clear_keep_days';

    /**
     * @return array
     */
    public function getAsyncEvents()
    {
        $types = $this->getValue(self::XML_PATH_EVENT_ASYNC_ACTIVE);

        return !empty($types) ? explode(',', $types) : [];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getEventClearHistoryBeforeDate()
    {
        $cleanBeforeInDays = $this->getValue(self::XML_PATH_EVENT_HISTORY_KEEP_DAYS);
        $datetime = new \DateTime('now');
        $dateInterval = 'P' . $cleanBeforeInDays . 'D';
        $datetime->sub(new \DateInterval($dateInterval));

        return $datetime->format('Y-m-d H:i:s');
    }
}
